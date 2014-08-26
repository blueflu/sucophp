<?php
/**
 * Suco_Helper_Currency 格式化货币
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Currency implements Suco_Helper_Interface
{
	protected $_amount;

	public static function callback($args)
	{
		return new self($args[0]);
	}

	public function __construct($amount)
	{
		$this->_amount = $amount;
	}

	public function __toString()
	{
		return Suco_Application::instance()
			->getLocale()
			->currency($this->_amount);
	}
}