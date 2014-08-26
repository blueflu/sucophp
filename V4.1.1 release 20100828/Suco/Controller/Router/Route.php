<?php

require_once 'Suco/Controller/Router/Interface.php';

class Suco_Controller_Router_Route implements Suco_Controller_Router_Interface
{
	const ROUTE_MAPPING = 'mapping';
	const ROUTE_PATTERN = 'pattern';
	const ROUTE_REVERSE = 'reverse';
	const ROUTE_DEFAULTS = 'defaults';
	
	protected $_request;
	protected $_routes = array();
	protected $_currentRoute;
	protected $_delimit = '/';
	protected $_domainMapping = array();

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
	
	public function setRequest(Suco_Controller_Request_Interface $request)
	{
		$this->_request = $request;
	}
	
	public function setDomainMapping($mapping)
	{
		$this->_domainMapping = $mapping;
		return $this;
	}
	
	public function getDomainMapping()
	{
		return $this->_domainMapping;
	}
	
	public function addRoute($name, $route)
	{
		if (is_array($route)) {
			$adapter = $route['type'];
			$pattern = $route['pattern'];
			require_once str_replace('_', '/', $route['type']) . '.php';
			$this->_routes[$name] = new $adapter($pattern, $route);
		} elseif ($route instanceof Suco_Controller_Router_Route_Abstract) {
			$this->_routes[$name] = $route;
		} else {
			require_once 'Suco/Controller/Router/Exception.php';
			throw new Suco_Controller_Router_Exception('无效设置');
		}
		return $this;
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
		$this->_request->setRouter($this);
		
		$routes = array_reverse($this->_routes);
		foreach ($routes as $name => $route) {
			if ($params = $route->match($this->_request)) {
				if ($this->_domainMapping) {
					$host = $this->_request->getHost();
					if (isset($this->_domainMapping[$host])) {
						$params[$this->_request->getModuleKey()] = $this->_domainMapping[$host];
					}
				}
				$this->_currentRoute = $name;
				$this->_request->setQuerys($params);
				return;
			}
		}
	}
	
	public function reverse($querys, $name = null)
	{
		if (!$querys) {
			return null;
		} elseif (is_string($querys) && strpos($querys, $this->_delimit) !== false) { //已经转换
			return $querys;
		} elseif (!is_array($querys)) {
			parse_str($querys, $options);
		}
		
		$moduleKey = $this->_request->getModuleKey();
    		$controllerKey = $this->_request->getControllerKey();
    		$actionKey = $this->_request->getActionKey();
    	
		//如果链接是以&开头的.则为追加查询参数
		if (is_string($querys) && substr($querys, 0, 1) == '&') {
			$options = array_merge($this->_request->getQuerys(), array(
				$moduleKey => $this->_request->getModuleName(),
				$controllerKey => $this->_request->getControllerName(),
				$actionKey => $this->_request->getActionName(),
			), $options);
		}
    	
    	//补齐URL
    	if (isset($options[$controllerKey]) && !isset($options[$moduleKey])) {
    		$options[$moduleKey] = $this->_request->getModuleName();
    	}
    	if (isset($options[$actionKey]) && !isset($options[$controllerKey])) {
    		$options[$moduleKey] = $this->_request->getModuleName();
    		$options[$controllerKey] = $this->_request->getControllerName();
    	}
    	
    	$options = array_merge(array($moduleKey => '', $controllerKey => '', $actionKey => ''), $options);
    	$routes = array_reverse($this->_routes);
		$baseUrl = Suco_Application::getInstance()->getRequest()->getHost()
			. Suco_Application::getInstance()->getRequest()->getBaseUrl();
		if ($this->_domainMapping) {
			$module = $options[$moduleKey];
			foreach ($this->_domainMapping as $host => $mod) {
				if ($mod == $module) {
					$baseUrl = "http://" . $host
						. Suco_Application::getInstance()->getRequest()->getBaseUrl();
					break;
				}
			}
		}
		
    	if (isset($routes[$name])) {
    		if ($url = $routes[$name]->reverseMatch($options)) {
				return $baseUrl . '/' . trim($url, '/');
			}
    	} else {
			foreach ($routes as $name => $route) {
				if ($url = $route->reverseMatch($options)) {
					return $baseUrl . '/' . trim($url, '/');
				}
			}
    	}
		
		foreach ($options as $key => $val) {
			if (empty($val)) {
				unset($options[$key]);
			}
		}
		return '?' . http_build_query($options);
	}
}