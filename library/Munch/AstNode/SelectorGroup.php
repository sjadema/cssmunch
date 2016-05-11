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
 * @version    SVN: $Id: SelectorGroup.php 48 2008-05-31 01:04:01Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_AstNode
 */
require_once 'Munch/AstNode.php';

/**
 * Represents a selector group node.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: SelectorGroup.php 48 2008-05-31 01:04:01Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_AstNode_SelectorGroup extends Munch_AstNode
{
    /**
     * The element name of the simple selector. Can be null if no element name
     * was specified.
     *
     * @var Munch_AstNode_ElementName|null
     */
    public $elementName = null;

    /**
     * An array of Munch_SimpleSelector_Abstract objects.
     *
     * @var array
     */
    public $simpleSelectors = array();

    /**
     * Constructs a Munch_AstNode_SelectorGroup instance.
     *
     * @param Munch_AstNode_ElementName|null $elementName
     * @param array $simpleSelectors
     * @return void
     */
    public function __construct($elementName, array $simpleSelectors)
    {
        $this->elementName     = $elementName;
        $this->simpleSelectors = $simpleSelectors;
    }
}
// vim: sw=4:ts=4:sts=4:et
