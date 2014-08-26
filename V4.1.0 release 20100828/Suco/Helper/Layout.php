<?php

require_once 'Suco/Helper/Abstract.php';

class Suco_Helper_Layout extends Suco_Helper_Abstract
{
	protected $_container;
	protected $_layoutPath;
	protected $_contentKey = 'content';
	
	public function layout()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}
	
	public function __set($key, $value)
	{
		$this->_container[$key] = $value;
		return $this;
	}
	
	public function __get($key)
	{
		if (!isset($this->_container[$key])) {
			return null;
		}
		return $this->_container[$key];
	}
}