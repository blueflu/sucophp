<?php 

class Suco_Cookie
{
	public function set($key, $val, $expiry = 3600)
	{
		$domain = Suco_Controller_Request_Http::getHost();
		$c = explode('.', $domain);
		if (count($c) >= 3) {
			unset($c[0]);
			$domain = implode('.', $c);	
		}
		setcookie($key, $val, time()+$expiry, '/', '.'.$domain);
	}
	
	public function get($key)
	{
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : false;
	}
}