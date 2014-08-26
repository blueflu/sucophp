<?php 

class Suco_Controller_Front
{
	protected $_options;
	
	protected $_request;
	protected $_response;
	protected $_router;
	protected $_dispatcher;
	
	public function getInstance($config = null)
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
			$instance->init($config);
		}
		return $instance;
	}
	
	public function init($config)
	{
		if (is_string($config)) {
			require_once 'Suco/Config.php';
			$this->_options = Suco_Config::factory($config)->production->toArray();
		} elseif (is_array($config)) {
			$this->_options = $config;
		} elseif ($config instanceof Suco_Config_Interface) {
			$this->_options = $config->production->toArray();
		}
	}
	
	public function setup()
	{
		if (isset($this->_options['php'])) {
			foreach ($this->_options['php'] as $item => $value) {
				ini_set($item, $value);
			}
		}
		if (isset($this->_options['dispatcher'])) {
			$this->getDispatcher()->setOptions($this->_options['dispatcher']);
		}
		if (isset($this->_options['router'])) {
			$this->getRouter()->setOptions($this->_options['router']);
		}
		if ($this->_options['db']) {
			require_once 'Suco/Db.php';
			require_once 'Suco/Db/Table.php';
			Suco_Db_Table::setAdapter(Suco_Db::factory($this->_options['db']['params']));
		}
	}
	
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
		
		//启动路由;
		$this->getRouter()->routing();
		return $this->_dispatcher;
	}
	
	public function run($bootstrap)
	{
		require_once $bootstrap;
		new Bootstrap();
		
		$this->setup();
		$this->getDispatcher()
			 ->dispatch();
	}
}