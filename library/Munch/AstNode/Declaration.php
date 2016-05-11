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
 * @version    SVN: $Id: Declaration.php 42 2008-05-25 02:29:22Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_AstNode
 */
require_once 'Munch/AstNode.php';

/**
 * Represents a declaration node.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Declaration.php 42 2008-05-25 02:29:22Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_AstNode_Declaration extends Munch_AstNode
{
    /**
     *
     * @var Munch_AstNode_Property
     */
    public $property = null;

    /**
     * 
     * @var Munch_AstNode_Expression
     */
    public $expression = null;

    /**
     * The priority of the declaration.  It is null if a priority token was not
     * specified.
     *
     * @var Munch_AstNode_Priority|null
     */
    public $priority = null;

    /**
     * Constructs a Munch_AstNode_Declaration instance.
     *
     * @param Munch_AstNode_Property $property
     * @param Munch_AstNode_Expression $expression
     * @param Munch_AstNode_Priority|null $priority
     * @return void
     */
    public function __construct(Munch_AstNode_Property $property, Munch_AstNode_Expression $expression, $priority)
    {
        $this->property   = $property;
        $this->expression = $expression;
        $this->priority   = $priority;
    }
}
// vim: sw=4:ts=4:sts=4:et
