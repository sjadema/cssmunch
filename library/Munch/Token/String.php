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
 * @version    SVN: $Id: String.php 34 2008-05-18 01:08:11Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_Token
 */
require_once 'Munch/Token.php';

/**
 * Represents a string token.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: String.php 34 2008-05-18 01:08:11Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_Token_String extends Munch_Token
{
    protected $_enclosure;

    public function __construct($value, $enclosure)
    {
        parent::__construct(Munch_Token::STR, $value);
        $this->_enclosure = $enclosure;
    }

    public function __toString()
    {
        return $this->_enclosure . $this->_value . $this->_enclosure;
    }

    /**
     * Returns the enclosure character for the string (ie ' or ").
     * 
     * @return string
     */
    public function getEnclosure()
    {
        return $this->_enclosure;
    }
}
// vim: sw=4:ts=4:sts=4:et
