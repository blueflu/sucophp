<?php
/**
 * Suco_Model 模型工厂
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Model
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Model
{
	/**
	 * 模型目录
	 * @var array
	 */
	public static $modelDirectorys = array();

	/**
	 * 工厂方法创建模型
	 *
	 * @param string $model
	 * @return object
	 */
	public static function factory($model)
	{
		static $instance = array();
		if (!isset($instance[$model])) {
			$className = self::_loadModelFile($model);
			if (!class_exists($model)) {
				throw new Suco_Exception("找不到模型 {$model}");
			}
			$instance[$model] = new $className();
		}
		return $instance[$model];
	}

	/**
	 * 追加模型目录
	 *
	 * @param string $path 模型目录
	 * @param string $namespace 命名空间
	 * @return void
	 */
	public static function appendModelDirectory($path, $namespace = null)
	{
		self::$modelDirectorys[] = array('path'=>$path, 'namespace'=>$namespace);
		Suco_Loader_File::addIncludePath($path);
	}

	/**
	 * 设置模型目录
	 *
	 * @param array $dirs
	 * @return void
	 */
	public static function setModelDirectorys($dirs)
	{
		self::$modelDirectorys = $dir;
	}

	/**
	 * 返回模型目录
	 *
	 * @return array
	 */
	public static function getModelDirectorys()
	{
		return self::$modelDirectorys;
	}

	/**
	 * 载入模型文件
	 *
	 * @param string $modelName
	 * @return string
	 */
	protected static function _loadModelFile($modelName)
	{
		$dirs = self::getModelDirectorys();
		$className = $modelName;
		$isLoaded = 0;
		foreach ($dirs as $dir) {
			$fileName = str_replace('_', DIRECTORY_SEPARATOR, $dir['namespace'].$modelName) . '.php';
			$path = $dir['path'];
			$sPath[] = $path . $fileName;
			if (file_exists($path . $fileName)) {
				$isLoaded = 1;
				require_once $path . $fileName;
				$className = $dir['namespace'].$modelName;
				break;
			}
		}

		if (!$isLoaded) {
			throw new App_Exception('找不到模型文件 '.implode("\r\n <br>", $sPath).'');
		}

		return $className;
	}
}