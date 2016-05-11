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
 * @version    SVN: $Id: ScannerTest.php 46 2008-05-27 00:53:52Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_Scanner
 */
require_once 'Munch/Scanner.php';

/**
 * Tests for Munch_Scanner
 *
 * @category   Security
 * @package    CSS Munch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: ScannerTest.php 46 2008-05-27 00:53:52Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_ScannerTest extends PHPUnit_Framework_TestCase
{
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
        $this->_scanner = new Munch_Scanner();
    }

    /**
     * Test to ensure that no tokens are returned with an empty string as 
     * input.
     *
     * @return void
     */
    public function testEmptyInput()
    {
        $this->_scanner->setInput('');
        $this->assertEquals(null, $this->_scanner->getNextToken());
    }

    /**
     * Test to ensure that comments are not treated as tokens.
     *
     * @return void
     */
    public function testCommentSimple()
    {
        $this->_scanner->setInput('/**/');
        $this->assertEquals(null, $this->_scanner->getNextToken());
    }

    /**
     * Test to ensure multi-line comments are skipped correctly.
     *
     * @return void
     */
    public function testCommentMultiline()
    {
        $this->_scanner->setInput(
            "/*
              * testing
              */ /* testing ... * */");
        
        $this->assertEquals(null, $this->_scanner->getNextToken());
    }

    /*
     * Test to ensure that a token after a comment is scanned correctly.
     *
     * @return void
     */
    public function testCommentAfter()
    {
        $this->_scanner->setInput("/* */ {");

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::LEFT_BRACE, $token->getType());
    }

    /*
     * Test to ensure that a single character is considered the value of an
     * invalid token.
     *
     * @return void
     */
    public function testStateAfterInvalidToken()
    {
        $this->_scanner->setInput('@#foo');

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::INVALID, $token->getType());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::HASH, $token->getType());
        $this->assertEquals('foo', $token->getValue());
    }

    /**
     * Data provider for testTokenSimple
     *
     * @return array
     */
    public static function providerTokenSimple()
    {
        return array(
            array(':', Munch_Token::COLON),
            array(';', Munch_Token::SEMI),
            array('{', Munch_Token::LEFT_BRACE),
            array('}', Munch_Token::RIGHT_BRACE),
            array('[', Munch_Token::LEFT_BRACKET),
            array(']', Munch_Token::RIGHT_BRACKET),
            array('(', Munch_Token::LEFT_PAREN),
            array(')', Munch_Token::RIGHT_PAREN),
            array('/', Munch_Token::SLASH),
            array('*', Munch_Token::STAR),
            array('=', Munch_Token::EQUALS),
            array('+', Munch_Token::PLUS),
            array('>', Munch_Token::GREATER),
            array(',', Munch_Token::COMMA),
            array('-', Munch_Token::MINUS),
            array(' {', Munch_Token::LEFT_BRACE),
            array(' +', Munch_Token::PLUS),
            array(' >', Munch_Token::GREATER),
            array(' ,', Munch_Token::COMMA),
            array('<!--', Munch_Token::CDO),
            array('-->', Munch_Token::CDC),
            array('~=', Munch_Token::INCLUDES),
            array('|=', Munch_Token::DASH_MATCH),
            array('.', Munch_Token::DOT),
            array(" \t", Munch_Token::WHITE_SPACE),

            array('@import', Munch_Token::IMPORT_SYM),
            array("@\\i\\6D\\50\r\nor\\t", Munch_Token::IMPORT_SYM),
            array('@page', Munch_Token::PAGE_SYM),
            array('@media', Munch_Token::MEDIA_SYM),
            array('@charset', Munch_Token::CHARSET_SYM),
            array('@test', Munch_Token::UNKNOWN_AT),

            array('!important', Munch_Token::IMPORTANT_SYM),
        );
    }

    /**
     * Test to determine if basic scanning of tokens works.
     *
     * @param string $in A string containing a token
     * @param string $type The expected type of the token
     * @return void
     * @dataProvider providerTokenSimple
     */
    public function testTokenSimple($in, $type)
    {
        $this->_scanner->setInput($in . '|=');

        $token = $this->_scanner->getNextToken();
        $this->assertEquals($type, $token->getType());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::DASH_MATCH, $token->getType());
    }

    /**
     * Test to determine if scanning a sequence of tokens works correctly.
     *
     * @return void
     */
    public function testSequence()
    {
        $this->_scanner->setInput('+ > --> @import /* hello */ |= url() {');

        $expectedTypes = array(Munch_Token::PLUS,
                               Munch_Token::GREATER,
                               Munch_Token::WHITE_SPACE,
                               Munch_Token::CDC,
                               Munch_Token::WHITE_SPACE,
                               Munch_Token::IMPORT_SYM,
                               Munch_Token::WHITE_SPACE,
                               Munch_Token::DASH_MATCH,
                               Munch_Token::WHITE_SPACE,
                               Munch_Token::URI,
                               Munch_Token::LEFT_BRACE);

        foreach ($expectedTypes as $type) {
            $this->assertEquals($type, $this->_scanner->getNextToken()->getType());
        }
    }

    /**
     * Data provider for testString
     *
     * @return array
     */
    public static function providerString()
    {
        return array(
            array('""', ''),
            array('"test"', 'test'),
            array("'test'", 'test'),

            array('"\\30"', '0'),
            array('"\\100"', "\xC4\x80"),
            array('"x\\100x"', "x\xC4\x80x"),
            array("'\\100\r\nx'", "\xC4\x80x"),
            array('"\\100 x"', "\xC4\x80x"),
            array("'\\100\rx'", "\xC4\x80x"),
            array("'\\100\nx'", "\xC4\x80x"),
            array("'\\100\tx'", "\xC4\x80x"),
            array("'\\030\x0Cx'", '0x'),

            array("'\\\\'", '\\'),
            array("'\\g'", 'g'),
            array("'\\g\\gx'", 'ggx'),
            array("'\\g\\00030 \\ x'", 'g0 x'),
        );
    }

    /**
     * Test to determine if scanning of string tokens works
     *
     * @param string $in A string token to be scanned
     * @param string value The expected value of the string token
     * @return void
     * @dataProvider providerString
     */
    public function testString($in, $value)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertType('Munch_Token_String', $token);
        $this->assertEquals(Munch_Token::STR, $token->getType());
        $this->assertEquals($value, $token->getValue());
    }

    /**
     * Data provider for testUnclosedEndOfInput.
     *
     * @return void
     */
    public static function providerUnclosedEndOfInput()
    {
        return array(
            array("'test", 'test'),
            array('"test', 'test'),
            array("'", ''),
            array('"', '')
        );
    }

    /**
     * Test to determine if the scanner behaves properly when the end of the
     * stylesheet is encountered in the middle of scanning a string.
     *
     * @param string $in A string token to scan
     * @param string $value The value of the string
     * @return void
     * @dataProvider providerUnclosedEndOfInput
     */
    public function testUnclosedEndOfInput($in, $value)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertType('Munch_Token', $token);
        $this->assertEquals(Munch_Token::STR, $token->getType());
        $this->assertEquals($value, $token->getValue());
    }

    /**
     * Data provider for testUri
     *
     * @return array
     */
    public static function providerUri()
    {
        return array(
            array('url("")', ''),
            array('url("http://foo.com/foo.css")', 'http://foo.com/foo.css'),
            array("url('http://foo.com/foo.css')", 'http://foo.com/foo.css'),

            array("url()", ''),
            array("url(http://x.y)", 'http://x.y'),
            array("url( test )", 'test'),

            array('UrL(test)', 'test'),
            array('\\75\\000072\\6C(test)', 'test')
        );
    }

    /**
     * Test to ensure the scanning of uri tokens works
     *
     * @param string $in A uri token to scan
     * @param string $value The expected value of the uri token
     * @return void
     * @dataProvider providerUri
     */
    public function testUri($in, $value)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertType('Munch_Token_Uri', $token);
        $this->assertEquals(Munch_Token::URI, $token->getType());
        $this->assertEquals($value, $token->getValue());
    }

    /**
     * Data provider for testUriInvalid.
     *
     * @return array
     */
    public static function providerUriInvalid()
    {
        return array(
            array("url("),
            array("url(x x)"),
            array("url(()"),
            array("url(x\\\r\nx)"),
            array("url(x')"),
            array('url(x")'),
            array('url(()'),
        );
    }

    /**
     * Test to ensure the scanner handles invalid uri tokens correctly.
     *
     * @param string $in A uri token to parse
     * @return void
     * @dataProvider providerUriInvalid
     */
    public function testUriInvalid($in)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::INVALID, $token->getType());
    }

    /**
     * Data provider for testIdent.
     *
     * @return array
     */
    public static function providerIdent()
    {
        return array(
            array('hello', 'hello'),
            array('x12345-', 'x12345-'),
            array('x12--345-', 'x12--345-'),
            array('-hello', '-hello'),

            array('abc\\100 def', "abc\xC4\x80def"),
            array("\xC4\x80", "\xC4\x80"),
            array('\\000064iv', 'div'),
            array('foo\!', 'foo!'),
            array('foo\\21', 'foo!'),
            array('foo\\', 'foo'),
            array("foo\\\nbar", 'foobar'),

            array('AZaz_09-h', 'AZaz_09-h')
        );
    }

    /**
     * Test to ensure the scanning of ident tokens works.
     *
     * @param string $in An ident token to scan
     * @param string $value The expected token value
     * @return void
     * @dataProvider providerIdent
     */
    public function testIdent($in, $value)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertType('Munch_Token_Identifier', $token);
        $this->assertEquals(Munch_Token::IDENT, $token->getType());
        $this->assertEquals($value, $token->getValue());
    }

    /**
     * Data provider for testNegativeNumber.
     *
     * @return array
     */
    public static function providerNegativeNumber()
    {
        return array(
            array('-1', '1'),
            array('-1.5', '1.5')
        );
    }

    /**
     * Test to ensure that negative number token sequences are not scanned as
     * ident tokens.
     *
     * @param string $in Input css to scan
     * @param string $value The expected number value
     * @return void
     * @dataProvider providerNegativeNumber
     */
    public function testNegativeNumber($in, $value)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::MINUS, $token->getType());

        $token = $this->_scanner->getNextToken();
        $this->assertType('Munch_Token_Number', $token);
        $this->assertEquals($value, $token->getValue());
    }

    /**
     * Test to ensure that the scanner returns the correct sequence of tokens
     * when encountering two dashes in a row.
     *
     * @return void
     */
    public function testDashDash()
    {
        $this->_scanner->setInput('--');

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::MINUS, $token->getType());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::MINUS, $token->getType());
    }

    /**
     * Test to ensure that the scanner behaves correctly (ie does not skip a 
     * character) at the end of an ident token.
     *
     * @return void
     */
    public function testAfterIdent()
    {
        $this->_scanner->setInput('test>');

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::IDENT, $token->getType());
        $this->assertEquals('test', $token->getValue());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::GREATER, $token->getType());
    }

    /**
     * Test to ensure that the scanner handles two idents separated by white
     * space correctly.
     *
     * @return void
     */
    public function testTwoIdents()
    {
        $this->_scanner->setInput("test \t\r\n -he--llo");

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::IDENT, $token->getType());
        $this->assertEquals('test', $token->getValue());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::WHITE_SPACE, $token->getType());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::IDENT, $token->getType());
        $this->assertEquals('-he--llo', $token->getValue());
    }

    /**
     * Data provider for testHash.
     *
     * @return array
     */
    public static function providerHash()
    {
        return array(
            array('#test', 'test'),
            array('#0123', '0123'),
            array('#--xy', '--xy'),
            array('#\\30 x', '0x'),
            );
    }

    /**
     * Tests to ensure that hash tokens are scanned properly.
     *
     * @param string $in The css to scan.
     * @param string $value The expected value of the hash.
     * @return void
     * @dataProvider providerHash
     */
    public function testHash($in, $value)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::HASH, $token->getType());
        $this->assertEquals($value, $token->getValue());
    }

    /**
     * Data provider for testImportantSym.
     *
     * @return array
     */
    public static function providerImportantSym()
    {
        return array(
            array('!important'),
            array("!\r\n\t\x0Cimportant"),
            array("!impor\\74 an\\54"),
            array('!\\i\\m\\pORTant'),
            
            array('! important'),
            array('! /* comment */ important'),
            array('!/* comment */important'),
            array('! /* comment */ /* another ... * /// */important'),
            );
    }

    /**
     * Test to ensure that the important symbol is scanned correctly.
     *
     * @param string $in The css to scan.
     * @return void
     * @dataProvider providerImportantSym
     */
    public function testImportantSym($in)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::IMPORTANT_SYM, $token->getType());
    }

    /**
     * Data provider for testFunc.
     *
     * @return array
     */
    public static function providerFunc()
    {
        return array(
            array('hello(', 'hello'),
            array('abc\\100 def(', "abc\xC4\x80def"),
            array('x12345-(', 'x12345-')
        );
    }

    /**
     * Test to ensure that function tokens are scanned correctly.
     *
     * @param string $in The css to scan.
     * @param string $value The expected function name.
     * @return void
     * @dataProvider providerFunc
     */
    public function testFunc($in, $value)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertType('Munch_Token_Function', $token);
        $this->assertEquals(Munch_Token::FUNC, $token->getType());
        $this->assertEquals($value, $token->getValue());
    }

    /**
     * Data provider for testNumberBased.
     *
     * @return array
     */
    public static function providerNumberBased()
    {
        return array(
            array('0', '0', null, Munch_Token::NUMBER, 'Munch_Token_Number'),
            array('10', '10', null, Munch_Token::NUMBER, 'Munch_Token_Number'),
            array('1.05', '1.05', null, Munch_Token::NUMBER, 'Munch_Token_Number'),
            array('.01', '.01', null, Munch_Token::NUMBER, 'Munch_Token_Number'),
            array('.0', '.0', null, Munch_Token::NUMBER, 'Munch_Token_Number'),
            array('1em', '1', 'em', Munch_Token::EMS, 'Munch_Token_Ems'),
            array('.5em', '.5', 'em', Munch_Token::EMS, 'Munch_Token_Ems'),
            array('0.5em', '0.5', 'em', Munch_Token::EMS, 'Munch_Token_Ems'),
            array('0\\45 \\6d', '0', 'em', Munch_Token::EMS, 'Munch_Token_Ems'),
            array('0e\\6d', '0', 'em', Munch_Token::EMS, 'Munch_Token_Ems'),
            array('1ex', '1', 'ex', Munch_Token::EXS, 'Munch_Token_Exs'),
            array('1deg', '1', 'deg', Munch_Token::ANGLE, 'Munch_Token_Angle'),
            array('1rad', '1', 'rad', Munch_Token::ANGLE, 'Munch_Token_Angle'),
            array('1grad', '1', 'grad', Munch_Token::ANGLE, 'Munch_Token_Angle'),
            array('1foo', '1', 'foo', Munch_Token::DIMENSION, 'Munch_Token_Dimension'),
            array('1hz', '1', 'hz', Munch_Token::FREQ, 'Munch_Token_Frequency'),
            array('1khz', '1', 'khz', Munch_Token::FREQ, 'Munch_Token_Frequency'),
            array('1px', '1', 'px', Munch_Token::LENGTH, 'Munch_Token_Length'),
            array('1cm', '1', 'cm', Munch_Token::LENGTH, 'Munch_Token_Length'),
            array('1mm', '1', 'mm', Munch_Token::LENGTH, 'Munch_Token_Length'),
            array('1in', '1', 'in', Munch_Token::LENGTH, 'Munch_Token_Length'),
            array('1pt', '1', 'pt', Munch_Token::LENGTH, 'Munch_Token_Length'),
            array('1pc', '1', 'pc', Munch_Token::LENGTH, 'Munch_Token_Length'),
            array('1%', '1', '%', Munch_Token::PERCENTAGE, 'Munch_Token_Percentage'),
            array('1ms', '1', 'ms', Munch_Token::TME, 'Munch_Token_Time'),
            array('1s', '1', 's', Munch_Token::TME, 'Munch_Token_Time')
        );
    }

    /**
     * Test to ensure that tokens with numbers at the start are scanned 
     * correctly.
     *
     * @param string $in The css to scan.
     * @param string $number The expected number value.
     * @param string|null $unit The expected unit value, if any.
     * @param string $tokenType The expected token type.
     * @param string $tokenClass The expected class of the token instance.
     * @return void
     * @dataProvider providerNumberBased
     */
    public function testNumberBased($in, $number, $unit, $tokenType, $tokenClass)
    {
        $this->_scanner->setInput($in);
        $token = $this->_scanner->getNextToken();
        $this->assertType($tokenClass, $token);
        $this->assertEquals($tokenType, $token->getType());
        $this->assertEquals($number, $token->getValue());

        if (null !== $unit) {
            $this->assertEquals($unit, $token->getUnit());
        }
    }

    /**
     * Test to ensure that a number, followed by a period, followed by an ident
     * is scanned correctly.
     *
     * @return void
     */
    public function testNumberThenPeriodThenIdent()
    {
        $this->_scanner->setInput('0.25.foo');
        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::NUMBER, $token->getType());
        $this->assertEquals('0.25', $token->getValue());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::DOT, $token->getType());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::IDENT, $token->getType());
        $this->assertEquals('foo', $token->getValue());
    }

    /**
     * Test to ensure that an integer followed by a period is not scanned as a
     * float.
     *
     * @return void
     */
    public function testIntegerThenPeriod()
    {
        $this->_scanner->setInput('100.');
        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::NUMBER, $token->getType());
        $this->assertEquals('100', $token->getValue());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::DOT, $token->getType());
    }

    /**
     * Test to ensure that an integer followed by a period followed by an
     * ident is scanned correctly.
     *
     * @return void
     */
    public function testIntegerThenPeriodThenIdent()
    {
        $this->_scanner->setInput('100.em');
        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::NUMBER, $token->getType());
        $this->assertEquals('100', $token->getValue());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::DOT, $token->getType());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::IDENT, $token->getType());
        $this->assertEquals('em', $token->getValue());
    }

    /**
     * Test to ensure that an integer followed by two periods is scanned
     * correctly.
     *
     * @return void
     */
    public function testIntegerThenTwoPeriods()
    {
        $this->_scanner->setInput('100..');
        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::NUMBER, $token->getType());
        $this->assertEquals('100', $token->getValue());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::DOT, $token->getType());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::DOT, $token->getType());
    }

    /**
     * Test to ensure that two periods are scanned correctly.
     *
     * @return void
     */
    public function testTwoPeriods()
    {
        $this->_scanner->setInput('..');
        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::DOT, $token->getType());

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::DOT, $token->getType());
    }

    /**
     * Test to ensure that a unicode escape sequence is limited to six 
     * characters (ie, below, ensure that the ending f is not treated
     * as part of the escape sequence).
     *
     * @return void
     */
    public function testLongUnicode()
    {
        $this->_scanner->setInput("'\\000030f'");

        $token = $this->_scanner->getNextToken();
        $this->assertEquals(Munch_Token::STR, $token->getType());
        $this->assertEquals('0f', $token->getValue());
    }
}
// vim: sw=4:ts=4:sts=4:et
