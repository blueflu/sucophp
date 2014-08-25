<?php

abstract class Suco_Controller_Action
{
	protected $_request;
	protected $_response;
	protected $_params;
	
	public function init() {}
	
	public function setParam($key, $value)
	{
		$this->_params[$key] = $value;
	}
	
	public function getParam($key)
	{
		return $this->_params[$key];
	}
	
	public function setParams($params)
	{
		$this->_params = $params;
	}
	
	public function getParams()
	{
		return $this->_params;
	}
	
	public function getModulePath()
	{
		return dirname($this->getControllerPath());
	}
	
	public function getControllerPath()
	{
		static $path;
		if (!isset($path)) {
			$path = Suco_Controller_Front::getInstance()
					->getDispatcher()
					->getControllerDirectory();
		}
		
		return $path;
	}
	
	public function setRequest(Suco_Controller_Request_Interface $request = null)
	{
		if ($request != null) {
			$this->_request = $request;
		} else {
			$this->_request = Suco_Controller_Front::getInstance()->getRequest();
		}
	}
	
	public function getRequest()
	{
		if (!$this->_request) {
			$this->setRequest();
		}
		return $this->_request;
	}
	
	public function setResponse(Suco_Controller_Response_Interface $response = null)
	{
		if ($response != null) {
			$this->_response = $response;
		} else {
			$this->_response = Suco_Controller_Front::getInstance()->getResponse();
		}
	}
	
	public function getResponse()
	{
		if (!$this->_response) {
			$this->setResponse();
		}
		return $this->_response;		
	}
	
	public function redirect($url)
	{
		$url = $this->url($url);
		$this->_response->redirect($url);
	}
	
	public function url($url, $route = 'default')
	{
		return $this->_request->getRouter()->reverse($url);
	}
		
	public function dispatch($action)
	{
		$this->init();
		$this->__call($this->_formatActionName($action));
		$this->getResponse()->appendBody(ob_get_contents())
							->send();
	}
	
	protected function __call($method, $args = array())
	{
		if (substr($method, 0, 2) == 'do') {
			if (!method_exists($this, $method)) {
				require_once 'Suco/Controller/Dispatcher/Exception.php';
				throw new Suco_Controller_Dispatcher_Exception("没有找到指定动作 {$method}");
			}
			return call_user_func(array($this, $method));
		}
		
		require_once 'Suco/Exception.php';
		throw new Suco_Exception('Not Found Method ' . $method);
	}
		
	protected function _formatActionName($action)
	{
		return 'do' . ucfirst($action);
	}
}