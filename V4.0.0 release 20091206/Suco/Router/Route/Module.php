<?php

require_once 'Suco/Router/Route/Abstract.php';

class Suco_Router_Route_Module extends Suco_Router_Route_Abstract
{
	protected $_dispatcher;

	protected $_queryDelimit = '/';
	protected $_urlDelimit = '/';
	protected $_urlVariable = ':';
	protected $_wildcard = '*';
	
	protected $_defaultRegex = '([^/]+)';
	protected $_configs = array();
	protected $_suffix = null;
	
	public function __construct($pattern = null, $options = null)
	{
		if (isset($options[Suco_Router_Route::ROUTE_SUFFIX])) {
			$this->_suffix = $options[Suco_Router_Route::ROUTE_SUFFIX];
		}
		if (isset($options[Suco_Router_Route::ROUTE_QUERY_DELIMIT])) {
			$this->_queryDelimit = $options[Suco_Router_Route::ROUTE_QUERY_DELIMIT];
		}
		if (isset($options[Suco_Router_Route::ROUTE_URL_DELIMIT])) {
			$this->_urlDelimit = $optinos[Suco_Router_Route::ROUTE_URL_DELIMIT];
		}
		
		$this->_dispatcher = Suco_Application::getInstance()->getDispatcher();
		parent::__construct($pattern, $options);
	}
	
	public function match($request)
	{
		$pathinfo = str_replace($this->_suffix, null, $request->getPathInfo());
		$parts = explode($this->_urlDelimit, trim($this->_pattern, $this->_urlDelimit));
		$paths = explode($this->_urlDelimit, trim($pathinfo, $this->_urlDelimit));
		
		$params = $this->_defaults;
		
		if ($parts[0] == $this->_urlVariable . 'module' && !$this->_dispatcher->isModule($paths[0])) {
			array_shift($parts);
		}

		foreach ($parts as $pos => $part) {
			if (substr($part, 0, 1) == $this->_urlVariable) {//变量
				$varname = substr($part, 1);
				$pattern = isset($this->_mapping[$varname]) ? $this->_mapping[$varname] : $this->_defaultRegex;
				if (isset($paths[$pos]) && preg_match('#'.$pattern.'#', $paths[$pos], $values)) {
					$params[$varname] = $values[1];
				}
				continue;
			} elseif ($part == $this->_wildcard) {//通配符
				if ($this->_urlDelimit != $this->_queryDelimit) {
					$querys = explode($this->_queryDelimit, $paths[$pos]);
					$key = 0;
				} else {
					$querys = $paths;
					$key = &$pos;
				}
				while (isset($querys[$key])) {
					$varname = urldecode($querys[$key]);
					if (isset($querys[$key+1])) {
						$params[$varname] = urldecode($querys[$key+1]);
					}
					$key += 2;
				}
				continue;
			} else { //静态变量
				if (isset($paths[$pos]) && $part == $paths[$pos]) {
					continue;
				}
				return false;
			}
		}
		
		return $params;
	}
	
	public function reverseMatch($options)
	{
		$parts = explode($this->_urlDelimit, trim($this->_pattern, $this->_urlDelimit));
		foreach ($parts as $pos => $part) {
			if (substr($part, 0, 1) == $this->_urlVariable) {
				$varname = substr($part, 1);
				if (!isset($options[$varname])) {
					return false;
				}
				$url[$varname] = $options[$varname];
				unset($options[$varname]);
				continue;
			} elseif ($part == $this->_wildcard) {
				$params = array();
    			if (count($options) > 0) {
    				foreach ($options as $key => $value) {
    					$params[] = urlencode($key);
    					$params[] = urlencode($value);
    				}
    			}
    			continue;
			}
		}
		
		//默认模块
		if ($url['module'] == $this->_dispatcher->getDefaultModule()) {
			unset($url['module']);
		}
		/*
		if ($url['controller'] == $this->_dispatcher->getDefaultController()) {
			unset($url['controller']);
		}
		*/
		if ($url['action'] == $this->_dispatcher->getDefaultAction())
		{
			unset($url['action']);
		} 
		
		$url = implode($this->_urlDelimit, $url) . $this->_urlDelimit;
		if ($params) {
			$url .= implode($this->_queryDelimit, $params);
		}
		return $url;
	}
}