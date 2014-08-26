<?php

abstract class Suco_Controller_Abstract
{
	protected $_request;
	protected $_response;
	
	public function url($options)
	{
		$router = Suco_Application::getInstance()->getRouter();
		return $router->reverse($options);
	}
	
	public function init()
	{
		
	}
	
	public function getModulePath()
	{
		return dirname($this->getControllerPath());
	}
	
	public function getControllerPath()
	{
		static $path;
		if (!isset($path)) {
			$dispatcher = Suco_Application::getInstance()->getDispatcher();
			$path = Suco_Application::getInstance()
					->getDispatcher()
					->getControllerDirectory();
		}
		
		return $path;
	}
	
	public function run($action, Suco_Request_Interface $request = null, Suco_Response_Interface $response = null)
	{
		$this->init();
		if ($request != null) {
			$this->_request = $request;
		} else {
			require_once 'Suco/Request/Http.php';
			$this->_request = new Suco_Request_Http();
		}
		
		if ($response != null) {
			$this->_response = $response;
		} else {
			require_once 'Suco/Response/Http.php';
			$this->_response = new Suco_Response_Http();
		}
		
		$this->_dispatch($action);
		$this->_response->appendBody(ob_get_contents())
						->send();
	}
	
	public function __call($method, $args = array())
	{
		if (substr($method, 0, 2) == 'do') {
			if (!method_exists($this, $method)) {
				require_once 'Suco/Dispatcher/Exception.php';
				throw new Suco_Dispatcher_Exception("没有找到指定动作 {$method}");
			}
			return call_user_func(array($this, $method));
		}
		
		require_once 'Suco/Exception.php';
		throw new Suco_Exception('Not Found Method');
	}
		
	protected function _formatActionName($action)
	{
		return 'do' . ucfirst($action);
	}
	
	protected function _dispatch($action)
	{
		$this->__call($this->_formatActionName($action));
	}
}