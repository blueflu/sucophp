<?php
/**
 * Suco_Loader_Class 类装载器
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Loader
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

require_once 'Suco/Loader/File.php';

class Suco_Loader_Class extends Suco_Loader_File
{
	/**
	 * 命名空间
	 * @var string
	 */
	protected static $_namespaces = array();

	/**
	 * 已装载项目
	 * @var array
	 */
	protected static $_loaded = array();

	/**
	 * 类后缀
	 * @var string
	 */
	protected static $_suffix = '.php';

	/**
	 * 注册命名空间
	 *
	 * @param string $namespace
	 * @param string $path
	 * @return void
	 */
	public static function registerNamespace($namespace, $path = null)
	{
		self::$_namespaces[$namespace] = $path;
	}

	/**
	 * 注销命名空间
	 *
	 * @param string $namespace
	 * @return void
	 */
	public static function unregisterNamespace($namespace)
	{
		unset(self::$_namespaces[$namespace]);
	}

	/**
	 * 返回命名空间
	 *
	 * @param string $namespace
	 * @return string
	 */
	public static function getNamespace($classname)
	{
		foreach (self::$_namespaces as $namespace => $path) {
			if ($namespace == substr($classname, 0, strlen($namespace))) {
				return $namespace;
			}
		}
	}

	/**
	 * 检查类是否存在
	 *
	 * @param string $classname
	 * @return bool
	 */
	public static function exists($classname)
	{
		if (isset(self::$_loaded[$classname])) return true;

		$file = $classname;
		if ($namespace = self::getNamespace($classname)) {
			$file = str_replace($namespace, self::$_namespaces[$namespace], $file);
		}
		$file = str_replace('_', DIRECTORY_SEPARATOR, $file) . self::getSuffix();
		if (!parent::exists($file)) return false;

		return true;
	}

	/**
	 * 载入类文件
	 *
	 * @param string $classname
	 * @return void
	 */
	public static function loadClass($classname)
	{
		if (!isset(self::$_loaded[$classname])) {
			$file = $classname;
			if ($namespace = self::getNamespace($classname)) {
				$file = str_replace($namespace, self::$_namespaces[$namespace], $file);
			}

			$file = str_replace('_', DIRECTORY_SEPARATOR, $file) . self::getSuffix();

			try {
				parent::loadFile($file);
			} catch (Suco_Loader_Exception $e) {
				throw new Suco_Loader_Exception("找不到 {$classname} 类文件 {$file}");
			}

			if (!class_exists($classname)) {
				require_once 'Suco/Loader/Exception.php';
				throw new Suco_Loader_Exception("文件 {$file}, 不是{$classname}类文件");
			}
			self::$_loaded[$classname] = true;
		}
	}

	/**
	 * 返回已载入的类文件
	 *
	 * @return array
	 */
	public static function getLoaded()
	{
		return array_keys(self::$_loaded);
	}

	/**
	 * 设置类后缀
	 *
	 * @param string $suffix
	 * @return void
	 */
	public static function setSuffix($suffix)
	{
		self::$_suffix = $suffix;
	}

	/**
	 * 返回类后缀
	 *
	 * @return string
	 */
	public static function getSuffix()
	{
		return self::$_suffix;
	}
}
?>