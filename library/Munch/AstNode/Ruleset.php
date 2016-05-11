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
 * @version    SVN: $Id: Ruleset.php 42 2008-05-25 02:29:22Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_AstNode
 */
require_once 'Munch/AstNode.php';

/**
 * Represents a ruleset node.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Ruleset.php 42 2008-05-25 02:29:22Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_AstNode_Ruleset extends Munch_AstNode
{
    /**
     * An array of Munch_AstNode_Selector instances.
     *
     * @var array
     */
    public $selectors = array();

    /**
     * An array of Munch_AstNode_Declaration instances.
     *
     * @var array
     */
    public $declarations = array();

    /**
     * Constructs a Munch_AstNode_SimpleSelector instance.
     *
     * @param array $selectors
     * @param array $declarations
     */
    public function __construct(array $selectors, array $declarations)
    {
        $this->selectors    = $selectors;
        $this->declarations = $declarations;
    }
}
// vim: sw=4:ts=4:sts=4:et
