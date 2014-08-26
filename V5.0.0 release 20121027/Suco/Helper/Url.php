<?php
/**
 * Suco_Helper_Url URL处理
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Url implements Suco_Helper_Interface
{
	protected $_url;
	protected $_route;

	public static function callback($args)
	{
		return new self($args[0], $args[1]);
	}

	public function __construct($url = null, $route = null)
	{
		$this->_url = $url;
		$this->_route = $route;
	}

	public function encode($param)
	{
		return Suco_Controller_Router_Route_Abstract::encode($param);
	}

	public function decode($param)
	{
		return Suco_Controller_Router_Route_Abstract::decode($param);
	}

	public function __toString()
	{
		if (!$this->_url) { return ''; }
		return Suco_Application::instance()
			->getRouter()
			->reverse($this->_url, $this->_route);
	}
}