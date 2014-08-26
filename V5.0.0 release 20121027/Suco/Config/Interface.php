<?php

/**
 * Suco_Config_Interface 接口
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

interface Suco_Config_Interface
{
	/**
	 * 保存配置文件
	 *
	 * @param string $file 文件路径
	 * @return void
	 */
	public function save($file = null);

	/**
	 * 载入配置文件
	 *
	 * @param string $file 文件路径
	 * @return mixed
	 */
	public function load($file);
}