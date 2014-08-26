<?php

/**
 * Suco_Cache_Interface, 缓存类接口
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Cache
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

interface Suco_Cache_Interface
{
	public static function load($id);
	public static function save($data, $exp = 0, $id = null);
}