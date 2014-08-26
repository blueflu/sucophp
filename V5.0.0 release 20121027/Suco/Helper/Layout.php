<?php
/**
 * Suco_Helper_Layout 页面布局
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Layout implements Suco_Helper_Interface
{
	protected $_container;
	protected $_layoutPath;
	protected $_contentKey = 'content';

	public static function callback($args)
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