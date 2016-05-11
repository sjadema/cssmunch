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
 * @version    SVN: $Id: Import.php 38 2008-05-19 00:14:18Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_AstNode
 */
require_once 'Munch/AstNode.php';

/**
 * Represents an import node.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Import.php 38 2008-05-19 00:14:18Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_AstNode_Import extends Munch_AstNode
{
    /**
     * The uri of the stylesheet to import (string or uri token).
     *
     * @var string
     */
    public $uri = '';

    /**
     * An array of the mediums that the import rule should apply to. The array
     * contains objects of class Munch_AstNode_Medium extends Munch_AstNode.
     *
     * @var array
     */
    public $mediums = array();

    /**
     * Constructs a Munch_AstNode_Import instance.
     *
     * @param string $uri
     * @param array $mediums
     * @return void
     */
    public function __construct($uri, array $mediums)
    {
        $this->uri     = $uri;
        $this->mediums = $mediums;
    }
}
// vim: sw=4:ts=4:sts=4:et
