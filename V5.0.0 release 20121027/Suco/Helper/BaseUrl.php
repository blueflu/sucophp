<?php
/**
 * Suco_Helper_BaseUrl 获取基地址
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_BaseUrl implements Suco_Helper_Interface
{
	protected $_baseUrl;
	protected $_url;

	public static function callback($args)
	{
		return new self($args[0]);
	}

	public function __construct($url)
	{
		$site = Suco_Application::instance()->getRequest()->getHost();
		$site = $site ? trim($site, '/').'/' : '';

		$baseUrl = Suco_Application::instance()->getRequest()->getBasePath();
		$baseUrl = $baseUrl ? trim($baseUrl, '/').'/' : '';

		$this->_url = $url;
		$this->_baseUrl = $site.$baseUrl;
	}

	public function __toString()
	{
		if ($this->_url) {
			$src = @parse_url($this->_url);
			if (isset($src['scheme'])) {
				return $this->_url;
			} else {
				return $this->_baseUrl . trim($this->_url, '/');
			}
		} else {
			return $this->_baseUrl;
		}
	}
}