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
 * @package    CSS Munch
 * @author     Christopher Utz <cutz@chrisutz.com>
 * @copyright  2008 Christopher Utz <cutz@chrisutz.com>
 * @license    http://www.opensource.org/licenses/lgpl-3.0.html LGPLv3
 * @version    SVN: $Id: bootstrap.php 28 2008-05-10 15:39:19Z baron314159@yahoo.com $
 * @link       http://cssmunch.googlecode.com
 */

require_once __DIR__ . '/../vendor/autoload.php';

$rootDir = dirname(dirname(__FILE__));

$incDirs = array(
    $rootDir.DIRECTORY_SEPARATOR.'library',
    $rootDir.DIRECTORY_SEPARATOR.'tests',
    $rootDir.DIRECTORY_SEPARATOR.'vendor'
);

set_include_path(implode(PATH_SEPARATOR, $incDirs));

// vim: sw=4:ts=4:sts=4:et
