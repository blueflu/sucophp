<?php

class Suco_Helper_Dom_Element
{
	protected $_attributes = array();
	
	public function __call($method, $params)
	{
		if (substr($method, 0, 3) == 'set') {
			$varname = strtolower(substr($method, 3));
			$this->set($varname, $params[0]);
			return $this;
		}
	}
		
	public function set($name, $value)
	{
		$this->_attributes[$name] = $value;
	}
	
	public function get($name)
	{
		return $this->_attributes[$name];
	}
}