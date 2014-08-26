<?php

require_once 'Suco/Dispatcher/Abstract.php';

class Suco_Dispatcher_Standard extends Suco_Dispatcher_Abstract
{
	protected $_request;
	protected $_response;
	
	protected static $_moduleDirectory = null;
	protected static $_controllerDirectorys = array();
	
	public function __construct(Suco_Request_Interface $request, Suco_Response_Interface $response)
	{
		$this->_request = $request;
		$this->_response = $response;
	}
	
	public function setOptions($options)
	{
		foreach ($options as $key => $option) {
			$method = 'set' . ucfirst($key);
			$this->$method($option);
		}
	}
	
	public function addControllerDirectory($directory, $namespace = null)
	{
		$namespace = $namespace != null ? $namespace : $this->getDefaultModule();
		self::$_controllerDirectorys[$namespace] = $directory;
	}
	
	public function getControllerDirectory($namespace = null)
	{
		$namespace = $namespace ? $namespace : $this->getModule();
		return self::$_moduleDirectory . self::$_controllerDirectorys[$namespace];
	}
	
	public function setModuleDirectory($directory)
	{
		self::$_moduleDirectory = $directory;
	}
	
	public function getModuleDirectory()
	{
		return self::$_moduleDirectory;
	}
	
	public function setControllerDirectorys($directorys)
	{
		self::$_controllerDirectorys = $directorys;
	}
	
	public function getControllerDirectorys()
	{
		return self::$_controllerDirectorys;
	}
	
	public function isModule($name)
	{
		return isset(self::$_controllerDirectorys[$name]);
	}
	
	public function dispatch()
	{
		$this->setModule($this->_request->getModuleName());
		$this->setController($this->_request->getControllerName());
		$this->setAction($this->_request->getActionName());
		
		$classname = $this->_formatControllerName($this->getController());
		$this->_loadControllerFile($this->getModule(), $this->getController());
		if (!class_exists($classname)) {
			require_once 'Suco/Dispatcher/Exception.php';
			throw new Suco_Dispatcher_Exception("找不到控制器 {$classname}");
		}
		
		$controller = new $classname();
		if (!$controller instanceof Suco_Controller_Abstract) {
			require_once 'Suco/Dispatcher/Exception.php';
			throw new Suco_Dispatcher_Exception("控制器必须继承 Suco_Controller_Abstract");
		}
		
		$controller->run($this->getAction(), $this->_request, $this->_response);
	}
	
	protected function _loadControllerFile($moduleName, $controllerName)
	{
		if (!$this->isModule($moduleName)) {
			require_once 'Suco/Dispatcher/Exception.php';
			throw new Suco_Dispatcher_Exception("系统未指定模块 {$moduleName}");
		}
		
		$path = trim(self::$_moduleDirectory, '/') . '/'
			  . trim(self::$_controllerDirectorys[$moduleName], '/') . '/';
			  
		if (!is_dir($path)) {
			require_once 'Suco/Dispatcher/Exception.php';
			throw new Suco_Dispatcher_Exception("找不到模块目录 {$path}");
		}
		
		$file = ucfirst($controllerName) . 'Controller.php';
		if (!file_exists($path . $file)) {
			require_once 'Suco/Dispatcher/Exception.php';
			throw new Suco_Dispatcher_Exception("找不到控制器文件 {$path}{$file}");
		}
		
		require_once $path . $file;
	}
	
	protected function _formatControllerName($controller)
	{
		$namespace = null;
		if ($this->getModule() != $this->getDefaultModule()) {
			$namespace = ucfirst($this->getModule()) . '_';
		}
		return $namespace . ucfirst($controller) . 'Controller';
	}
}