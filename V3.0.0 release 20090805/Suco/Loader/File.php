<?php 

class Suco_Loader_File
{
	protected static $_includePaths = array();
	protected static $_loaded = array();
	
	public function addIncludePath($path)
	{
		self::$_includePaths[] = realpath($path);
	}
	
	public function setIncludePaths($paths)
	{
		foreach ($paths as $path) {
			self::addIncludePath($path);
		}
	}
	
	public function getIncludePaths()
	{
		return array_merge(self::$_includePaths, explode(PATH_SEPARATOR, get_include_path()));
	}
	
	public function getLoaded()
	{
		return array_keys(self::$_loaded);
	}
	
	public function exists($file)
	{
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
	 * @param $file
	 * @param $once
	 * @param $throw
	 * @return unknown_type
	 */
	public function import($file, $once = true, $throw = true)
	{
		self::loadFile($file, $once, $throw);
	}
	
	public function loadFile($file, $once = true, $throw = true)
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
	
	public function includeFile($file, $once = false)
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