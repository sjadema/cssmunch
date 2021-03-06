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
 * @version    SVN: $Id: UnitValue.php 41 2008-05-24 20:46:15Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_AstNode_Term_Value
 */
require_once 'Munch/AstNode/Term/Value.php';

/**
 * Base class of all AstNode term subclasses that have a unit in addition to a
 * value.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: UnitValue.php 41 2008-05-24 20:46:15Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
abstract class Munch_AstNode_Term_UnitValue extends Munch_AstNode_Term_Value
{
    /**
     * The unit value of the term.
     *
     * @var string
     */
    public $unit;

    /**
     *
     * @param $value string The value of this term.
     * @param $unit string The unit of this term.
     * @param $unaryOperator Munch_AstNode_UnaryOperator|null The term's unary operator, or null. 
     * @return void
     */
    public function __construct($value, $unit, $unaryOperator = null)
    {
        parent::__construct($value, $unaryOperator);

        $this->unit = $unit;
    }
}
// vim: sw=4:ts=4:sts=4:et
