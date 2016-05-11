<?php
/**
 * CSS Munch
 * Copyright (c) 2008, Christopher Utz <cutz@chrisutz.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz <cutz@chrisutz.com>
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Parser.php 55 2008-06-07 04:09:09Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_Scanner_Interface
 */
require_once 'Munch/Scanner/Interface.php';

/**
 * @see Munch_Scanner
 */
require_once 'Munch/Scanner.php';

/**
 * @see Munch_Parser_Exception
 */
require_once 'Munch/Parser/Exception.php';

/**
 * @see Munch_Parser_UnexpectedTokenException
 */
require_once 'Munch/Parser/UnexpectedTokenException.php';

/**
 * Parses a string of CSS into an abstract syntax tree.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Parser.php 55 2008-06-07 04:09:09Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_Parser
{
    /**
     * Recovery type constants
     */
    const DECLARATION = 'd';
    const BRACE       = 'b';

    /**
     * The scanner which the parser receives tokens from.
     *
     * @var Munch_Scanner_Interface
     */
    protected $_scanner = null;

    /**
     * The previous token considered by the parser. Use the _unputToken method
     * to make this token the current token again.
     *
     * @var Munch_Token|null
     */
    protected $_prevToken = null;

    /**
     * The next token to return from _nextToken.  This property is set by 
     * _unputToken.
     *
     * @var Munch_Token|null
     */
    protected $_nextToken = null;

    /*
     * The current token being considered by the parser. Initially set to -1
     * and not null to simplify the logic of _nextToken.
     *
     * @var Munch_Token|null
     */
    protected $_currToken = -1;

    /**
     * The type of the current token (ie, $this->_currToken->getType()).
     *
     * @var string
     */
    protected $_currType = '';

    /**
     * Constructs a Munch_Parser instance. If $scanner is not supplied, an
     * instance of Munch_Scanner is constructed and used to receive tokens
     *
     * @param Munch_Scanner_Interface $scanner
     * @return void
     * @throws Munch_Parser_Exception
     */
    public function __construct($scanner = null)
    {
        if (!is_null($scanner)) {
            if (!($scanner instanceof Munch_Scanner_Interface)) {
                throw new Munch_Parser_Exception('$scanner must implement Munch_Scanner_Interface');
            }

            $this->_scanner = $scanner;
        } else {
            $this->_scanner = new Munch_Scanner();
        }
    }

    /**
     * Returns the scanner the parser receives tokens from.
     *
     * @return Munch_Scanner_Interface
     */
    public function getScanner()
    {
        return $this->_scanner;
    }

    /**
     * Parses CSS into an abstract syntax tree (a tree of Munch_AstNode 
     * subclasses).
     * 
     * @return Munch_AstNode
     */
    public function parse()
    {
        $this->_nextToken();

        return $this->_parseStylesheet();
    }

    /**
     * Parses a stylesheet production.
     *
     * @return void
     */
    protected function _parseStylesheet()
    {
        $this->_skipUnknownAtRules();

        $charset  = null;
        $imports  = array();
        $rulesets = array();

        try {
            $charset = $this->_parseCharset();
        } catch (Munch_Parser_UnexpectedTokenException $e) {
            $this->_pairRecover(self::DECLARATION);
        }

        $this->_skipWhiteSpaceAndHtmlCommentTokens();

        while ($this->_currType == Munch_Token::IMPORT_SYM) {
            try {
                $imports[] = $this->_parseImport();
            } catch (Munch_Parser_UnexpectedTokenException $e) {
                $this->_pairRecover(self::DECLARATION);
            }

            $this->_skipWhiteSpaceAndHtmlCommentTokens();
        }

        while ($this->_currType !== null) {
            try {
                if ($this->_isCurrentSelectorToken()) {
                    $rulesets[] = $this->_parseRuleset();
                } else {
                    throw new Munch_Parser_UnexpectedTokenException();
                }
            } catch (Munch_Parser_UnexpectedTokenException $e) {
                $this->_pairRecover(self::BRACE);
            }
            $this->_skipWhiteSpaceAndHtmlCommentTokens();
        }

        return self::_newNode('Stylesheet', array($charset, $imports, $rulesets));
    }

    /**
     * Returns the character set of the stylesheet as a string, or null if no
     * character set is specified.
     *
     * @return string|null
     * @throws Munch_Parser_UnexpectedTokenException
     */
    protected function _parseCharset()
    {
        // If the current token is not @charset, then there is no character 
        // set specified in the CSS (there can be at most one such 
        // declaration in a stylesheet).
        if ($this->_currType != Munch_Token::CHARSET_SYM) {
            return;
        }

        $this->_nextToken();

        // There must be exactly a single space character after the @charset
        // rule according to the CSS specs.
        if ($this->_currType != Munch_Token::WHITE_SPACE or
            $this->_currToken->getValue() != ' ') {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $this->_nextToken();

        // The string must be double quoted according to the CSS specs.
        if ($this->_currType != Munch_Token::STR or
            $this->_currToken->getEnclosure() != '"') {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $charset = $this->_currToken->getValue();

        $this->_nextToken();

        if ($this->_currType != Munch_Token::SEMI) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $this->_nextToken();

        return $charset;
    }

    /**
     * Parses a single @import rule. The method expects the current token to be
     * an IMPORT_SYM.
     *
     * @return Munch_AstNode_Import
     * @throws Munch_Parser_Exception|Munch_UnexpectedTokenException
     */
    protected function _parseImport()
    {
        if ($this->_currType != Munch_Token::IMPORT_SYM) {
            throw new Munch_Parser_Exception("current token does not start an import");
        }

        $this->_nextToken();
        $this->_skipWhiteSpace();

        if ($this->_currType != Munch_Token::STR and
            $this->_currType != Munch_Token::URI) {
            throw new Munch_Parser_UnexpectedTokenException();            
        }

        $uri = $this->_currToken->getValue();

        $this->_nextToken();
        $this->_skipWhiteSpace();

        $mediums = array();

        if ($this->_currType == Munch_Token::IDENT) {
            $mediums[] = self::_newNode('Medium', $this->_currToken->getValue());

            $this->_nextToken();
            $this->_skipWhiteSpace();

            while ($this->_currType == Munch_Token::COMMA) {
                $this->_nextToken();
                $this->_skipWhiteSpace();

                if ($this->_currType != Munch_Token::IDENT) {
                    throw new Munch_Parser_UnexpectedTokenException();
                }

                $mediums[] = self::_newNode('Medium', $this->_currToken->getValue());

                $this->_nextToken();
                $this->_skipWhiteSpace();
            }
        }

        if ($this->_currToken != Munch_Token::SEMI) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $this->_nextToken();
        $this->_skipWhiteSpace();

        return self::_newNode('Import', array($uri, $mediums));
    }

    /**
     * Parses a single ruleset. The method expects the current token to be part
     * of a selector.
     *
     * @return Munch_AstNode_Ruleset
     * @throws Munch_Parser_Exception|Munch_Parser_UnexpectedTokenException
     */
    protected function _parseRuleset()
    {
        if (!$this->_isCurrentSelectorToken()) {
            throw new Munch_Parser_Exception("current token does not start a ruleset");
        }

        $selectors = array($this->_parseSelector());

        while ($this->_currType == Munch_Token::COMMA) {
            $this->_nextToken();
            $this->_skipWhiteSpace();

            $selectors[] = $this->_parseSelector();
        }

        $declarations = array();

        if ($this->_currType != Munch_Token::LEFT_BRACE) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $this->_nextToken();
        $this->_skipWhiteSpace();

        while ($this->_currType != Munch_Token::RIGHT_BRACE and
               $this->_currType != null) {
            // Sequences of space and semicolons are acceptable according to
            // the grammar, but serve no function.
            if ($this->_currType == Munch_Token::SEMI) {
                $this->_nextToken();
                $this->_skipWhiteSpace();
            } else if ($this->_currType == Munch_Token::IDENT) {
                try {
                    $declarations[] = $this->_parseDeclaration();
                } catch (Munch_Parser_UnexpectedTokenException $e) {
                    $this->_pairRecover(self::DECLARATION);
                }

                $this->_skipWhiteSpace();
            } else {
                $this->_pairRecover(self::DECLARATION);
            }
        }

        $this->_nextToken();
        $this->_skipWhiteSpace();

        return self::_newNode('Ruleset', array($selectors, $declarations));
    }

    /**
     * Parses a selector. The method expects the current token to be part
     * of a selector.
     *
     * @return Munch_AstNode_Selector
     * @throws Munch_Parser_Exception
     */
    protected function _parseSelector()
    {
        if (!$this->_isCurrentSelectorToken()) {
            throw new Munch_Parser_Exception("current token does not start a selector");
        }

        $selectorGroups = array($this->_parseSelectorGroup());
        $combinators    = array();

        while ($this->_currType == Munch_Token::WHITE_SPACE or
               $this->_currType == Munch_Token::GREATER or
               $this->_currType == Munch_Token::PLUS) {

            if (null === ($combinator = $this->_parseCombinator())) {
                break;
            }

            $combinators[]    = $combinator;
            $selectorGroups[] = $this->_parseSelectorGroup();
        }

        return self::_newNode('Selector', array($selectorGroups, $combinators));
    }

    /**
     * Parses a combinator. 
     *
     * @return Munch_AstNode_Combinator
     * @throws Munch_Parser_UnexpectedTokenException
     */
    protected function _parseCombinator()
    {
        switch ($this->_currType) {
            case Munch_Token::WHITE_SPACE:
                // we need to check if this is actually a selector or if its 
                // just a space before the curly brace.
                if ($this->_currType == Munch_Token::WHITE_SPACE) {
                    $this->_nextToken();

                    if (!$this->_isCurrentSelectorToken()) {
                        // the space was not a selector, backup ...
                        $this->_unputToken();
                        return null;
                    } else {
                        return self::_newNode('Combinator', ' ');
                    }
                }
                break;
            case Munch_Token::GREATER:
                $this->_nextToken();
                $this->_skipWhiteSpace();
                return self::_newNode('Combinator', '>');
                break;
            case Munch_Token::PLUS:
                $this->_nextToken();
                $this->_skipWhiteSpace();
                return self::_newNode('Combinator', '+');
                break;
            default:
                throw new Munch_Parser_UnexpectedTokenException();
        }
    }

    /**
     * Parses a selector group.
     *
     * @return Munch_AstNode_SelectorGroup
     * @throws Munch_Parser_UnexpectedTokenException
     */
    protected function _parseSelectorGroup()
    {
        switch ($this->_currType) {
            case Munch_Token::IDENT:
                $elementName = self::_newNode('ElementName', 
                    array('element', $this->_currToken->getValue()));
                $this->_nextToken();
                break;
            case Munch_Token::STAR:
                $elementName = self::_newNode('ElementName', '*');
                $this->_nextToken();
                break;
            default:
                $elementName = null;
                break;
        }

        $simpleSelectors = array();

        while ($this->_currType == Munch_Token::HASH or
               $this->_currType == Munch_Token::DOT or
               $this->_currType == Munch_Token::LEFT_BRACKET or
               $this->_currType == Munch_Token::COLON) {

            switch ($this->_currType) {
                case Munch_Token::HASH:
                    // id selector
                    $simpleSelectors[] = self::_newNode('SimpleSelector_Id', 
                        $this->_currToken->getValue());
                    $this->_nextToken();
                    break;
                case Munch_Token::DOT:
                    // class selector
                    $this->_nextToken();
                    if ($this->_currType != Munch_Token::IDENT) {
                        throw new Munch_Parser_UnexpectedTokenException();
                    }
                    $simpleSelectors[] = self::_newNode('SimpleSelector_Class',
                        $this->_currToken->getValue());
                    $this->_nextToken();
                    break;
                case Munch_Token::LEFT_BRACKET:
                    // attribute selector
                    $simpleSelectors[] = $this->_parseAttributeSelector();
                    break;
                case Munch_Token::COLON:
                    // pseudo selector
                    $simpleSelectors[] = $this->_parsePseudoSelector();
                    break;
            }
        }

        // When an element name is not provided, there must be at least one
        // simple selector present.
        if ($elementName == null and count($simpleSelectors) == 0) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        return self::_newNode('SelectorGroup', array($elementName, $simpleSelectors));
    }

    /**
     * Parses a declaration. This method expects the current token to be an ident.
     *
     * @return Munch_AstNode_Declaration
     * @throws Munch_Parser_Exception|Munch_Parser_UnexpectedTokenException
     */
    protected function _parseDeclaration()
    {
        if ($this->_currType != Munch_Token::IDENT) {
            throw new Munch_Parser_Exception("current token does not start a declaration");
        }

        $property = self::_newNode('Property', $this->_currToken->getValue());

        $this->_nextToken();
        $this->_skipWhiteSpace();

        if ($this->_currType != Munch_Token::COLON) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $this->_nextToken();
        $this->_skipWhiteSpace();

        if (!$this->_isCurrentTermToken()) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $expression = $this->_parseExpression();

        if ($this->_currType == Munch_Token::IMPORTANT_SYM) {
            $priority = self::_newNode('Priority', array());
            $this->_nextToken();
            $this->_skipWhiteSpace();
        } else {
            $priority = null;
        }

        return self::_newNode('Declaration', array($property, $expression, $priority));
    }

    /**
     * 
     */
    protected function _parseExpression()
    {
        if (!$this->_isCurrentTermToken()) {
            throw new Munch_Parser_Exception("current token does not start an expression");
        }

        $terms     = array($this->_parseTerm());
        $operators = array();

        while ($this->_currType == Munch_Token::SLASH or
               $this->_currType == Munch_Token::COMMA or
               $this->_isCurrentTermToken()) {
            if ($this->_currType == Munch_Token::SLASH) {
                $operators[] = self::_newNode('Operator', '/');
                $this->_nextToken();
                $this->_skipWhiteSpace();
            } else if ($this->_currType == Munch_Token::COMMA) {
                $operators[] = self::_newNode('Operator', ',');
                $this->_nextToken();
                $this->_skipWhiteSpace();
            } else {
                $operators[] = self::_newNode('Operator', ' ');
            }

            $terms[] = $this->_parseTerm();
        }

        return self::_newNode('Expression', array($terms, $operators));
    }

    /**
     * Parses a term. This method expects the current token to be part of a 
     * term.
     *
     * @return Munch_AstNode_Term_Abstract
     * @throws Munch_Parser_Exception|Munch_Parser_UnexpectedTokenException
     */
    protected function _parseTerm()
    {
        if (!$this->_isCurrentTermToken()) {
            throw new Munch_Parser_Exception("current token does not start an term");
        }

        // parse the unary operator if one is present.
        switch ($this->_currType) {
            case Munch_Token::MINUS:
                $unaryOperator = self::_newNode('UnaryOperator', '-');
                $this->_nextToken();
                break;
            case Munch_Token::PLUS:
                $unaryOperator = self::_newNode('UnaryOperator', '+');
                $this->_nextToken();
                break;
            default:
                $unaryOperator = null;
                break;
        }

        $value = $this->_currToken->getValue();

        if ($this->_currToken instanceof Munch_Token_UnitValue) {
            $unit = $this->_currToken->getUnit();
        }

        switch ($this->_currType) {
            case Munch_Token::NUMBER:
                $term = self::_newNode('Term_Number', array($value, $unaryOperator));
                break;
            case Munch_Token::PERCENTAGE:
                $term = self::_newNode('Term_Percentage', array($value, $unaryOperator));
                break;
            case Munch_Token::LENGTH:
                $term = self::_newNode('Term_Length', array($value, $unit, $unaryOperator));
                break;
            case Munch_Token::EMS:
                $term = self::_newNode('Term_Ems', array($value, $unaryOperator));
                break;
            case Munch_Token::EXS:
                $term = self::_newNode('Term_Exs', array($value, $unaryOperator));
                break;
            case Munch_Token::ANGLE:
                $term = self::_newNode('Term_Angle', array($value, $unit, $unaryOperator));
                break;
            case Munch_Token::TME:
                $term = self::_newNode('Term_Time', array($value, $unit, $unaryOperator));
                break;
            case Munch_Token::FREQ:
                $term = self::_newNode('Term_Frequency', array($value, $unit, $unaryOperator));
                break;
            case Munch_Token::STR:
                $term = self::_newNode('Term_String', array($value, $unaryOperator));
                break;
            case Munch_Token::IDENT:
                $term = self::_newNode('Term_Identifier', array($value, $unaryOperator));
                break;
            case Munch_Token::URI:
                $term = self::_newNode('Term_Uri', array($value, $unaryOperator));
                break;
            case Munch_Token::HASH:
                $term = self::_newNode('Term_HexColor', array($value, $unaryOperator));
                break;
            case Munch_Token::FUNC:
                $term = self::_newNode('Term_Function', array($this->_parseFunction(), $unaryOperator));
                $noNextToken = true;
                break;
        }

        // little hack to accommodate function parsing above; allows 
        // _parseFunction to be self contained.
        if (empty($noNextToken)) {
            $this->_nextToken();
            $this->_skipWhiteSpace();
        }

        return $term;
    }

    /**
     * Parses a function. This method expects the current token to be a
     * function.
     *
     * @return Munch_AstNode_Function
     * @throws Munch_Parser_Exception|Munch_Parser_UnexpectedTokenException
     */
    protected function _parseFunction()
    {
        if ($this->_currType != Munch_Token::FUNC) {
            throw new Munch_Parser_Exception("current token does not start a function");
        }

        $name = $this->_currToken->getValue();

        $this->_nextToken();
        $this->_skipWhiteSpace();

        // TODO: Repeated recursions could cause a stack overflow here.  Perhaps
        // some sort of check should be done to prevent this.
        $expression = $this->_parseExpression();

        if ($this->_currType != Munch_Token::RIGHT_PAREN) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $this->_nextToken();
        $this->_skipWhiteSpace();

        return self::_newNode('Function', array($name, $expression));
    }

    /**
     *
     */
    protected function _parseAttributeSelector()
    {
        if ($this->_currType != Munch_Token::LEFT_BRACKET) {
            throw new Munch_Parser_Exception("current token does not start an attribute selector");
        }

        $this->_nextToken();
        $this->_skipWhiteSpace();

        // an empty selector is valid according to the grammar
        if ($this->_currType == Munch_Token::RIGHT_BRACKET) {
            return self::_newNode('SimpleSelector_Attribute', array());
        }

        if ($this->_currType != Munch_Token::IDENT) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $attribute = $this->_currToken->getValue();

        $this->_nextToken();
        $this->_skipWhiteSpace();

        switch ($this->_currType) {
            case Munch_Token::EQUALS:
                $operator = '=';
                break;
            case Munch_Token::INCLUDES:
                $operator = '~=';
                break;
            case Munch_Token::DASH_MATCH:
                $operator = '|=';
                break;
            default:
                throw new Munch_Parser_UnexpectedTokenException();
        }

        $this->_nextToken();
        $this->_skipWhiteSpace();

        if ($this->_currType != Munch_Token::IDENT and 
            $this->_currType != Munch_Token::STR) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $value = $this->_currToken->getValue();

        $this->_nextToken();
        $this->_skipWhiteSpace();

        if ($this->_currType != Munch_Token::RIGHT_BRACKET) {
            throw new Munch_Parser_UnexpectedTokenException();
        }

        $this->_nextToken();

        return self::_newNode('SimpleSelector_Attribute', array($attribute, $operator, $value));
    }

    /**
     *
     */
    protected function _parsePseudoSelector()
    {
        if ($this->_currType != Munch_Token::COLON) {
            throw new Munch_Parser_Exception("current token does not start a pseudo selector");
        }

        $this->_nextToken();
        // white space is not allowed here.
        
        switch ($this->_currType) {
            case Munch_Token::IDENT:
                $pseudo = $this->_currToken->getValue();
                $value  = null;
                $this->_nextToken();
                break;
            case Munch_Token::FUNC:
                $pseudo = $this->_currToken->getValue();
                $this->_nextToken();
                $this->_skipWhiteSpace();
                if ($this->_currType == Munch_Token::RIGHT_PAREN) {
                    $value = '';
                    $this->_nextToken();
                    break;
                } else if ($this->_currType != Munch_Token::IDENT) {
                    throw new Munch_Parser_UnexpectedTokenException();
                }
                $value = $this->_currToken->getValue();
                $this->_nextToken();
                $this->_skipWhiteSpace();
                if ($this->_currType != Munch_Token::RIGHT_PAREN) {
                    throw new Munch_Parser_UnexpectedTokenException();
                }
                $this->_nextToken();
                break;
            default:
                throw new Munch_Parser_UnexpectedTokenException();
        }

        return self::_newNode('SimpleSelector_Pseudo', array($pseudo, $value));
    }

    /**
     * Skips over all whitespace, <!--, and --> tokens starting at the current
     * token.
     *
     * @return void
     */
    protected function _skipWhiteSpaceAndHtmlCommentTokens()
    {
        while ($this->_currType == Munch_Token::WHITE_SPACE or
               $this->_currType == Munch_Token::CDO or
               $this->_currType == Munch_Token::CDC) {
            
            $this->_nextToken();
        }
    }

    /**
     * Skips over all whitespace tokens starting at the current token.
     *
     * @return void
     */
    protected function _skipWhiteSpace()
    {
        while ($this->_currType == Munch_Token::WHITE_SPACE) {
            $this->_nextToken();
        }
    }

    /**
     * Moves the parser past all unknown at rules.
     *
     * @return void
     */
    protected function _skipUnknownAtRules()
    {
        while ($this->_currType == Munch_Token::UNKNOWN_AT) {
            $this->_unknownAtRuleRecover();
        }
    }

    /**
     * Advances the current token past the end of the declaration, while 
     * observing rules for matching pairs of (), [], {}, "", and '', and also 
     * correctly handling escapes.
     *
     * @param string $type The recovery method.
     * @return void
     */
    protected function _pairRecover($type)
    {
        $pairStack = array();
        $foundOpeningBrace = false;

        while (true) {
            $atClosingBrace = false;

            switch ($this->_currType) {
                case Munch_Token::LEFT_BRACE:
                    if (count($pairStack) == 0) {
                        $foundOpeningBrace = true;
                    }
                case Munch_Token::LEFT_PAREN:
                case Munch_Token::LEFT_BRACKET:
                    array_push($pairStack, $this->_currType);
                    break;
                case Munch_Token::RIGHT_PAREN:
                    if (end($pairStack) == Munch_Token::LEFT_PAREN) {
                        array_pop($pairStack);
                    }
                    break;
                case Munch_Token::RIGHT_BRACKET:
                    if (end($pairStack) == Munch_Token::LEFT_BRACKET) {
                        array_pop($pairStack);
                    }
                    break;
                case Munch_Token::RIGHT_BRACE:
                    if (end($pairStack) == Munch_Token::LEFT_BRACE) {
                        array_pop($pairStack);
                    } else if (count($pairStack) == 0) {
                        $atClosingBrace = true;
                    }
                    break;
                case null:
                    return;
            }

            if (count($pairStack) == 0) {
                if ($type == self::DECLARATION and $this->_currType == Munch_Token::SEMI) {
                    // We have recovered to a semicolon on the same level as the
                    // declaration.  Moving to the next token, we can continue
                    // parsing as normal.
                    $this->_nextToken();
                    return;
                } else if ($type == self::DECLARATION and $atClosingBrace) {
                    // We are at the end of the current ruleset block, 
                    // leave the closing right brace as the current token.
                    return;
                } else if ($type == self::BRACE and $foundOpeningBrace) {
                    // We recover when we're at the end of a matching pair of 
                    // curly brackets.
                    $this->_nextToken();
                    return;
                }
            }

            $this->_nextToken();
        }
    }

    /**
     * When an unknown at rule is encountered, this method will move the parser
     * ahead to a place where it can begin parsing again.  Assumes that the 
     * current token is an unknown at rule.
     *
     * @return void
     */
    protected function _unknownAtRuleRecover()
    {
        // Starting at the current token, which is an unknown at rule, all tokens
        // are skipped, until the next semicolon or the end of the next curly 
        // bracket block.

        $this->_nextToken();

        $brackDepth = 0;

        while (true) {
            if ($this->_currType == null) {
                return;
            }

            switch ($this->_currType) {
                case Munch_Token::SEMI:
                    // We are at a semicolon on the same block level as the at 
                    // rule. We can begin parsing normally again.
                    if ($brackDepth == 0) {
                        $this->_nextToken();
                        return;
                    }
                    break;
                case Munch_Token::LEFT_BRACKET:
                    $brackDepth++;
                    break;
                case Munch_Token::RIGHT_BRACKET:
                    // We have moved past a curly block. We can begin parsing
                    // normally again.
                    if ($brackDepth == 1) {
                        $this->_nextToken();
                        return;
                    }

                    $brackDepth = max(0, $brackDepth - 1);
                    break;
            }
        }
    }

    /**
     * Returns true if the current token could be the start of a selector
     * or simple selector production.
     *
     * @return boolean
     */
    protected function _isCurrentSelectorToken()
    {
        return $this->_currType == Munch_Token::IDENT or
               $this->_currType == Munch_Token::STAR or
               $this->_currType == Munch_Token::HASH or
               $this->_currType == Munch_Token::DOT or
               $this->_currType == Munch_Token::LEFT_BRACKET or
               $this->_currType == Munch_Token::COLON;
    }

    /**
     * Returns true if the current token could be the start of a term token.
     *
     * @return boolean
     */
    protected function _isCurrentTermToken()
    {
        return $this->_currType == Munch_Token::MINUS or
               $this->_currType == Munch_Token::PLUS or
               $this->_currType == Munch_Token::NUMBER or
               $this->_currType == Munch_Token::PERCENTAGE or
               $this->_currType == Munch_Token::LENGTH or
               $this->_currType == Munch_Token::EMS or
               $this->_currType == Munch_Token::EXS or
               $this->_currType == Munch_Token::ANGLE or
               $this->_currType == Munch_Token::TME or
               $this->_currType == Munch_Token::FREQ or
               $this->_currType == Munch_Token::STR or
               $this->_currType == Munch_Token::IDENT or
               $this->_currType == Munch_Token::URI or
               $this->_currType == Munch_Token::HASH or
               $this->_currType == Munch_Token::FUNC;
    }

    /**
     * Fetches the next token from the scanner, setting the member variables
     * $_currType and $_currToken appropriately.
     *
     * @return void
     */
    protected function _nextToken()
    {
        if ($this->_currToken == null) {
            // We were already at the end of the stylesheet, don't proceed so that
            // _unputToken will still work.
            return;
        } else if ($this->_nextToken != null) {
            // _unputToken was previously called. Move $_nextToken back into
            // $_currToken.
            $this->_prevToken = $this->_currToken;
            $this->_currToken = $this->_nextToken;
            $this->_nextToken = null;
        } else {
            // Proceed normally, pull the next token from the scanner.
            $this->_prevToken = $this->_currToken;
            $this->_currToken = $this->_scanner->getNextToken();
        }

        if ($this->_currToken != null) {
            $this->_currType = $this->_currToken->getType();
        } else {
            $this->_currType = null;
        }
    }

    /**
     * Sets the previous token to be the current token, saving the current
     * token so that calling _nextToken returns the current token again.
     *
     * @return void
     * @throws Munch_Parser_Exception
     */
    protected function _unputToken()
    {
        if ($this->_nextToken != null) {
            throw new Munch_Parser_Exception("cannot unput more than one token");
        }

        $this->_nextToken = $this->_currToken;
        $this->_currToken = $this->_prevToken;
        $this->_prevToken = null;
        $this->_currType  = $this->_currToken->getType();
    }

    /**
     * Instantiates a new AstNode object of a particular type, loading the
     * class based upon the standard directory layout if needed.
     *
     * @param string $nodeType The part of the node name after "AstNode_".
     * @param mixed $constructorArgs The arguments to the constructor.
     * @return Munch_AstNode
     */
    protected static function _newNode($nodeType, $constructorArgs)
    {
        $className = 'Munch_AstNode_' . $nodeType;

        if (!class_exists($className)) {
            $classPath = str_replace('_', '/', $className) . '.php';

            include_once $classPath;

            if (!class_exists($className)) {
                throw new Munch_Parser_Exception("{$className} is not defined");
            }
        }

        $classObj = new ReflectionClass($className);

        return $classObj->newInstanceArgs((array) $constructorArgs);
    }
}
// vim: sw=4:ts=4:sts=4:et
