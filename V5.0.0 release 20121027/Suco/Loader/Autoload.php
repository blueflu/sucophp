<?php
/**
 * Suco_Loader_Autoload 自动装载器
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Loader
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Loader_Autoload
{
	/**
	 * 启用
	 *
	 * @return void
	 */
	public static function enable()
	{
		spl_autoload_register(array(__CLASS__, '_autoload'));
	}

	/**
	 * 禁用
	 *
	 * @return void
	 */
	public static function disable()
	{
		spl_autoload_unregister(array(__CLASS__, '_autoload'));
	}

	/**
	 * 装载
	 *
	 * @param string $class
	 * @return bool
	 */
    protected static function _autoload($class)
    {
        try {
        	require_once 'Suco/Loader/Class.php';
            Suco_Loader_Class::loadClass($class);
            return true;
        } catch (Suco_Loader_Exception $e) {
            return false;
        }
    }
}