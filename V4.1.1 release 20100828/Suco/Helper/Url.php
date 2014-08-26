<?php

class Suco_Helper_Url
{
	protected $_url;
	protected $_route;
	protected $_filter = array(
		0 => array('/', '.', '&', '+'),
		1 => array('$syb1d', '$syb2d', '$syb3d', '$syb4d'),
	);
	
	public function url($url = null, $route = null)
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		if ($url) { $instance->_url = $url;	}
		if ($route) { $instance->_route = $route; }
		return $instance;
	}
	
	public function encode($param)
	{
		return str_replace($this->_filter[0], $this->_filter[1], base64_encode($param));
	}
	
	public function decode($param)
	{
		return base64_decode(str_replace($this->_filter[1], $this->_filter[0], $param));
	}

	public function __toString()
	{
		return Suco_Application::getInstance()
			->getRouter()
			->reverse($this->_url, $this->_route);
	}
}