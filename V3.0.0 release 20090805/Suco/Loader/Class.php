<?php 

require_once 'Suco/Loader/File.php';

class Suco_Loader_Class extends Suco_Loader_File
{
	protected static $_namespaces = array();
	protected static $_loaded = array();
	protected static $_suffix = '.php';
	
	public function registerNamespace($namespace, $path = null)
	{
		self::$_namespaces[$namespace] = $path;
	}
	
	public function unregisterNamespace($namespace)
	{
		unset(self::$_namespaces[$namespace]);
	}
	
	public function getNamespace($classname)
	{
		foreach (self::$_namespaces as $namespace => $path) {
			if ($namespace == substr($classname, 0, strlen($namespace))) {
				return $namespace;
			}
		}
	}
	
	public function exists($classname)
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
	
	public function loadClass($classname)
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
	
	public function getLoaded()
	{
		return array_keys(self::$_loaded);
	}
	
	public function setSuffix($suffix)
	{
		self::$_suffix = $suffix;
	}
	
	public function getSuffix()
	{
		return self::$_suffix;
	}
}
?>