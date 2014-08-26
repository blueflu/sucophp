<?php
/**
 * Suco_Cache 缓存工厂
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @package		Cache
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Cache
{
	/**
	 * 通过工厂单件实例化相关的缓存类
	 * 如:<code>
	 * $c1 = Suco_Cache::factory('Memcache');
	 * $c1 = Suco_Cache::factory('File');
	 * </code>
	 * @return object
	 */
	public static function factory($adapter = 'file')
	{
		$class = 'Suco_Cache_'.ucfirst($adapter);
		return call_user_func (array($class, 'instance'));
	}
}