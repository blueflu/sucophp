<?php

/**
 * Suco_Config 配置工厂类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Config
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

require_once 'Suco/Config/Abstract.php';

class Suco_Config
{
	/**
	 * 工厂方法
	 * 根据文件后缀载入相应的适配器
	 *
	 * @param string $file 配置文件路径
	 *
	 * @return mixed
	 */
	public function factory($file)
	{
		static $loaded = array();

		if (!isset($loaded[$file])) {
			$path = pathinfo($file);
			$adapter = ucfirst($path['extension']);
			$class = "Suco_Config_{$adapter}";

			require_once "Suco/Config/{$adapter}.php";
			$cf = new $class();
			$loaded[$file] = $cf->load($file);
		}
		return $loaded[$file];
	}
}