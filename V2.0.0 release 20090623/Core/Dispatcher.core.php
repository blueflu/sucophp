<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * 调度器 
 * 主调文件接收到URL参数后通过此类调度对应的控制器与动作
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */

class SCN_Dispatcher
{
	public $controllerPath		= NULL;
	public $controllerName		= NULL;
	public $controllerPrefix	= NULL;
	public $controllerSuffix	= NULL;
	
	public $actionName			= NULL;
	public $actionPrefix		= 'do';
	public $actionSuffix		= NULL;
	
	public function run()
	{
		global $kernel;
		
		$class = $this->controllerPrefix . ucfirst( $this->controllerName ) . $this->controllerSuffix;
		$action = $this->actionPrefix . ucfirst( $this->actionName ) . $this->actionSuffix;
		$file = $this->controllerPath . $class . PHP_EXT;

		!file_exists($file) && die('ERROR: Not found file <strong>' . $file . '</strong>');
		require_once $this->controllerPath . $class . PHP_EXT;
		
		SCN_Output::_start();
		
		!class_exists($class) && die('ERROR: Not found controller <strong>' . $class . '</strong>');
		$kernel = new $class;
		!method_exists($kernel, $action) && die('ERROR: Not found action <strong>' . $action . '</strong>');
		$kernel->$action();
		
		SCN_Output::_end();
		$kernel->output();
	}
}

?>