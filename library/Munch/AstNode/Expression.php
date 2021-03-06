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
 * @version    SVN: $Id: Expression.php 44 2008-05-26 02:27:10Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_AstNode
 */
require_once 'Munch/AstNode.php';

/**
 * Represents an expression node.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Expression.php 44 2008-05-26 02:27:10Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_AstNode_Expression extends Munch_AstNode
{
    /**
     * An array of instances of Munch_AstNode_Term.
     *
     * @var Munch_AstNode_Term
     */
    public $terms = array();

    /**
     * An array of instances of Munch_AstNode_Operator. The number of objects in
     * $operators will be one less than the number in $terms. So, for example,
     * the operator between the first and second terms in $terms is at index
     * 0.
     * 
     * @var array
     */
    public $operators = array();

    /**
     * Constructs a Munch_AstNode_Expression.
     *
     * @param array $operators
     * @param array $terms
     */
    public function __construct(array $terms, array $operators)
    {
        $this->terms     = $terms;
        $this->operators = $operators;
    }
}
// vim: sw=4:ts=4:sts=4:et
