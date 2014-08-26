<?php

class Suco_Helper_UrlParam
{
	protected $_filter = array(
		0 => array('/', '.', '&', '+'),
		1 => array('$syb1d', '$syb2d', '$syb3d', '$syb4d'),
	);
	
	public function urlParam()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
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
}