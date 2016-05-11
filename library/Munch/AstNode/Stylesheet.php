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
 * @version    SVN: $Id: Stylesheet.php 34 2008-05-18 01:08:11Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

/**
 * @see Munch_AstNode
 */
require_once 'Munch/AstNode.php';

/**
 * Represents a stylesheet node, the starting production of the CSS grammar.
 *
 * @category   Security
 * @package    CSSMunch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: Stylesheet.php 34 2008-05-18 01:08:11Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */
class Munch_AstNode_Stylesheet extends Munch_AstNode
{
    /**
     * The character set of the stylesheet. The value is null if no character
     * character set was specified.
     *
     * @var string|null
     */
    public $charset = null;

    /**
     * An array of all the import rules at the start of the stylesheet.
     * Elements of the array are of type Munch_AstNode_Import.
     *
     * @var array
     */
    public $imports = array();

    /**
     * An array of all the ruleset, media, and page elements in the root of the
     * stylesheet.
     *
     * @var array
     */
    public $rulesets = array();

    /**
     * Constructs a Munch_AstNode_Stylesheet.
     *
     * @param string|null $charset
     * @param array $imports
     * @param array $rulesets
     */
    public function __construct($charset, array $imports, array $rulesets)
    {
        $this->charset  = $charset;
        $this->imports  = $imports;
        $this->rulesets = $rulesets;
    }
}
// vim: sw=4:ts=4:sts=4:et
