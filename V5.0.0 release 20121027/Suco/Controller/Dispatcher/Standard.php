<?php
/**
 * Suco_Controller_Dispatcher_Standard 分发器
 * 用于将请求分发到相应的控制器
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

require_once 'Suco/Controller/Dispatcher/Abstract.php';

class Suco_Controller_Dispatcher_Standard extends Suco_Controller_Dispatcher_Abstract
{
  /**
   * 请求对象
   * @var object
   */
  protected $_request;

  /**
   * 响应对应
   * @var object
   */
  protected $_response;

  /**
   * 模块目录
   * @var string
   */
  protected static $_moduleDirectory = null;

  /**
   * 控制器目录集
   * @var array
   */
  protected static $_controllerDirectorys = array();

  /**
   * 构造函数
   *
   * @param object $request 请求对象
   * @param object $response 响应对象
   * @return void
   */
  public function __construct(Suco_Controller_Request_Interface $request, Suco_Controller_Response_Interface $response)
  {
    $this->_request = $request;
    $this->_response = $response;
  }

  /**
   * 解析配置参数
   *
   * @param array $options
   * @return void
   */
  public function setOptions($options)
  {
    foreach ($options as $key => $option) {
      $method = 'set' . ucfirst($key);
      $this->$method($option);
    }
  }

  /**
   * 添加控制器目录
   *
   * @param string $directory 目录
   * @param string $namespace 命名空间
   * @return object
   */
  public function addControllerDirectory($directory, $namespace = null)
  {
    $namespace = $namespace != null ? $namespace : $this->getDefaultModule();
    self::$_controllerDirectorys[$namespace] = $directory;

    return $this;
  }

  /**
   * 根据命名空间返回控制器目录
   *
   * @param string $namespace 命名空间
   * @return string
   */
  public function getControllerDirectory($namespace = null)
  {
    $namespace = $namespace ? $namespace : $this->getModule();
    return self::$_moduleDirectory . self::$_controllerDirectorys[$namespace];
  }

  /**
   * 设置模块目录
   *
   * @param string $directory 目录
   * @return object
   */
  public function setModuleDirectory($directory)
  {
    self::$_moduleDirectory = $directory;
    $dir = realpath($directory) . DIRECTORY_SEPARATOR;
    if (is_dir($dir) && $dh = opendir($dir)) {
      while (($file = readdir($dh)) !== false) {
        if ($file == '.' || $file == '..') { continue; }
        if (filetype($dir . $file) == 'dir') {
          $this->addControllerDirectory($file . DIRECTORY_SEPARATOR . 'controllers', $file);
        }
      }
      closedir($dh);
    }
    return $this;
  }

  /**
   * 返回模块目录
   *
   * @return string
   */
  public function getModuleDirectory()
  {
    return self::$_moduleDirectory;
  }

  /**
   * 设置控制器目录集
   *
   * @param array $directorys
   * @return void
   */
  public function setControllerDirectorys($directorys)
  {
    self::$_controllerDirectorys = $directorys;
  }

  /**
   * 返回控制器目录集
   *
   * @return array
   */
  public function getControllerDirectorys()
  {
    return self::$_controllerDirectorys;
  }

  /**
   * 检查是否为模块
   *
   * @param string $namespace
   * @return bool
   */
  public function isModule($namespace)
  {
    return isset(self::$_controllerDirectorys[$namespace]);
  }

  /**
   * 检查是否为控制器
   *
   * @param string $controller
   * @param string $module
   * @return bool
   */
  public function isController($controller, $module = null)
  {
    $path = self::getModuleDirectory() . @self::$_controllerDirectorys[$module] . '/';
    $file = ucfirst($controller) . 'Controller.php';
    return Suco_Loader_File::exists($path . $file);
  }

  /**
   * 开始分发，并返回相应的控制器
   *
   * @param string $controller
   * @param string $action
   * @param string $module
   * @param array $params
   * @return object
   */
  public function dispatch($controller = null, $action = null, $module = null, $params = array())
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

    //DEBUG
    $params = array_merge($this->_request->getParams(), (array)$params);

    $controller->setDispatcher($this);
    $controller->setRequest($this->_request);
    $controller->setResponse($this->_response);
    $controller->setParams($params);
    $controller->dispatch($this->getAction());
    return $controller;
  }

  /**
   * 载入控制器文件
   *
   * @param string $moduleName
   * @param string $controllerName
   * @return void
   */
  protected function _loadControllerFile($moduleName, $controllerName)
  {
    if (!$this->isModule($moduleName)) {
      require_once 'Suco/Controller/Dispatcher/Exception.php';
      throw new Suco_Controller_Dispatcher_Exception("系统未指定模块 {$moduleName}");
    }
    $path = (self::$_moduleDirectory ? rtrim(self::$_moduleDirectory, DIRECTORY_SEPARATOR) : '.') . DIRECTORY_SEPARATOR
        . trim(self::$_controllerDirectorys[$moduleName], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    if (!is_dir($path)) {
      require_once 'Suco/Controller/Dispatcher/Exception.php';
      throw new Suco_Controller_Dispatcher_Exception("找不到模块目录 {$path}");
    }

    if (is_file(realpath($path . 'Abstract.php'))) {
      require_once realpath($path . 'Abstract.php');
    }

    $file = ucfirst($controllerName) . 'Controller.php';
    if (!file_exists($path . $file)) {
      require_once 'Suco/Controller/Dispatcher/Exception.php';
      throw new Suco_Controller_Dispatcher_Exception("找不到控制器文件 {$path}{$file}");
    }

    require_once $path . $file;
  }

  /**
   * 格式化控制器
   *
   * @param string $controller
   * @return void
   */
  protected function _formatControllerName($controller)
  {
    $namespace = null;
    if ($this->getModule() != $this->getDefaultModule()) {
      $namespace = ucfirst($this->getModule()) . '_';
    }

    return $namespace . ucfirst($controller) . 'Controller';
  }
}