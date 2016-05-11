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
 * @package    CSS Munch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz <cutz@chrisutz.com>
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: ParserTest.php 55 2008-06-07 04:09:09Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_Parser
 */
require_once 'Munch/Parser.php';

/**
 * Tests for Munch_Parser
 *
 * @category   Security
 * @package    CSS Munch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: ParserTest.php 55 2008-06-07 04:09:09Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Munch_Parser
     */
    protected $_parser  = null;

    /**
     * @var Munch_Scanner
     */
    protected $_scanner = null;

    /**
     * Setup method for this battery of tests.
     * 
     * @return void
     */
    public function setUp()
    {
        $this->_parser  = new Munch_Parser();
        $this->_scanner = $this->_parser->getScanner();
    }

    /**
     * Test to ensure that the expected exception is thrown when the first
     * parameter of the constructor of Munch_Parser does not implement
     * Munch_Scanner_Interface.
     *
     * @return void
     * @expectedException Munch_Parser_Exception
     */
    public function testInvalidScanner()
    {
        $parser = new Munch_Parser(new stdClass());
    }

    /**
     * Test to ensure that the default scanner used when no scanner is passed
     * to the constructor of Munch_Parser is Munch_Scanner.
     */
    public function testConstructorNoParams()
    {
        $this->assertInstanceOf('Munch_Scanner', $this->_parser->getScanner());
    }

    /**
     * Test to ensure that the @charset rule is parsed correctly.
     *
     * @return void
     */
    public function testCharset()
    {
        $this->_parser->getScanner()->setInput('@charset "utf-8";');
        $stylesheet = $this->_parser->parse();

        $this->assertEquals('utf-8', $stylesheet->charset);
    }

    /**
     * Test to ensure that a @charset rule with extra spaces before the
     * string token is not allowed.
     *
     * @return void
     */
    public function testCharsetTooManySpaces()
    {
        $this->_parser->getScanner()->setInput('@charset  "utf-8";');
        $stylesheet = $this->_parser->parse();

        $this->assertEquals(null, $stylesheet->charset);
    }

    /**
     * Test to ensure that a @charset rule with the wrong string enclosure
     * character (ie, ' not ") is not allowed.
     *
     * @return void
     */
    public function testCharsetWrongEnclosure()
    {
        $this->_parser->getScanner()->setInput("@charset 'utf-8';");
        $stylesheet = $this->_parser->parse();

        $this->assertEquals(null, $stylesheet->charset);
    }

    /**
     * Test to ensure that an @charset followed by an @import rule
     * is parsed correctly.
     *
     * @return void
     */
    public function testCharsetThenImport()
    {
        $input = "@charset \"utf-8\";\n@import url('http://foo.com');";
        $this->_parser->getScanner()->setInput($input);

        $stylesheet = $this->_parser->parse();

        $this->assertEquals('utf-8', $stylesheet->charset);
        $this->assertEquals('http://foo.com', $stylesheet->imports[0]->uri);
        $this->assertEquals(array(), $stylesheet->imports[0]->mediums);
    }

    /**
     * Data provider for testImport.
     *
     * @return array
     */
    public static function providerImport()
    {
        return array(
            array('@import "http://foo.com";', 'http://foo.com', array()),
            array('@import"http://foo.com";', 'http://foo.com', array()),
            array("@import \t'http://foo.com' ;", 'http://foo.com', array()),
            array("@import \turl( http://foo.com?x );", 'http://foo.com?x', array()),
            array("@import url() x;", '', array('x')),
            array("@import url() x,y,z;", '', array('x', 'y', 'z')),
            array("@import url() x, y,z;", '', array('x', 'y', 'z')),
            array(" @import\n'foo' screen, a-b\t;", 'foo', array('screen', 'a-b'))
        );
    }

    /**
     * Various tests to ensure that @import rules are parsed correctly.
     *
     * @param string $input The CSS to parse.
     * @param string $expUri The uri of the import rule.
     * @param string $expMediums The mediums of the import rule.
     * @dataProvider providerImport
     */
    public function testImport($input, $expUri, $expMediums)
    {
        $this->_parser->getScanner()->setInput($input);

        $stylesheet = $this->_parser->parse();

        $this->assertEquals($expUri, $stylesheet->imports[0]->uri);
        $this->assertEquals(count($expMediums), count($stylesheet->imports[0]->mediums));

        for($i=0; $i < count($expMediums); $i++) {
            $this->assertEquals($expMediums[$i], $stylesheet->imports[0]->mediums[$i]->value);
        }
    }

    /**
     * Test to ensure that two imports in succession are parsed correctly.
     *
     * @return void
     */
    public function testTwoImports()
    {
        $this->_scanner->setInput('@import "foo" all; <!-- --> @iMport "bar" screen,tv;');

        $stylesheet = $this->_parser->parse();

        $this->assertEquals(2, count($stylesheet->imports));
        $this->assertEquals('foo', $stylesheet->imports[0]->uri);
        $this->assertEquals('all', $stylesheet->imports[0]->mediums[0]->value);
        $this->assertEquals('bar', $stylesheet->imports[1]->uri);
        $this->assertEquals('screen', $stylesheet->imports[1]->mediums[0]->value);
        $this->assertEquals('tv', $stylesheet->imports[1]->mediums[1]->value);
    }

    /**
     * Data provider for testDeclarationRecover.
     *
     * @return array
     */
    public static function providerDeclarationRecover()
    {
        return array(
            array('@import (");@import \'foo\'";@import "foo";); @import "bar";'),
            array('@import {"};@import \'foo\'";@import "foo";}; @import "bar";'),
            array('@import ["];@import \'foo\'";@import "foo";]; @import "bar";'),
            array('@import @foo ((;@import "xxx";)); @import "bar";'),
            array('@import @foo {{;@import "xxx";}}; @import "bar";'),
            array('@import @foo [[;@import "xxx";]]; @import "bar";'),
        );
    }

    /**
     * Tests to ensure that parser recovers at the correct semicolon after an
     * unexpected token (ie, the parser should not be fouled up by semicolons
     * appearing within nested parens, curly brackets, or braces.
     *
     * @param string $input The CSS to test
     * @return void
     * @dataProvider providerDeclarationRecover
     */
    public function testDeclarationRecover($input)
    {
        $this->_scanner->setInput($input);

        $stylesheet = $this->_parser->parse();

        $this->assertEquals(1, count($stylesheet->imports));
        $this->assertEquals('bar', $stylesheet->imports[0]->uri);
    }

    /**
     *
     */
    public static function providerSelectorRecover()
    {
        return array(
            array('x;{} x{}'),
            array('x! { x{}}x{}'),
            array('x!({}x{})x{}x{}'), 
            array('x![{}x{}]x{}x{}'), 
        );
    }

    /**
     * Test to ensure that errors occurring during the parsing of selectors are
     * recovered from correctly.
     *
     * @param string $input The css to parse
     * @return void
     * @dataProvider providerSelectorRecover
     */
    public function testSelectorRecover($input)
    {
        $this->_scanner->setInput($input);
        $stylesheet = $this->_parser->parse();

        $this->assertEquals(1, count($stylesheet->rulesets));
    }

    /**
     * Data provider for testRulesetNoDeclarations
     *
     * @return array
     */
    public static function providerRulesetNoDeclarations()
    {
        return array(
            array('x{}'),
            array('x{ }'),
            array('x {}'),
            array('x { } '),
            array('x {;}'),
            array('x { ;}'),
            array('x {; }'),
            array('x { ; }'),
            array('x { ;; }'),
            array('x { ; ; }')
        );
    }

    /**
     * Test to ensure that rulesets with no declarations are parsed correctly.
     *
     * @param string $input The css to parse.
     * @return void
     * @dataProvider providerRulesetNoDeclarations
     */
    public function testRulesetNoDeclarations($input)
    {
        $this->_scanner->setInput($input);
        $stylesheet = $this->_parser->parse();

        $this->assertEquals(1, count($stylesheet->rulesets));
        $this->assertEquals(0, count($stylesheet->rulesets[0]->declarations));
    }

    /**
     * Test to ensure that many rulesets in a row are parsed correctly.
     *
     * @return void
     */
    public function testManyRulesets()
    {
        $this->_scanner->setInput('x {x:x;y:y} y {} z{}');
        $stylesheet = $this->_parser->parse();

        $this->assertEquals(3, count($stylesheet->rulesets));
    }

    /**
     * Data provider for testCombinators.
     *
     * @return array
     */
    public static function providerCombinators()
    {
        return array(
            array('.x > .y { }', '>'),
            array('.x + .y { }', '+'),
            array('.x .y { }', ' ')
        );
    }

    /**
     *
     * @param string $input The css to parse
     * @param string $combinator The expected combinator
     * @return void
     * @dataProvider providerCombinators
     */
    public function testCombinators($input, $combinator)
    {
        $this->_scanner->setInput($input);
        $stylesheet = $this->_parser->parse();

        $selector = $stylesheet->rulesets[0]->selectors[0];
        $this->assertEquals(2, count($selector->selectorGroups));

        $this->assertInstanceOf('Munch_AstNode_SimpleSelector_Class', $selector->selectorGroups[0]->simpleSelectors[0]);
        $this->assertInstanceOf('Munch_AstNode_SimpleSelector_Class', $selector->selectorGroups[1]->simpleSelectors[0]);

        $this->assertEquals(1, count($selector->combinators));
        $this->assertInstanceOf('Munch_AstNode_Combinator', $selector->combinators[0]);
        $this->assertEquals($combinator, $selector->combinators[0]->combinatorType);
    }

    /**
     * Data provider for testClassAndIdSelector
     *
     * @return array
     */
    public static function providerClassAndIdSelector()
    {
        return array(
            array('Class', '.selector { }', null),
            array('Class', 'name.selector { }', 'name'),
            array('Class', '*.selector { }', '*'),
            array('Id', '#selector { }', null),
            array('Id', 'name#selector { }', 'name'),
            array('Id', '*#selector { }', '*')
        );
    }

    /**
     * Test to ensure that class/id selectors are parsed correctly.
     *
     * @param string $selectorType
     * @param string $input
     * @param string $elementName
     * @return void
     * @dataProvider providerClassAndIdSelector
     */
    public function testClassAndIdSelector($selectorType, $input, $elementName)
    {
        $this->_scanner->setInput($input);

        $stylesheet = $this->_parser->parse();

        $this->assertEquals(1, count($stylesheet->rulesets));
        $this->assertEquals(1, count($stylesheet->rulesets[0]->selectors));

        $selector = $stylesheet->rulesets[0]->selectors[0];

        $this->assertEquals(1, count($selector->selectorGroups));

        $selectorGroup = $selector->selectorGroups[0];

        switch ($elementName) {
            case null:
                $this->assertEquals(null, $selectorGroup->elementName);
                break;
            case '*':
                $this->assertEquals(Munch_AstNode_ElementName::UNIVERSAL, $selectorGroup->elementName->type);
                break;
            default:
                $this->assertEquals(Munch_AstNode_ElementName::ELEMENT, $selectorGroup->elementName->type);
                $this->assertEquals($elementName, $selectorGroup->elementName->name);
                break;
        }

        $this->assertEquals(1, count($selectorGroup->simpleSelectors));
        $this->assertInstanceOf('Munch_AstNode_SimpleSelector_' . $selectorType, $selectorGroup->simpleSelectors[0]);
        $this->assertEquals('selector', $selectorGroup->simpleSelectors[0]->value);
    }

    /**
     * Data provider for testAttributeSelector.
     *
     * @return array
     */
    public static function providerAttributeSelector()
    {
        return array(
            array('[x=x] { }', 'x', '=', 'x'),
            array('*[x=x] { }', 'x', '=', 'x'),
            array('x[x=x] { }', 'x', '=', 'x'),
            array('x[x~=x] { }', 'x', '~=', 'x'),
            array('x[x|=x] { }', 'x', '|=', 'x'),
            array('x[ x = "x" ] { }', 'x', '=', 'x'),
        );
    }

    /**
     * Test to ensure attribute selectors are parsed correctly.
     *
     * @param string $input The css to parse.
     * @param string $attribute The expected attribute.
     * @param string $operator The expected operator.
     * @param string $value The expected value.
     * @return void
     * @dataProvider providerAttributeSelector
     */
    public function testAttributeSelector($input, $attribute, $operator, $value)
    {
        $this->_scanner->setInput($input);
        $stylesheet = $this->_parser->parse();

        $selectorGroup = $stylesheet->rulesets[0]->selectors[0]->selectorGroups[0];

        $this->assertEquals(1, count($selectorGroup->simpleSelectors));
        $simpleSelector = $selectorGroup->simpleSelectors[0];

        $this->assertInstanceOf('Munch_AstNode_SimpleSelector_Attribute', $simpleSelector);
        $this->assertEquals($attribute, $simpleSelector->attribute);
        $this->assertEquals($operator, $simpleSelector->operator);
        $this->assertEquals($value, $simpleSelector->value);
    }

    /**
     * Data provider for testPseudoSelector.
     *
     * @return array
     */
    public static function providerPseudoSelector()
    {
        return array(
            array('x:x { }', 'x', null),
            array('x:x(x) { }', 'x', 'x'),
            array('x:x( x ) { }', 'x', 'x'),
            array('x:x() { }', 'x', ''),
            array('x:x( ) { }', 'x', '')
        );
    }

    /**
     *
     * @dataProvider providerPseudoSelector
     */
    public function testPseudoSelector($input, $pseudo, $value)
    {
        $this->_scanner->setInput($input);
        $stylesheet = $this->_parser->parse();

        $selectorGroup = $stylesheet->rulesets[0]->selectors[0]->selectorGroups[0];

        $this->assertEquals(1, count($selectorGroup->simpleSelectors));
        $simpleSelector = $selectorGroup->simpleSelectors[0];

        $this->assertInstanceOf('Munch_AstNode_SimpleSelector_Pseudo', $simpleSelector);
        $this->assertEquals($pseudo, $simpleSelector->pseudo);
        $this->assertSame($value, $simpleSelector->value);
    }

    /**
     * Test to ensure that many simple selectors in a row are parsed correctly.
     *
     * @return void
     */
    public function testManySimpleSelectors()
    {
        $this->_scanner->setInput('a.x[y=y][z=z]#w { }');
        $stylesheet = $this->_parser->parse();

        $selectorGroup = $stylesheet->rulesets[0]->selectors[0]->selectorGroups[0];

        $this->assertEquals(4, count($selectorGroup->simpleSelectors));
        $this->assertInstanceOf('Munch_AstNode_SimpleSelector_Class', $selectorGroup->simpleSelectors[0]);
        $this->assertInstanceOf('Munch_AstNode_SimpleSelector_Attribute', $selectorGroup->simpleSelectors[1]);
        $this->assertInstanceOf('Munch_AstNode_SimpleSelector_Attribute', $selectorGroup->simpleSelectors[2]);
        $this->assertInstanceOf('Munch_AstNode_SimpleSelector_Id', $selectorGroup->simpleSelectors[3]);
    }

    /**
     * Data provider for testTerm.
     *
     * @return array
     */
    public static function providerTerm()
    {
        return array(
            array('1', null, 'Number', '1', null),
            array('-1', '-', 'Number', '1', null),
            array('+1', '+', 'Number', '1', null),
            array('1.5%', null, 'Percentage', '1.5', null),
            array('1cm', null, 'Length', '1', 'cm'),
            array('-1.52em', '-', 'Ems', '1.52', 'em'),
            array('0ex', null, 'Exs', '0', 'ex'),
            array('1rad', null, 'Angle', '1', 'rad'),
            array('400.2ms', null, 'Time', '400.2', 'ms'),
            array('2hz', null, 'Frequency', '2', 'hz'),
            array('"x"', null, 'String', 'x', null),
            array('-f', null, 'Identifier', '-f', null),
            array('url("http://foo.com")', null, 'Uri', 'http://foo.com', null),
            array('#abcdef', null, 'HexColor', 'abcdef', null)
        );
    }

    /**
     * Test to ensure that terms are parsed correctly.
     *
     * @param string $input The css to parse
     * @param string|null $unaryOperator The expected unary operator, if any
     * @param string $termType The type of term token expected
     * @param string $termValue The term value expected
     * @param string|null The term unit expected, if any
     * @return void
     * @dataProvider providerTerm
     */
    public function testTerm($input, $unaryOperatorType, $termType, $termValue, $termUnit)
    {
        $this->_scanner->setInput('x { x : ' . $input . ' ; }');

        $stylesheet = $this->_parser->parse();

        $this->assertEquals(1, count($stylesheet->rulesets));
        
        $ruleset = $stylesheet->rulesets[0];
        $this->assertInstanceOf('Munch_AstNode_Ruleset', $ruleset);
        $this->assertEquals(1, count($ruleset->declarations));

        $declaration = $ruleset->declarations[0];
        $this->assertInstanceOf('Munch_AstNode_Declaration', $declaration);
        $this->assertInstanceOf('Munch_AstNode_Property', $declaration->property);

        $this->assertEquals('x', $declaration->property->value);

        $expression = $declaration->expression;
        $this->assertInstanceOf('Munch_AstNode_Expression', $expression);

        $this->assertEquals(1, count($expression->terms));
        $this->assertEquals(0, count($expression->operators));

        $term = $expression->terms[0];
        $this->assertInstanceOf('Munch_AstNode_Term_' . $termType, $term);

        if ($unaryOperatorType) {
            $this->assertEquals($unaryOperatorType, $term->unaryOperator->type);
        } else {
            $this->assertEquals(null, $term->unaryOperator);
        }

        $this->assertEquals($termValue, $term->value);

        if ($termUnit) {
            $this->assertEquals($termUnit, $term->unit);
        }
    }

    /**
     * Data provider for testFunctionTerm.
     *
     * @return array
     */
    public static function providerFunctionTerm()
    {
        return array(
            array('x{x:foo(bar("\\78",hello-world 1%));}'),
            array(' x { x : foo( bar( "\\78" , hello-world 1% ) ) ; } '),
        );
    }

    /**
     * Test to ensure that function terms are parsed correctly.
     *
     * @param string $input The css to parse
     * @return void
     * @dataProvider providerFunctionTerm
     */
    public function testFunctionTerm($input)
    {
        $this->_scanner->setInput($input);

        $stylesheet = $this->_parser->parse();

        $this->assertEquals(1, count($stylesheet->rulesets));
        $this->assertEquals(1, count($stylesheet->rulesets[0]->declarations));
        $this->assertEquals(1, count($stylesheet->rulesets[0]->declarations[0]->expression->terms));
        $term = $stylesheet->rulesets[0]->declarations[0]->expression->terms[0];

        $this->assertInstanceOf('Munch_AstNode_Term_Function', $term);
        $func = $term->function;

        $this->assertEquals(1, count($func->expression->terms));

        $term = $func->expression->terms[0];

        $this->assertInstanceOf('Munch_AstNode_Term_Function', $term);
        $func = $term->function;

        $this->assertEquals(3, count($func->expression->terms));
        $terms = $func->expression->terms;

        $this->assertInstanceOf('Munch_AstNode_Term_String', $terms[0]);
        $this->assertEquals('x', $terms[0]->value);
        $this->assertInstanceOf('Munch_AstNode_Term_Identifier', $terms[1]);
        $this->assertEquals('hello-world', $terms[1]->value);
        $this->assertInstanceOf('Munch_AstNode_Term_Percentage', $terms[2]);
        $this->assertEquals('1', $terms[2]->value);

        $this->assertEquals(2, count($func->expression->operators));
        $operators = $func->expression->operators;

        $this->assertInstanceOf('Munch_AstNode_Operator', $operators[0]);
        $this->assertEquals(Munch_AstNode_Operator::COMMA, $operators[0]->type);
        $this->assertInstanceOf('Munch_AstNode_Operator', $operators[1]);
        $this->assertEquals(Munch_AstNode_Operator::SPACE, $operators[1]->type);
    }

    /**
     * Data provider for testDeclarationRecoverInRuleset
     *
     * @return array
     */
    public static function providerDeclarationRecoverInRuleset()
    {
        return array(
            array('x { x: !; x: x }'),
            array('x { x: {;x:x;"}"};x:x; }'),
            array('x { x: [;x:x;"]"];x:x; }'),
            array('x { x: (;x:x;")");x:x; }'),
            array('x { x:x; x }'),
            array('x { x:({)};x:x;);x:x; }'),
            array('x { x:{)};x:x;); }'),
            array('x { x:x; x }'),
            array('x { x:x; x: }'),
            array('x { x; x:x }'),
            array('x { x:; x:x }'),
        );
    }

    /**
     * Test to ensure that parse errors in declarations are recovered from 
     * properly.
     *
     * @param string $input The css to parse.
     * @return void
     * @dataProvider providerDeclarationRecoverInRuleset
     */
    public function testDeclarationRecoverInRuleset($input)
    {
        $this->_scanner->setInput($input);
        $stylesheet = $this->_parser->parse();

        $this->assertEquals(1, count($stylesheet->rulesets));
        $declarations = $stylesheet->rulesets[0]->declarations;
        $this->assertEquals(1, count($declarations));
    }

    /**
     * Test to ensure that an unclosed block at the end of the stylesheet 
     * treated as if it were closed by the parser.
     *
     * @return void 
     */
    public function testRulesetEndOfStylesheet()
    {
        $this->_scanner->setInput('x { x:x; ');
        $stylesheet = $this->_parser->parse();

        $this->assertEquals(1, count($stylesheet->rulesets));
    }
}
// vim: sw=4:ts=4:sts=4:et
