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
 * @version    SVN: $Id: ElementName.php 41 2008-05-24 20:46:15Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_AstNode
 */
require_once 'Munch/AstNode.php';

/**
 * Represents an element name node.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: ElementName.php 41 2008-05-24 20:46:15Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_AstNode_ElementName extends Munch_AstNode
{
    /**
     * Element name types
     */
    const UNIVERSAL = '*';
    const ELEMENT   = 'element';

    /**
     * The element name type (universal or element).
     *
     * @var string
     */
    public $type;

    /**
     * The element name. Will be null if the node represents a universal 
     * selector.
     *
     * @var string|null
     */
    public $name;

    /**
     * Constructs a Munch_AstNode_ElementName instance.
     *
     * @param string $type
     * @param string|null $name
     * @return void
     */
    public function __construct($type, $name = null)
    {
        $this->type = $type;
        $this->name = $name;
    }
}
// vim: sw=4:ts=4:sts=4:et
