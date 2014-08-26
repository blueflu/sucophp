<?php

require_once 'Suco/Controller/Dispatcher/Abstract.php';

class Suco_Controller_Dispatcher_Standard extends Suco_Controller_Dispatcher_Abstract
{
	protected $_request;
	protected $_response;
	
	protected static $_moduleDirectory = null;
	protected static $_controllerDirectorys = array();
	
	public function __construct(Suco_Controller_Request_Interface $request, Suco_Controller_Response_Interface $response)
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
		
		$dir = realpath($directory) . DIRECTORY_SEPARATOR;
		if (is_dir($dir) && $dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == '.' || $file == '..') { continue; }
				if (filetype($dir . $file) == 'dir') {
					$this->addControllerDirectory($file . '/controllers', $file);
				}
			}
			closedir($dh);
		}
		return $this;
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
	
	public function isController($controller, $module = null)
	{
		$path = self::getModuleDirectory() . @self::$_controllerDirectorys[$module] . '/';
		$file = ucfirst($controller) . 'Controller.php';
		return Suco_Loader_File::exists($path . $file);
	}
	
	public function dispatch($controller = null, $action = null, $module = null, $params = null)
	{
		$this->setModule($module ? $module : $this->_request->getModuleName());
		$this->setController($controller ? $controller : $this->_request->getControllerName());
		$this->setAction($action ? $action : $this->_request->getActionName());	
		
		$classname = $this->_formatControllerName($this->getController());
		$this->_loadControllerFile($this->getModule(), $this->getController());
		if (!class_exists($classname)) {
			require_once 'Suco/Controller/Dispatcher/Exception.php';
			throw new Suco_Controller_Dispatcher_Exception("找不到控制器 {$classname}");
		}
		
		$controller = new $classname();
		if (!$controller instanceof Suco_Controller_Action) {
			require_once 'Suco/Controller/Dispatcher/Exception.php';
			throw new Suco_Controller_Dispatcher_Exception("控制器必须继承 Suco_Controller_Action");
		}
		$controller->setRequest($this->_request);
		$controller->setResponse($this->_response);
		$controller->setParams($params);
		$controller->dispatch($this->getAction());
		return $controller;
	}
	
	protected function _loadControllerFile($moduleName, $controllerName)
	{
		if (!$this->isModule($moduleName)) {
			require_once 'Suco/Controller/Dispatcher/Exception.php';
			throw new Suco_Controller_Dispatcher_Exception("系统未指定模块 {$moduleName}");
		}
		
		$path = (self::$_moduleDirectory ? trim(self::$_moduleDirectory, '/') : '.') . '/'
			  . trim(self::$_controllerDirectorys[$moduleName], '/') . '/';
			  
		if (!is_dir($path)) {
			require_once 'Suco/Controller/Dispatcher/Exception.php';
			throw new Suco_Controller_Dispatcher_Exception("找不到模块目录 {$path}");
		}
		
		$file = ucfirst($controllerName) . 'Controller.php';
		if (!file_exists($path . $file)) {
			require_once 'Suco/Controller/Dispatcher/Exception.php';
			throw new Suco_Controller_Dispatcher_Exception("找不到控制器文件 {$path}{$file}");
		}
		
		require_once $path . $file;
	}
	
	protected function _formatControllerName($controller)
	{
		$namespace = null;
		if ($this->getModule() != $this->getDefaultModule()) {
			//$namespace = ucfirst($this->getModule()) . '_';
		}
		return $namespace . ucfirst($controller) . 'Controller';
	}
}