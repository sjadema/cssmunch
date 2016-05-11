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
 * @version    SVN: $Id: Scanner.php 43 2008-05-26 02:06:57Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_Scanner_Interface
 */
require_once 'Munch/Scanner/Interface.php';

/**
 * @see Munch_Token
 */
require_once 'Munch/Token.php';

/**
 * @see Munch_Token_Angle
 */
require_once 'Munch/Token/Angle.php';

/**
 * @see Munch_Token_Dimension
 */
require_once 'Munch/Token/Dimension.php';

/**
 * @see Munch_Token_Ems
 */
require_once 'Munch/Token/Ems.php';

/**
 * @see Munch_Token_Exs
 */
require_once 'Munch/Token/Exs.php';

/**
 * @see Munch_Token_Frequency
 */
require_once 'Munch/Token/Frequency.php';

/**
 * @see Munch_Token_Function
 */
require_once 'Munch/Token/Function.php';

/**
 * @see Munch_Token_Hash
 */
require_once 'Munch/Token/Hash.php';

/**
 * @see Munch_Token_Identifier
 */
require_once 'Munch/Token/Identifier.php';

/**
 * @see Munch_Token_Length
 */
require_once 'Munch/Token/Length.php';

/**
 * @see Munch_Token_Number
 */
require_once 'Munch/Token/Number.php';

/**
 * @see Munch_Token_Percentage
 */
require_once 'Munch/Token/Percentage.php';

/**
 * @see Munch_Token_Time
 */
require_once 'Munch/Token/Time.php';

/**
 * @see Munch_Token_String
 */
require_once 'Munch/Token/String.php';

/**
 * @see Munch_Token_Uri
 */
require_once 'Munch/Token/Uri.php';

/**
 * @see Munch_Utf8
 */
require_once 'Munch/Utf8.php';

/**
 * Breaks up a character string into a series of tokens. It makes
 * no interpretation of context; that is the job of the parser
 * and weeder.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Scanner.php 43 2008-05-26 02:06:57Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_Scanner implements Munch_Scanner_Interface
{
    /**
     * The css code that is being tokenized.
     * 
     * @var string
     */
    protected $_string = '';

    /**
     * The number of characters in the member variable $_string.
     *
     * @var integer
     */
    protected $_stringLength = 0;

    /**
     * The current character of $_string that is being processed. Note that
     * since we are working with utf-8, this could be a part of a character.
     * However, the fact that ASCII byte patterns do not occur within multi
     * byte utf-8 sequences prevents this from being a problem. If we are at
     * the end of $_string, or before the first character, then $_currChar is
     * null.
     *
     * @var string|null
     */
    protected $_currChar = null;

    /**
     * The index of $_currChar in $_string.
     *
     * @var integer
     */
    protected $_index = -1;

    /**
     * The length of the last string that was passed into the _peekEquals
     * method.
     *
     * @var integer
     */
    protected $_lastPeekLength = 0;

    /**
     * Sets the string of CSS code to be tokenized. It is assumed that $cssCode
     * is encoded using utf-8. (If your input is encoded with another character
     * set, you need to convert it to utf-8 before passing it in).
     *
     * @param  string $cssCode
     * @return void
     */
    public function setInput($cssCode)
    {
        $this->_string = $cssCode;
        $this->_stringLength = strlen($cssCode);
        $this->_index = -1;
    }

    /**
     * Gets the next token from the input stream.  Repeated calls of this 
     * function will return the input css code broken out into individual
     * tokens suitable to be used by a parser. When no more tokens remain,
     * null is returned.  If an invalid token is encountered, a Munch_Token
     * instance is returned with type Munch_Token::INVALID.
     *
     * @return Munch_Token|null
     * @throws Munch_Scanner_Exception|Munch_Utf8_Exception
     */
    public function getNextToken()
    {
        $this->_nextChar();

        if (null === $this->_currChar) {
            return null;
        }

        $this->_skipComments();

        $startIndex = $this->_index;
        $skippedWhiteSpace = $this->_skipToNonWhiteSpace();

        switch ($this->_currChar) {
            case '{':
                return new Munch_Token(Munch_Token::LEFT_BRACE);
            case '+':
                return new Munch_Token(Munch_Token::PLUS);
            case '>':
                return new Munch_Token(Munch_Token::GREATER);
            case ',':
                return new Munch_Token(Munch_Token::COMMA);
            default:
                if ('' != $skippedWhiteSpace) {
                    $this->_unputChar();
                    return new Munch_Token(Munch_Token::WHITE_SPACE, $skippedWhiteSpace);
                }
                break;
        }

        switch($this->_currChar) {
            case ':':
                return new Munch_Token(Munch_Token::COLON);
            case ';':
                return new Munch_Token(Munch_Token::SEMI);
            case '}':
                return new Munch_Token(Munch_Token::RIGHT_BRACE);
            case '[':
                return new Munch_Token(Munch_Token::LEFT_BRACKET);
            case ']':
                return new Munch_Token(Munch_Token::RIGHT_BRACKET);
            case '(':
                return new Munch_Token(Munch_Token::LEFT_PAREN);
            case ')':
                return new Munch_Token(Munch_Token::RIGHT_PAREN);
            case '/':
                return new Munch_Token(Munch_Token::SLASH);
            case '*':
                return new Munch_Token(Munch_Token::STAR);
            case '=':
                return new Munch_Token(Munch_Token::EQUALS);
            case '<':
                if ($this->_peekEquals('<!--')) {
                    $this->_syncOnLastPeek();
                    return new Munch_Token(Munch_Token::CDO);
                } 
                break;
            case '-':
                if ($this->_peekEquals('-->')) {
                    $this->_syncOnLastPeek();
                    return new Munch_Token(Munch_Token::CDC);
                }  
                break;
            case '~':
                if ($this->_peekEquals('~=')) {
                    $this->_syncOnLastPeek();
                    return new Munch_Token(Munch_Token::INCLUDES);
                }
                break;
            case '|':
                if ($this->_peekEquals('|=')) {
                    $this->_syncOnLastPeek();
                    return new Munch_Token(Munch_Token::DASH_MATCH);
                }
                break;
            case '"':
            case "'":
                $enclosure = $this->_currChar;
                $string    = $this->_getString();

                // According to the CSS 2.1 specs, a UA should recover at the
                // end of the next declaration when an unexpected end of line
                // occurs within a string. That is why INVALID is returned
                // here, without moving back to the quote.

                if (null === $string) {
                    return new Munch_Token(Munch_Token::INVALID);
                } else {
                    return new Munch_Token_String($string, $enclosure);
                }
                break;
            case '@':
                $startIndex = $this->_index;

                $this->_nextChar();

                $ident = $this->_getIdent();

                if (null === $ident) {
                    // Move back to the @ character, and return invalid.
                    $this->_setCurrentCharIndex($startIndex);
                    return new Munch_Token(Munch_Token::INVALID);
                } else {
                    switch (strtolower($ident)) {
                        case 'import':
                            return new Munch_Token(Munch_Token::IMPORT_SYM);
                        case 'page':
                            return new Munch_Token(Munch_Token::PAGE_SYM);
                        case 'media':
                            return new Munch_Token(Munch_Token::MEDIA_SYM);
                        case 'charset':
                            return new Munch_Token(Munch_Token::CHARSET_SYM);
                        default:
                            // For the convenience of the parser, so that it can
                            // recover from invalid at keyword properly.
                            return new Munch_Token(Munch_Token::UNKNOWN_AT);
                    }
                }
                break;
            case '#':
                $startIndex = $this->_index;

                $this->_nextChar();

                $name = $this->_getIdent(true);

                if (null === $name) {
                    $this->_setCurrentCharIndex($startIndex);
                    return new Munch_Token(Munch_Token::INVALID);
                } else {
                    return new Munch_Token_Hash($name);
                }
                break;
            case '!':
                $startIndex = $this->_index;

                $this->_nextChar();
                $this->_skipComments();
                $this->_skipToNonWhiteSpace();

                $ident = $this->_getIdent();

                if (null === $ident or strtolower($ident) != 'important') {
                    $this->_setCurrentCharIndex($startIndex);
                    return new Munch_Token(Munch_Token::INVALID);
                } else {
                    return new Munch_Token(Munch_Token::IMPORTANT_SYM);
                }
                break;
            case null:
                return null;
        }

        if ($this->_isCurrentDigit()) {
            return $this->_getNumberBasedToken();
        } else if ($this->_currChar == '.') {
            $this->_nextChar();

            // We need to see the next character to determine if we are in
            // a number token starting with a period, such as ".1em", or if we
            // are just at a DOT token.

            if ($this->_isCurrentDigit()) {
                $this->_unputChar();
                return $this->_getNumberBasedToken();
            } else {
                $this->_unputChar();
                return new Munch_Token(Munch_Token::DOT);
            }
        }

        $identStartMinus = ($this->_currChar == '-');
        $identStart = $this->_index;

        if (null !== ($ident = $this->_getIdent())) {
            $this->_nextChar();

            if ($this->_currChar == '(') {
                // A url function token is distinct from a function token
                // because the parameter to url() does not need to be in
                // quotes (the syntax is looser in the CSS standard for 
                // backwards compatability reasons).
                if (strtolower($ident) == 'url') {
                    return $this->_getUriFunctionToken();
                }

                return new Munch_Token_Function($ident);
            } else {
                $this->_unputChar();
                return new Munch_Token_Identifier($ident);
            }
        } else if ($identStartMinus) {
            // - is not a valid ident, but it is a valid token,
            // namely minus.
            $this->_setCurrentCharIndex($identStart);
            return new Munch_Token(Munch_Token::MINUS);
        }

        return new Munch_Token(Munch_Token::INVALID);
    }

    /**
     * Scans for tokens that start with a number. Assumes that the current
     * character is the first character of the number.
     * 
     * @return Munch_Token
     */
    protected function _getNumberBasedToken()
    {
        // _getNumber does not return null based on the assumptions of this
        // method.
        $numberString = $this->_getNumber();
        $startIndex   = $this->_index;

        $this->_nextChar();

        if ($this->_currChar == '%') {
            return new Munch_Token_Percentage($numberString);
        }

        $ident = $this->_getIdent();

        if (null === $ident) {
            $this->_setCurrentCharIndex($startIndex);
            return new Munch_Token_Number($numberString);
        }

        $loweredIdent = strtolower($ident);

        switch ($loweredIdent) {
            case 'em':
                return new Munch_Token_Ems($numberString);
            case 'ex':
                return new Munch_Token_Exs($numberString);
            case 'px':
            case 'cm':
            case 'mm':
            case 'in':
            case 'pt':
            case 'pc':
                return new Munch_Token_Length($numberString, $loweredIdent);
            case 'deg':
            case 'rad':
            case 'grad':
                return new Munch_Token_Angle($numberString, $loweredIdent);
            case 'ms':
            case 's':
                return new Munch_Token_Time($numberString, $loweredIdent);
            case 'hz':
            case 'khz':
                return new Munch_Token_Frequency($numberString, $loweredIdent);
            default:
                return new Munch_Token_Dimension($numberString, $ident);
        }
    }
    
    /**
     * Scans for a url function token only, based upon its specific, looser
     * syntax.  Assumes that the current character is the paren immediately 
     * after the string "url".
     *
     * @return Munch_Token
     */
    protected function _getUriFunctionToken()
    {
        $this->_nextChar();
        $this->_skipToNonWhiteSpace();

        if ($this->_currChar == '"' or $this->_currChar == "'") {
            $enclosure = $this->_currChar;

            $string = $this->_getString();

            if (null === $string) {
                return new Munch_Token(Munch_Token::INVALID);
            } 

            $this->_nextChar();
        } else if ($this->_isCurrentNonenclosedUriParam()) {
            $enclosure = '';

            $string = $this->_getNonenclosedUriParam();

            if (null === $string) {
                // TODO: this behavior might be incorrect!
                return new Munch_Token(Munch_Token::INVALID);
            } 

            $this->_nextChar();
        } else if (')' == $this->_currChar) {
            $enclosure = '';
            $string = '';
        } else {
            // TODO: this behavior might be incorrect!
            return new Munch_Token(Munch_Token::INVALID);
        }

        $this->_skipToNonWhiteSpace();

        // Close off open parens at the end of stylesheet, as per the CSS 2.1
        // specs.
        if ($this->_currChar == ')' || $this->_currChar === null) {
            return new Munch_Token_Uri($string, $enclosure);
        } else {
            return new Munch_Token(Munch_Token::INVALID);
        }
    }

    /**
     * Scans out a number string from the input css. This method assumes that
     * the current character is a digit or a period followed at least one digit.
     * This is assured when it is called throughout this class.
     * 
     * @return string|null
     */
    protected function _getNumber()
    {
        $haveDecimalPoint = false;
        $numberString = '';

        do {
            if ($this->_currChar == '.') {
                if ($haveDecimalPoint) {
                    break;
                } else {
                    $haveDecimalPoint = true;
                }
            }

            $numberString .= $this->_currChar;

            $this->_nextChar();
        } while ($this->_isCurrentDigit() or $this->_currChar == '.');

        $this->_unputChar();

        if ($numberString[strlen($numberString)-1] == '.') {
            if (strlen($numberString) == 1) {
                // This should never be executed, based upon how this method is
                // called!
                throw new Munch_Scanner_Exception("[ASSERTION] numberString cannot be just a period");
            }

            // We have at least one digit, so we have a number.  But the period 
            // at the end is not part of number, so we need to put it back.
            $this->_unputChar();
            return substr($numberString, 0, -1);
        } else {
            return $numberString;
        }
    }

    /**
     * Scans out an identifier token from the input css. This function does not
     * make any assumptions about the current character. If $getName is true,
     * then the method scans for a name token instead (a name token's first 
     * non-dash character CAN be a digit). If the current character does not 
     * start an identifier (or name) token, then null is returned.
     *
     * @param  string $getName
     * @return null|string
     */
    protected function _getIdent($getName = false)
    {
        if (!$this->_isCurrentIdent($getName)) {
            return null;
        }

        $identValue = '';

        if ($this->_currChar == '-') {
            $identValue = '-';
            $this->_nextChar();

            if (!$getName and $this->_currChar == '-') {
                // an ident token cannot be started by two dashes.
                $this->_unputChar();
                return null;
            }
        }

        if (!$getName and $this->_isCurrentDigit()) {
            // the first non-dash character of an ident cannot be a digit
            if ($identValue == '-') {
                $this->_unputChar();
            }
            return null;
        }

        while ($this->_isCurrentIdent() and null !== $this->_currChar) {
            if (null !== ($escape = $this->_getEscape())) {
                $identValue .= $escape;
            } else {
                $identValue .= $this->_currChar;
            }

            $this->_nextChar();
        }

        $this->_unputChar();

        // an ident token must have at least one non-dash character.
        if (!$getName and $identValue == '-') {
            return null;
        } else {
            return $identValue;
        }
    }

    /**
     * Scans out a string string from the input css. This method assumes
     * that the current character is the starting (left) enclosure character.
     *
     * @return string
     */
    protected function _getString()
    {
        $enclosure = $this->_currChar;

        $this->_nextChar();

        $stringValue = '';

        do {
            if ($this->_currChar == "\r" or
                $this->_currChar == "\n" or
                $this->_currChar == "\x0C") {
                // Unexpected end of string. According to the CSS 2.1 specs:
                // "User agents must close strings upon reaching the end of a 
                // line, but then drop the construct (declaration or rule) in 
                // which the string was found".
                // See: http://www.w3.org/TR/CSS21/syndata.html#parsing-errors
                return null;
            } else if($this->_currChar === null) {
                // End of the stylesheet. According to the CSS 2.1 specs:
                // "User agents must close all open constructs (for example: 
                // blocks, parentheses, brackets, rules, strings, and comments)
                //  at the end of the style sheet."
                // See: http://www.w3.org/TR/CSS21/syndata.html#parsing-errors
                
                // NB - In Firefox, unclosed strings at the end of input are 
                // dropped.

                return $stringValue;
            } else if ($this->_currChar == $enclosure) {
                // The string was properly enclosed.
                return $stringValue;
            }

            // newline character sequences can appear in strings if they are
            // preceded by a backslash.

            if ($this->_peekEquals("\\\n")) {
                $stringValue .= "\n";
                $this->_syncOnLastPeek();
            } else if ($this->_peekEquals("\\\r\n")) {
                $stringValue .= "\r\n";
                $this->_syncOnLastPeek();
            } else if ($this->_peekEquals("\\\r")) {
                $stringValue .= "\r";
                $this->_syncOnLastPeek();
            } else if ($this->_peekEquals("\\\x0C")) {
                $stringValue .= "\x0C";
                $this->_syncOnLastPeek();
            } 

            try {
                if (null !== ($escape = $this->_getEscape())) {
                    $stringValue .= $escape;
                    $this->_nextChar();
                    continue;
                }
            } catch (Munch_Utf8_Exception $e) {
                // Skip codepoints higher than 10ffff and illegal surrogates.
                continue;
            }

            $stringValue .= $this->_currChar;
            $this->_nextChar();
        } while (true);
    }

    /**
     * Scans out the parameter to the url function token. Assumes that
     * the current character is the first character of the parameter.
     *
     * @return string|null
     */
    protected function _getNonenclosedUriParam()
    {
        $stringValue = '';

        do {
            try {
                if (null !== ($escape = $this->_getEscape())) {
                    $stringValue .= $escape;
                } else {
                    $stringValue .= $this->_currChar;
                }
                $this->_nextChar();
            } catch (Munch_Utf8_Exception $e) {
                // Invalid Unicode character was specified ...
                return null;
            }
        } while ($this->_isCurrentNonenclosedUriParam());

        $this->_unputChar();

        return $stringValue;
    }

    /**
     * Scans out an escape sequence.  This method makes no assumptions about
     * the current character.  It returns null if the current character does
     * not start an escape sequence.
     *
     * @return string|null
     */
    protected function _getEscape()
    {
        if ($this->_currChar == '\\') {
            $this->_nextChar();

            if ($this->_isCurrentHex()) {
                $hexValue = '';

                do {
                    $hexValue .= $this->_currChar;
                    $this->_nextChar();
                } while ($this->_isCurrentHex() and strlen($hexValue) < 6);

                if ($this->_peekEquals("\r\n")) {
                    $this->_syncOnLastPeek();
                } else if (!$this->_isCurrentWhiteSpace()) {
                    $this->_unputChar();
                }

                return Munch_Utf8::fromUnicode(array(hexdec($hexValue)));
            } else if($this->_currChar != "\r" and
                      $this->_currChar != "\n" and
                      $this->_currChar != "\x0C") {
                return $this->_currChar;               
            } else {
                // currChar is \r, \n, or \x0C. Firefox and CSS Tidy ignore a 
                // backslash followed by one of these three characters so we
                // do the same. Maybe this behavior should be configurable.
                return '';
            }
        }

        return null;
    }

    /**
     * Skips over groupings of whitespace and C style comments beginning with
     * the current character.
     * 
     * @return void
     */
    protected function _skipComments()
    {
        do {
            $startIndex = $this->_index;

            $this->_skipToNonWhiteSpace();

            if ($this->_peekEquals('/*')) {
                $this->_skipTo('*/');
                $this->_nextChar();
            } else {
                $this->_setCurrentCharIndex($startIndex);
                break;
            }
        } while (true);
    }

    /**
     * Advances the current character to the first character of the next
     * occurrence of $skipTo, and returns true.  If $skipTo does not appear
     * again within the input css, false is returned and the current character
     * is set at the end of the input string.
     *
     * @param  string $skipTo
     * @return boolean true if another occurrence was found, false otherwise.
     */
    protected function _skipTo($skipTo)
    {
        $skipToLen = strlen($skipTo);

        do {
            $compareTo = substr($this->_string, $this->_index, $skipToLen);

            if ($compareTo === $skipTo) {
                $this->_setCurrentCharIndex($this->_index + ($skipToLen - 1));
                return true;
            }
        } while (null !== $this->_nextChar());

        return false;
    }

    /**
     * Advances the current character to the first non-whitespace character.
     * 
     * @return string The string of whitespace skipped.
     */
    protected function _skipToNonWhiteSpace()
    {
        $space = '';

        while ($this->_isCurrentWhiteSpace()) {
            $space .= $this->_currChar;
            $this->_nextChar();
        }

        return $space;
    }

    /**
     * Checks to see if the substring beginning with the current character is
     * equal to $peekEquals.
     *
     * @param  string $peekEquals
     * @return boolean
     */
    protected function _peekEquals($peekEquals)
    {
        $this->_lastPeekLength = strlen($peekEquals);
        $compareTo = substr($this->_string, $this->_index, $this->_lastPeekLength);

        return $compareTo === $peekEquals;
    }

    /**
     * Advances the current character to the last character of previous call to
     * _peekEquals.
     * 
     * @return void
     */
    protected function _syncOnLastPeek()
    {
        $this->_index += $this->_lastPeekLength - 1;

        if ($this->_index >= $this->_stringLength) {
            $this->_index    = $this->_stringLength;
            $this->_currChar = null;
        } else {
            $this->_currChar = $this->_string[$this->_index];
        }
    }

    /**
     * Checks to see if the current character is allowable within an identifier
     * token. If $allowDigits is false, then digits are not considered part of
     * the set of allowed characters.
     *
     * @param  boolean $allowDigits
     * @return boolean
     */
    protected function _isCurrentIdent($allowDigits = true)
    {
        $currCharOrd = ord($this->_currChar);

        return ($currCharOrd > 0x7F) or
               (0x61 <= $currCharOrd and $currCharOrd <= 0x7A) or
               (0x41 <= $currCharOrd and $currCharOrd <= 0x5A) or
               ($allowDigits and 0x30 <= $currCharOrd and $currCharOrd <= 0x39) or
               ('_' == $this->_currChar) or
               ('-' == $this->_currChar) or
               ('\\' == $this->_currChar);
    }

    /**
     * Checks to see if the current character is a whitespace character.
     *
     * @return boolean
     */
    protected function _isCurrentWhiteSpace()
    {
        return "\t" == $this->_currChar or
               "\r" == $this->_currChar or
               "\n" == $this->_currChar or
               // PHP < 5.2.5 does not have the \f escape character.
               "\x0C" == $this->_currChar or
               " " == $this->_currChar;
    }

    /**
     * Checks to see if the current character is a hexadecimal character, that
     * is, a-f, A-F, or 0-9.
     *
     * @return boolean
     */
    protected function _isCurrentHex()
    {
        $currCharOrd = ord($this->_currChar);

        return (0x61 <= $currCharOrd and $currCharOrd <= 0x66) or
               (0x41 <= $currCharOrd and $currCharOrd <= 0x46) or
               (0x30 <= $currCharOrd and $currCharOrd <= 0x39);
    }

    /**
     * Checks to see if the current character is a digit character.
     *
     * @return boolean
     */
    protected function _isCurrentDigit()
    {
        $currCharOrd = ord($this->_currChar);
        return 0x30 <= $currCharOrd and $currCharOrd <= 0x39;
    }

    /**
     * Checks to see if the current character is allowed within an unenclosed
     * url function parameter.
     *
     * @return boolean
     */
    protected function _isCurrentNonenclosedUriParam()
    {
        $currCharOrd = ord($this->_currChar);

        return ($currCharOrd > 0x7F) or
               // Range is * to ~
               (0x2A <= $currCharOrd and $currCharOrd <= 0x7E) or
               ($this->_currChar == '!') or
               ($this->_currChar == '#') or
               ($this->_currChar == '$') or
               ($this->_currChar == '%') or
               ($this->_currChar == '&') or
               ($this->_currChar == '\\');
    }

    /**
     * Puts the current character back into the unprocessed portion of the 
     * css code string. That is, a subsequent call to _nextChar will return
     * the same character. It is safe to repeatedly call this function if one
     * needs to put back more than one character. Throws an exception if
     * the input stream is already one back from the first character.
     *
     * @return void
     * @throws Munch_Scanner_Exception
     */
    protected function _unputChar()
    {
        if (-1 == $this->_index) {
            throw new Munch_Scanner_Exception("cannot unput character at index -1");
        } else if (0 == $this->_index) {
            $this->_currChar = null;
            $this->_index--;
            return $this->_currChar;
        } else {
            $this->_currChar = $this->_string[--$this->_index];
            return $this->_currChar;
        }
    }

    /**
     * Advances the current character to the next unprocessed character of the
     * css code string. If the current character is already at the end of the
     * css code string, then the current character is set to null.
     *
     * @return string|null The current character, or null if at the end of the string. 
     */
    protected function _nextChar()
    {
        if ($this->_index >= $this->_stringLength - 1) {
            $this->_index    = $this->_stringLength;
            $this->_currChar = null;
            return null;
        } else {
            $this->_currChar = $this->_string[++$this->_index];
            return $this->_currChar;
        }
    }

    /**
     * Sets the current character to be the character at the integer $index.
     *
     * @param  integer $index
     * @return void
     */
    protected function _setCurrentCharIndex($index)
    {
        $this->_index    = min(max(-1, $index), $this->_stringLength);
        $this->_currChar = ($this->_index == -1 or $this->_index == $this->_stringLength) ? null : $this->_string[$this->_index];
    }
}
// vim: sw=4:ts=4:sts=4:et
