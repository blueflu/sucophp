<?php
/**
 * Suco_Loader 装载器
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Loader
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

require_once 'Suco/Loader/Class.php';

class Suco_Loader extends Suco_Loader_Class
{
	/**
	 * 是否开启自动装载
	 *
	 * @param bool $enabled
	 * @return void
	 */
	public static function setAutoload($enabled = true)
	{
		require_once 'Suco/Loader/Autoload.php';
        if ($enabled === true) {
            Suco_Loader_Autoload::enable();
        } else {
            Suco_Loader_Autoload::disable();
        }
	}
}
?>