<?php
/**
 * Suco_Helper 辅助类工厂
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper
{
	/**
	 * 类命名空间
	 * @var string
	 */
	protected static $_helperNamespace = 'Helper_';

	/**
	 * 辅助类目录
	 * @var string
	 */
	protected static $_helperPath;

	/**
	 * 工厂方法
	 *
	 * @param string $name
	 * @return string
	 */
	public static function factory($name)
	{
		$classname = 'Suco_Helper_' . ucfirst($name);
		if (self::getHelperPath()) {
			$file = self::getHelperPath() . ucfirst($name) . '.php';
			if (file_exists($file)) {
				require_once $file;
				if (self::getHelperNamespace()) {
					$classname = self::getHelperNamespace() . ucfirst($name);
				}
				return $classname;
			}
		}

		try {
			require_once 'Suco/Loader/Class.php';
			Suco_Loader_Class::loadClass($classname);
			return $classname;
		} catch (Suco_Loader_Exception $e) {
			throw new Suco_Loader_Exception("加载辅助类{$classname}失败!");
		}
	}

	/**
	 * 设置辅助类目录
	 *
	 * @param string $path
	 * @return void
	 */
	public static function setHelperPath($path)
	{
		self::$_helperPath = $path;
	}

	/**
	 * 返回辅助类目录
	 *
	 * @return string
	 */
	public static function getHelperPath()
	{
		return self::$_helperPath;
	}

	/**
	 * 设置辅助类命名空间
	 *
	 * @param string $namespace
	 * @return void
	 */
	public static function setHelperNamespace($namespace)
	{
		self::$_helperNamespace = $namespace;
	}

	/**
	 * 返回辅助类命名空间
	 *
	 * @return string
	 */
	public static function getHelperNamespace()
	{
		return self::$_helperNamespace;
	}
}