<?php

require_once 'Suco/Router/Interface.php';

class Suco_Router_Route implements Suco_Router_Interface
{
	const ROUTE_MAPPING = 'mapping';
	const ROUTE_PATTERN = 'pattern';
	const ROUTE_REVERSE = 'reverse';
	const ROUTE_DEFAULTS = 'defaults';
	
	const ROUTE_URL_DELIMIT = 'urlDelimit';
	const ROUTE_QUERY_DELIMIT = 'queryDelimit';
	const ROUTE_SUFFIX = 'suffix';
	
	protected $_request;
	protected $_routes = array();
	protected $_currentRoute;

	public function setOptions($options)
	{
		foreach ($options as $key => $option) {
			$method = 'set' . ucfirst($key);
			$this->$method($option);
		}
	}
	
	public function setRoutes($routes)
	{
		foreach ($routes as $name => $route) {
			$this->addRoute($name, $route);
		}
	}
	
	public function setRequest(Suco_Request_Interface $request)
	{
		$this->_request = $request;
	}
	
	public function addRoute($name, $route)
	{
		if (is_array($route)) {
			$adapter = $route['type'];
			$pattern = $route['pattern'];
			require_once str_replace('_', '/', $route['type']) . '.php';
			$this->_routes[$name] = new $adapter($pattern, $route);
		} elseif ($route instanceof Suco_Router_Route_Abstract) {
			$this->_routes[$name] = $routes;
		} else {
			require_once 'Suco/Router/Exception.php';
			throw new Suco_Router_Exception('无效设置');
		}
	}
	
	public function removeRoute($name)
	{
		unset($this->_routes[$name]);
	}
	
	public function clearRoute()
	{
		$this->_routes = array();
	}
	
	public function routing()
	{
		$routes = array_reverse($this->_routes);
		foreach ($routes as $name => $route) {
			if ($params = $route->match($this->_request)) {
				$this->_currentRoute = $name;
				$this->_request->setParams($params);
				return;
			}
		}
	}
	
	public function reverse($options)
	{
		if (!is_array($options)) {
			parse_str($options, $options);
		}
		
    	$actionKey = $this->_request->getActionKey();
    	$controllerKey = $this->_request->getControllerKey();
    	$moduleKey = $this->_request->getModuleKey();
    	
    	if (!isset($options[$moduleKey])) {
    		$options[$moduleKey] = $this->_request->getModuleName();
    	}
    	if (!isset($options[$controllerKey])) {
    		$options[$controllerKey] = $this->_request->getControllerName();
    	}
    	if (!isset($options[$actionKey])) {
    		$options[$actionKey] = $this->_request->getActionName();
    	}

    	$routes = array_reverse($this->_routes);
		foreach ($routes as $name => $route) {
			if ($url = $route->reverseMatch($options)) {
				return $this->_request->getBaseUrl() . '/' . $url;
			}
		}
		
		return '?' . http_build_query($options);
	}
}