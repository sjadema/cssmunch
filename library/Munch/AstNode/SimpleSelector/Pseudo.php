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
 * @version    SVN: $Id: Pseudo.php 50 2008-05-31 23:40:10Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_AstNode_SimpleSelector_Abstract
 */
require_once 'Munch/AstNode/SimpleSelector/Abstract.php';

/**
 * Represents a pseudo selector node.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Pseudo.php 50 2008-05-31 23:40:10Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_AstNode_SimpleSelector_Pseudo extends Munch_AstNode_SimpleSelector_Abstract
{
    /**
     * The pseudo class/element.
     *
     * @var string
     */
    public $pseudo;

    /**
     * The value of the pseudo selector. If the value is null, then just a
     * pseudo class/element was specified, ie it is not a "function". If it is
     * an empty string, then the pseudo class/element was specified as a 
     * function with no parameter.
     *
     * @var string|null
     */
    public $value;

    /**
     * Constructs a Munch_AstNode_SimpleSelector_Id instance.
     *
     * @param string $value
     * @return void
     */
    public function __construct($pseudo, $value = null)
    {
        $this->pseudo = $pseudo;
        $this->value  = $value;
    }
}
// vim: sw=4:ts=4:sts=4:et
