<?php
/**
 * Suco_Loader_File 文件装载器
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Loader
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Loader_File
{
	/**
	 * 包含目录
	 * @var array
	 */
	protected static $_includePaths = array();

	/**
	 * 已载入的文件
	 * @var array
	 */
	protected static $_loaded = array();

	/**
	 * 添加包含目录
	 *
	 * @param string $path
	 * @return void
	 */
	public static function addIncludePath($path)
	{
		self::$_includePaths[] = realpath($path);
	}

	/**
	 * 设置包含目录集
	 *
	 * @param string $paths
	 * @return void
	 */
	public static function setIncludePaths($paths)
	{
		foreach ($paths as $path) {
			self::addIncludePath($path);
		}
	}

	/**
	 * 返回包含目录集
	 *
	 * @return array
	 */
	public static function getIncludePaths()
	{
		return array_merge(self::$_includePaths, explode(PATH_SEPARATOR, get_include_path()));
	}

	/**
	 * 返回已载入的文件
	 *
	 * @return array
	 */
	public static function getLoaded()
	{
		return array_keys(self::$_loaded);
	}

	/**
	 * 检查文件是否存在
	 *
	 * @return array
	 */
	public static function exists($file)
	{
		if (file_exists($file)) return true;

		$paths = self::getIncludePaths();
		foreach ($paths as $path) {
			if (file_exists($path . DIRECTORY_SEPARATOR . $file)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * loadFile 的别名
	 */
	public static function import($file, $once = true, $throw = true)
	{
		self::loadFile($file, $once, $throw);
	}

	/**
	 * 装载文件
	 *
	 * @param string $file
	 * @param bool $once
	 * @param bool $throw
	 * @return void
	 */
	public static function loadFile($file, $once = true, $throw = true)
	{
		//先从当前目录查找
		if (file_exists($file)) {
			self::includeFile($file, $once);
			return true;
		}

		$paths = self::getIncludePaths();
		foreach ($paths as $path) {
			if (file_exists($path . DIRECTORY_SEPARATOR . $file)) {
				self::includeFile($path . DIRECTORY_SEPARATOR . $file, $once);
				return true;
			}
		}

		if ($throw == true) {
			require_once 'Suco/Loader/Exception.php';
			throw new Suco_Loader_Exception("加载文件失败  {$file} [".implode(PATH_SEPARATOR, $paths)."]");
		}
		return false;
	}

	/**
	 * 引入文件
	 *
	 * @param string $file
	 * @param bool $once
	 * @return void
	 */
	public static function includeFile($file, $once = false)
	{
		if ($once == true) {
			if (!isset($file, self::$_loaded[$file])) {
				include_once $file;
			}
		} else {
			include $file;
		}
		self::$_loaded[$file] = true;
	}
}
?>