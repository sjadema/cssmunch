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
 * @version    SVN: $Id: Token.php 36 2008-05-18 14:27:02Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_Token_Exception
 */
require_once 'Munch/Token/Exception.php';

/**
 * Holds type and (when relevant) the value of tokens.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Token.php 36 2008-05-18 14:27:02Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_Token
{
    const INVALID       = 'invalid';
    const COLON         = ':';
    const SEMI          = ';';
    const LEFT_BRACE    = '{';
    const RIGHT_BRACE   = '}';
    const LEFT_BRACKET  = '[';
    const RIGHT_BRACKET = ']';
    const LEFT_PAREN    = '(';
    const RIGHT_PAREN   = ')';
    const PLUS          = '+';
    const MINUS         = '-';
    const SLASH         = '/';
    const STAR          = '*';
    const GREATER       = '>';
    const COMMA         = ',';
    const DOT           = '.';
    const WHITE_SPACE   = ' ';
    const CDO           = '<!--';
    const CDC           = '-->';
    const INCLUDES      = '~=';
    const DASH_MATCH    = '|=';
    const EQUALS        = '=';
    const STR           = 'str';
    const URI           = 'uri';
    const IDENT         = 'ident';
    const FUNC          = 'func';
    const IMPORT_SYM    = '@import';
    const PAGE_SYM      = '@page';
    const MEDIA_SYM     = '@media';
    const CHARSET_SYM   = '@charset';
    const UNKNOWN_AT    = '@unknown';
    const HASH          = 'hash';
    const IMPORTANT_SYM = '!important';
    const ANGLE         = 'angle';
    const EMS           = 'ems';
    const EXS           = 'exs';
    const FREQ          = 'freq';
    const LENGTH        = 'length';
    const NUMBER        = 'number';
    const TME           = 'time';
    const DIMENSION     = 'dimension';
    const PERCENTAGE    = 'percentage';

    protected $_type;

    protected $_value;

    public function __construct($tokenType, $value = '')
    {
        $this->_type  = $tokenType;
        $this->_value = $value;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function __toString()
    {
        return $this->_type;
    }
}
// vim: sw=4:ts=4:sts=4:et
