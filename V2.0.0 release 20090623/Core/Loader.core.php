<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * 装载器
 * 本框架中的所有类与文件装载匀通过此类
 * 使用装载器,可避免文件与类的重复载入
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */

class SCN_Loader
{

	/*
	 * 单件模式载入文件 类似于 require及require_once 
	 * @params string $fileName
	 * @params string $path
	 * @params bool $once
	 * @return mixed
	 */
	public function import($fileName, $path = null, $once = false)
	{
		static $loaded = array();
		$fileName = $path . $fileName . PHP_EXT;
		$fileNameLower = strtolower($fileName);
		if (!isset($loaded[$fileName]) && !isset($loaded[$fileNameLower])) {
			!file_exists($fileName) && die('ERROR: Not found file <strong>' . $fileName . '</strong>');
			$once ? require_once $fileName : require $fileName;
			$loaded[$fileName] = true;
		}
		
		return $fileName;
	}
	
	/*
	 * 单件模式载入类
	 * @params string $className
	 * @params mixed $params
	 * @return object
	 */
	private function instance($className, $params = null)
	{
		static $instance = array();
		if (!isset($instance[$className])) {
			if (!class_exists($className)) die('ERROR: Instance <strong>'.$className.'</strong> fail!');
			
			if (is_array($params)) {
				$params = implode(',', $params);
				eval('$instance[$className] = new ' . $className.'('.$params.');');
			} else {
				$instance[$className] = $params ? new $className($params) : new $className;
			}
		}

		return $instance[$className];
	}
	
	/*
	 * 载入框架内核
	 * @params string $className
	 * @params mixed $params
	 */
	public function core($className, $params = null)
	{
		SCN_Loader::import(ucfirst($className) . CORE_EXT, CORE_DIR);
		
		$kernel = &SCN_Base::getKernel();
		$kernel->$className = &SCN_Loader::instance(CLS_PREFIX . ucfirst($className), $params);

		SCN_Loader::assignModel();
		return $kernel->$className;
	}
	
	/*
	 * 载入类库
	 * @params string $className
	 * @params mixed $params
	 */
	public function library($className, $params = null)
	{
		SCN_Loader::import(ucfirst($className) . LIBS_EXT, LIBS_DIR);
		
		$kernel = &SCN_Base::getKernel();
		$kernel->$className = &SCN_Loader::instance(CLS_PREFIX . ucfirst($className), $params);

		SCN_Loader::assignModel();
		return $kernel->$className;
	}

	/*
	 * 将装载的类装入模型
	 * @params string $className
	 * @params object $obj
	 */
	private function &assignModel()
	{
		if (!$this->models) { return; }
		
		$kernel = &SCN_Base::getKernel();
		if (is_object($kernel)) {
			foreach ($this->models as $model) {
				$kernel->$model->initModel();
			}
		} else {
			foreach ($this->models as $model) {
				$this->$model->initModel();
			}
		}
	}

	/*
	 * 载入模型
	 * @params string $className
	 * @params mixed $params
	 */
	public function model($className, $params = false)
	{
		
		SCN_Loader::import('Model.core', CORE_DIR);
		SCN_Loader::import(ucfirst($className) . strtolower(MODE_EXT), APP_PATH . MODS_DIR);

		$kernel = &SCN_Base::getKernel();

		$kernel->$className = &SCN_Loader::instance(ucfirst($className) . 'Model', $params);
		$kernel->$className->initModel();

		$this->models[] = &$className;
		return $kernel->$className;
	}

	/*
	 * 载入辅助函数
	 * @params string $className
	 * @params mixed $params
	 */
	public function helper($func)
	{
		SCN_Loader::import($func . FUNC_EXT, FUNC_DIR);
	}

	/*
	 * 装载模板引擎
	 * @params string $className
	 * @params object $obj
	 */
	
	protected function template($path = VIEW_DIR)
	{
		SCN_Loader::import('Template' . CORE_EXT, CORE_DIR);

		$kernel = &SCN_Base::getKernel();
		$kernel->tpl = &SCN_Loader::instance('SCN_Template');
		$kernel->tpl->tplPath = $path . CUR_STYLE . DS;
		$kernel->tpl->comPath = COMP_DIR . str_replace(DS, '', CTRL_DIR) . '_' . CUR_STYLE . DS ;
	}

	/*
	 * 装载数据库
	 * @params string $className
	 * @params object $obj
	 */
	
	protected function database($mode = 'DEVELOPER')
	{
		static $dbo = array();
		if (!isset($dbo[$mode])) {
			SCN_Configure::load('db.conf', CFG_DIR);
			$db = SCN_Configure::get('DB');
			SCN_Loader::import('Factory', DATABASE_DIR);
			
			$dbo[$mode] = &SCN_Db_Factory::connect($db[$mode]);
			$dbo[$mode]->setCharset($db[$mode]['charset']);
			$dbo[$mode]->setTbPrefix($db[$mode]['tbprefix']);
		}
		
		$kernel = &SCN_Base::getKernel();
		$kernel->db = &$dbo[$mode];
		SCN_Loader::assignModel();
	}

	/*
	 * 装载语言包
	 * @params string $className
	 * @params object $obj
	 */
	
	protected function language($file, $lang)
	{
		$file = LANG_DIR . $lang . DS . $file . LANG_EXT . PHP_EXT;
		require_once $file;

		$kernel = &SCN_Base::getKernel();
		$kernel->lang = array_merge((array)$kernel->lang, (array)$_LANG);

		#SCN_Loader::assignModel();
	}

}
?>