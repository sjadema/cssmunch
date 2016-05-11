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
 * @version    SVN: $Id: Utf8.php 28 2008-05-10 15:39:19Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_Utf8_Exception
 */
require_once 'Munch/Utf8/Exception.php';

/**
 * Various helper functions for dealing with UTF8. 
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Utf8.php 28 2008-05-10 15:39:19Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_Utf8
{
    /**
     * Based off of the function utf8_from_unicode, which is part of the
     * PHP UTF-8 library. See utils/unicode.php for the function.
     * PHP UTF-8 is released under the LGPL library.
     */
    public static function fromUnicode($arr) {
        ob_start();
        
        foreach (array_keys($arr) as $k) {
            
            # ASCII range (including control chars)
            if ( ($arr[$k] >= 0) && ($arr[$k] <= 0x007f) ) {
                
                echo chr($arr[$k]);
            
            # 2 byte sequence
            } else if ($arr[$k] <= 0x07ff) {
                
                echo chr(0xc0 | ($arr[$k] >> 6));
                echo chr(0x80 | ($arr[$k] & 0x003f));
            
            # Byte order mark (skip)
            } else if($arr[$k] == 0xFEFF) {
                
                // nop -- zap the BOM
            
            # Test for illegal surrogates
            } else if ($arr[$k] >= 0xD800 && $arr[$k] <= 0xDFFF) {
                
                // found a surrogate
                throw new Munch_Utf8_Exception(
                    'fromUnicode: Illegal surrogate '.
                        'at index: '.$k.', value: '.$arr[$k]);
            
            # 3 byte sequence
            } else if ($arr[$k] <= 0xffff) {
                
                echo chr(0xe0 | ($arr[$k] >> 12));
                echo chr(0x80 | (($arr[$k] >> 6) & 0x003f));
                echo chr(0x80 | ($arr[$k] & 0x003f));
            
            # 4 byte sequence
            } else if ($arr[$k] <= 0x10ffff) {
                
                echo chr(0xf0 | ($arr[$k] >> 18));
                echo chr(0x80 | (($arr[$k] >> 12) & 0x3f));
                echo chr(0x80 | (($arr[$k] >> 6) & 0x3f));
                echo chr(0x80 | ($arr[$k] & 0x3f));
                
            } else {
                throw new Munch_Utf8_Exception(  
                    'fromUnicode: Codepoint out of Unicode range '.
                    'at index: '.$k.', value: '.$arr[$k]);
            }
        }
        
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}
// vim: sw=4:ts=4:sts=4:et
