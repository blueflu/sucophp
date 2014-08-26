<?php 

class Suco_Controller_Front
{
	protected $_options;
	
	protected $_request;
	protected $_response;
	protected $_router;
	protected $_dispatcher;
	
	protected $_throwException = true;
	
	public function setRequest($request = null)
	{
		if ($request instanceof Suco_Controller_Request_Interface) {
			$this->_request = $request;
		} else {
			require_once 'Suco/Controller/Request/Http.php';
			$this->_request = new Suco_Controller_Request_Http();
		}
	}
	
	public function getRequest()
	{
		if (!$this->_request) {
			$this->setRequest();
		}
		return $this->_request;
	}
	
	public function setResponse($response = null)
	{
		if ($response instanceof Suco_Controller_Response_Interface) {
			$this->_response = $response;
		} else {
			require_once 'Suco/Controller/Response/Http.php';
			$this->_response = new Suco_Controller_Response_Http();
		}
	}
	
	public function getResponse()
	{
		if (!$this->_response) {
			$this->setResponse();
		}
		return $this->_response;
	}
	
	public function setRouter($router = null)
	{
		if ($router instanceof Suco_Controller_Router_Interface) {
			$this->_router = $router;
		} else {
			require_once 'Suco/Controller/Router/Route.php';
			$this->_router = new Suco_Controller_Router_Route();
			$this->_router->setRequest($this->getRequest());
		}
	}
	
	public function getRouter()
	{
		if (!$this->_router) {
			$this->setRouter();
		}
		return $this->_router;
	}
	
	public function setDispatcher($dispatcher = null)
	{
		if ($dispatcher instanceof Suco_Controller_Dispatcher_Interface) {
			$this->_dispatcher = $dispatcher;
		} else {
			require_once 'Suco/Controller/Dispatcher/Standard.php';
			$this->_dispatcher = new Suco_Controller_Dispatcher_Standard($this->getRequest(), $this->getResponse());
		}		
	}
	
	public function getDispatcher()
	{
		if (!$this->_dispatcher) {
			$this->setDispatcher();
		}
		
		return $this->_dispatcher;
	}
	
	public function run($bootstrap)
	{
		require_once $bootstrap;
		$this->_resource = new Bootstrap();
		
		try {
			$this->getRouter()->routing();
			$this->getDispatcher()
				 ->dispatch();
		} catch (Suco_Exception $e) {
			if ($this->getDispatcher()->isController('error', $this->getDispatcher()->getModule())) {
				$this->getDispatcher()
					 ->dispatch('error', 'default', $this->getDispatcher()->getModule(), array('error_handle' => $e));
			} else {
				echo $e->dump();
			}
		}
		
		$this->getResponse()->appendBody(ob_get_clean())
							->send();
	}
}