<?php

require_once 'Suco/Controller/Router/Route/Abstract.php';

class Suco_Controller_Router_Route_Module extends Suco_Controller_Router_Route_Abstract
{
	protected $_dispatcher;

	protected $_variable = ':';
	protected $_wildcard = '*';
	
	//发生冲突时的转义符
	protected $_delimit = array('/', '--');
	protected $_escape = array('#d1#', '#d2#');
	
	protected $_defaultRegex = '([^/]+)';
	protected $_configs = array();
	
	protected $_suffix = '.html';
	
	public function __construct($pattern = null, $options = null)
	{
		if (isset($options['suffix'])) {
			$this->_suffix = $options['suffix'];
		}
		if (isset($options['delimit'])) {
			$this->_delimit = $optinos['delimit'];
		}
		
		$this->_dispatcher = Suco_Application::getInstance()->getDispatcher();
		parent::__construct($pattern, $options);
	}
	
	public function match($request)
	{
		$pathinfo = str_replace($this->_suffix, null, $request->getPathInfo());
		$parts = explode($this->_delimit[0], trim($this->_pattern, $this->_delimit[0]));
		$paths = explode($this->_delimit[0], trim($pathinfo, $this->_delimit[0]));
		$params = $this->_defaults;
		if ($parts[0] == $this->_variable . 'module' && !$this->_dispatcher->isModule($paths[0])) {
			array_shift($parts);
		}

		foreach ($parts as $pos => $part) {
			if (substr($part, 0, 1) == $this->_variable) {//变量
				$varname = substr($part, 1);
				$pattern = isset($this->_mapping[$varname]) ? $this->_mapping[$varname] : $this->_defaultRegex;
				if (isset($paths[$pos]) && preg_match('#'.$pattern.'#', $paths[$pos], $values)) {
					$params[$varname] = $values[1];
				}
				continue;
			} elseif ($part == $this->_wildcard) {//通配符
				if ($this->_delimit[0] != $this->_delimit[1]) {
					$querys = isset($paths[$pos]) ? explode($this->_delimit[1], $paths[$pos]) : array();
					$key = 0;
				} else {
					$querys = $paths;
					$key = &$pos;
				}
				while (isset($querys[$key])) {
					$varname = $this->decode($querys[$key]);
					if (isset($querys[$key+1])) {
						$value = str_replace($this->_escape, $this->_delimit, $this->decode($querys[$key+1]));
						$params[$varname] = $value;
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
		
		if (!empty($_GET) && empty($_POST)) {
			foreach ($_GET as $key => $val) {
				if ($val == NULL) {
					unset($_GET[$key]);
					unset($params[$key]);
				}
			}
			$url = Suco_Application::getInstance()->getRequest()->getBaseUrl()
				 . '/' . trim($this->reverseMatch(array_merge($params, $_GET)), '/');
			header('Location:' . $url);
			exit;
		}
		return $params;
	}
	
	public function reverseMatch($options)
	{
		if ($mapping = Suco_Application::getInstance()->getRouter()->getDomainMapping()) {
			foreach ($mapping as $host => $mod) {
				if ($mod == $options['module']) {
					$options['module'] = '';
					break;
				}
			}
		}
		$parts = explode($this->_delimit[0], trim($this->_pattern, $this->_delimit[0]));
		foreach ($parts as $pos => $part) {
			if (substr($part, 0, 1) == $this->_variable) {
				$varname = substr($part, 1);
				if (!isset($options[$varname])) {
					return false;
				} elseif($options) {
					$url[$varname] = $options[$varname];
				}
				unset($options[$varname]);
				continue;
			} elseif ($part == $this->_wildcard) {
				$params = array();
    			if (count($options) > 0) {
    				foreach ($options as $key => $value) {
    					if ($value != null) {
	    					$value = str_replace($this->_delimit, $this->_escape, $value);
	    					$params[] = $this->encode($key);
	    					$params[] = $this->encode($value);
    					}
    				}
    			}
    			continue;
			}
		}
		
		//默认模块
		
		if ($url['module'] == $this->_dispatcher->getDefaultModule() || !$url['module']) {
			unset($url['module']);
		}
		if ($url['controller'] == $this->_dispatcher->getDefaultController() && !isset($url['module']) || !$url['controller']) {
			unset($url['controller']);
		}/*
		if ($url['action'] == $this->_dispatcher->getDefaultAction() || !$url['action'])
		{
			unset($url['action']);
		}
		*/
		
		$url = implode($this->_delimit[0], $url) . $this->_delimit[0];
		if ($params) {
			$url .= implode($this->_delimit[1], $params) . $this->_suffix;
		}
		return $url;
	}
}