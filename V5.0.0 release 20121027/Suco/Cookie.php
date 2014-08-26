<?php

/**
 * Suco_Cookie 类, 对COOKIE操作的封装
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @package		Cookie
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Cookie
{
	/** 写入COOKIE
	 *
	 * @param string $key COOKIE 键名
	 * @param string $val COOKIE 值
	 * @param int $expiry 有限期
	 */
	public function set($key, $val, $expiry = 3600)
	{
		setcookie($key, $val, time()+$expiry, '/');
		$_COOKIE[$key] = $val;
	}

	/**
	 * 读取COOKIE
	 *
	 * @param string $key COOKIE 键名
	 * @return mixed
	 */
	public function get($key)
	{
		$cookie = isset($_COOKIE[$key]) ? $_COOKIE[$key] : false;
		if (ini_get('magic_quotes_gpc')) {
			$cookie = stripslashes($cookie);
		}
		return $cookie;
	}
}