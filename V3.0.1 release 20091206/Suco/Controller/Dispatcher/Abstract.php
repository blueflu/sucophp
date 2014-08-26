<?php 

require_once 'Suco/Controller/Dispatcher/Interface.php';

class Suco_Controller_Dispatcher_Abstract implements Suco_Controller_Dispatcher_Interface
{
	protected $_module;
	protected $_controller;
	protected $_action;
	
	protected $_defaultModule = 'default';
	protected $_defaultController = 'index';
	protected $_defaultAction = 'default';

	public function setModule($module = null)
	{
		$this->_module = $module ? $module : $this->getDefaultModule();
	}
	
	public function getModule()
	{
		return $this->_module;	
	}
	
	public function setController($controller = null)
	{
		$this->_controller = $controller ? $controller : $this->getDefaultController();
	}
	
	public function getController()
	{
		return $this->_controller;
	}
	
	public function setAction($action)
	{
		$this->_action = $action ? $action : $this->getDefaultAction();
	}
	
	public function getAction()
	{
		return $this->_action;
	}
	
	public function setDefaultModule($module)
	{
		$this->_defaultModule = $module;
	}
	
	public function getDefaultModule()
	{
		return $this->_defaultModule;
	}
	
	public function setDefaultController($controller)
	{
		$this->_defaultController = $controller;
	}
	
	public function getDefaultController()
	{
		return $this->_defaultController;
	}
	
	public function setDefaultAction($action)
	{
		$this->_defaultAction = $action;
	}
	
	public function getDefaultAction()
	{
		return $this->_defaultAction;
	}
}