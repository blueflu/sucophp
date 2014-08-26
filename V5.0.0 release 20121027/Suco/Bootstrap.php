<?php
/**
 * Suco_Bootstrap 框架引导类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @package		Bootstrap
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Bootstrap
{
	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct()
	{
		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (substr($method, 0, 4) == 'init') {
				$this->$method();
			}
		}
	}
}