<?php

abstract class Suco_Controller_Action
{
	protected $_request;
	protected $_response;
	
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
			$path = Suco_Controller_Front::getInstance()
					->getDispatcher()
					->getControllerDirectory();
		}
		
		return $path;
	}
	
	public function run($action, Suco_Controller_Request_Interface $request = null, Suco_Controller_Response_Interface $response = null)
	{
		$this->init();
		if ($request != null) {
			$this->_request = $request;
		} else {
			$this->_request = Suco_Controller_Front::getInstance()->getRequest();
		}
		
		if ($response != null) {
			$this->_response = $response;
		} else {
			$this->_response = Suco_Controller_Front::getInstance()->getResponse();
		}
		
		$this->_dispatch($action);
		$this->_response->appendBody(ob_get_contents())
						->send();
	}
	
	public function __call($method, $args = array())
	{
		if (substr($method, 0, 2) == 'do') {
			if (!method_exists($this, $method)) {
				require_once 'Suco/Controller/Dispatcher/Exception.php';
				throw new Suco_Controller_Dispatcher_Exception("没有找到指定动作 {$method}");
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