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
 * @version    SVN: $Id: AllTests.php 29 2008-05-10 20:22:57Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Munch_AllTests::main');
}
 
require_once 'bootstrap.php';

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'Munch/Utf8Test.php';
require_once 'Munch/ParserTest.php';
require_once 'Munch/ScannerTest.php';

/**
 * AllTests for classes in Munch.
 *
 * @category   Security
 * @package    CSS Munch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: AllTests.php 29 2008-05-10 20:22:57Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_AllTests
{
    public static function main()
    {   
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }   
 
    public static function suite()
    {   
        $suite = new PHPUnit_Framework_TestSuite('Munch');

        $suite->addTestSuite('Munch_Utf8Test');
        $suite->addTestSuite('Munch_ParserTest');
        $suite->addTestSuite('Munch_ScannerTest');

        return $suite;
    }   
}
 
if (PHPUnit_MAIN_METHOD == 'Munch_AllTests::main') {
    Munch_AllTests::main();
}
// vim: sw=4:ts=4:sts=4:et
