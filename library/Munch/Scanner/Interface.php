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
 * @version    SVN: $Id: Interface.php 28 2008-05-10 15:39:19Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * Interface used to define a scanner usable by a Munch_Parser instance.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Interface.php 28 2008-05-10 15:39:19Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
interface Munch_Scanner_Interface
{
    /**
     * Sets the string of CSS code to be tokenized. 
     *
     * @param  string $cssCode
     * @return void
     */
    public function setInput($cssCode);

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
    public function getNextToken();
}
// vim: sw=4:ts=4:sts=4:et
