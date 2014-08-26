<?php
/**
 * Suco_Controller_Router_Route_Regex 正则路由
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

require_once 'Suco/Controller/Router/Route/Abstract.php';

class Suco_Controller_Router_Route_Regex extends Suco_Controller_Router_Route_Abstract implements Suco_Controller_Router_Route_Interface
{
	/**
	 * 正向解析
	 *
	 * @param object $request 请求对象
	 * @return array
	 */
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

	/**
	 * 反向解析
	 *
	 * @param array $options
	 * @return string
	 */
	public function reverseMatch($options)
	{
		foreach ($this->_defaults as $key => $val) {
			if (isset($options[$key]) && !$options[$key]) continue;
			if ($val != $options[$key]) {
				return false;
			}
		}
		$querys[0] = $this->_reverse;
		foreach ($this->_mapping as $pos => $key) {
			if (!isset($options[$key])) {
				return false;
			} else {
				if ($options[$key]) {
					$querys[$pos] = $this->encode($options[$key]);
				}
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