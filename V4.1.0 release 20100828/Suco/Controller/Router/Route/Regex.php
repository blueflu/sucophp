<?php

require_once 'Suco/Controller/Router/Route/Abstract.php';

class Suco_Controller_Router_Route_Regex extends Suco_Controller_Router_Route_Abstract
{

	public function match($request)
	{
		$url = $request->getPathInfo();
		if (preg_match('#'.$this->_pattern.'#', $url, $values)) {
			$params = $this->_defaults;
			foreach ($values as $pos => $value) {
				if (isset($this->_mapping[$pos])) {
					$key = $this->decode($this->_mapping[$pos]);
					$params[$key] = $this->decode($value);
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
					$querys[$pos] = $this->encode($options[$key]);
				}
			}
			$keys = array_merge(array_values($this->_mapping), array_keys($this->_defaults));
			foreach ($keys as $key) {
				unset($options[$key]);
			}
			foreach ($options as $key => $row) {
				if (!$row) {
					unset($options[$key]);
				} else {
					$options[$key] = $this->encode($options[$key]);	
				}
			}
			$querys[0] .= $options ? '?'.http_build_query($options) : '';
			return @call_user_func_array('sprintf', $querys);
		}
	}
	
}