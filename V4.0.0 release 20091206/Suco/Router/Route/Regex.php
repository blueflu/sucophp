<?php

require_once 'Suco/Router/Route/Abstract.php';

class Suco_Router_Route_Regex extends Suco_Router_Route_Abstract
{
    
	public function match($request)
	{
		$url = $request->getPathInfo();
		if (preg_match('#'.$this->_pattern.'#', $url, $values)) {
			$params = $this->_defaults;
			foreach ($values as $pos => $value) {
				if (isset($this->_mapping[$pos])) {
					$key = $this->_mapping[$pos];
					$params[$key] = $value;
				}
			}
			return $params;
		}
		return false;
	}
	
	public function reverseMatch($options)
	{
		if ($this->_defaults == array_intersect($this->_defaults, $options)) {
			$querys[0] = $this->_reverse;
			foreach ($this->_mapping as $pos => $key) {
				if (!isset($options[$key])) {
					return false;
				} else {
					$querys[$pos] = $options[$key];
				}
			}
			return call_user_func_array('sprintf', $querys);
		}
	}
	
}