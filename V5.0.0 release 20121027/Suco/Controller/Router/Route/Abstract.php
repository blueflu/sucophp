<?php
/**
 * Suco_Controller_Router_Route_Abstract 路由规则抽象
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

class Suco_Controller_Router_Route_Abstract
{
	/**
	 * 正向解析表达式
	 * @var string
	 */
	protected $_pattern;

	/**
	 * 反向解析表达式
	 */
	protected $_reverse;

	/**
	 * 参数映射
	 * @var array
	 */
	protected $_mapping;

	/**
	 * 默认动作
	 * @var array
	 */
	protected $_defaults;

	/**
	 * 过滤符号
	 * @var array
	 */
	protected static $_filter = array(
		0 => array(' ', '/', '.', '&', '+', '=', '%'),
		1 => array('$0d', '$1d', '$2d', '$3d', '$4d', '$5d', '$6d'),
	);

	/**
	 * 构造函数
	 * @return void
	 */
	public function __construct($pattern = null, $options = null)
	{
		$this->_pattern = $pattern;

		if (isset($options[Suco_Controller_Router_Route::ROUTE_DEFAULTS])) {
			$this->_defaults = $options[Suco_Controller_Router_Route::ROUTE_DEFAULTS];
		}
		if (isset($options[Suco_Controller_Router_Route::ROUTE_MAPPING])) {
			$this->_mapping = $options[Suco_Controller_Router_Route::ROUTE_MAPPING];
		}
		if (isset($options[Suco_Controller_Router_Route::ROUTE_REVERSE])) {
			$this->_reverse = $options[Suco_Controller_Router_Route::ROUTE_REVERSE];
		}
	}

	/**
	 * 参数编码
	 *
	 * @param string $str
	 * @return string
	 */
	public function encode($str)
	{
		//return urlencode(str_replace(self::$_filter[0], self::$_filter[1], trim($str)));
		return str_replace(self::$_filter[0], self::$_filter[1], trim($str));
	}

	/**
	 * 参数解码
	 *
	 * @param string $str
	 * @return string
	 */
	public function decode($str)
	{
		return str_replace(self::$_filter[1], self::$_filter[0], urldecode(trim($str)));
		//return str_replace(self::$_filter[1], self::$_filter[0], $str);
	}

}