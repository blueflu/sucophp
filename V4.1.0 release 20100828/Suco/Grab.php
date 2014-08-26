<?php

class Suco_Grab
{
	protected $_content;
	protected $_cookiejar;
	protected $_filter = array(
		0 => array('#', '.', ':', '(', ')', "'", '"', '?', '*', '&', '/', '{all}', '{data}', '{block}', '{space}', '{numeric}', '{cr}'),
		1 => array('\#', '\.', '\:', '\(', '\)', "\'", '\"', '\?', '\*', '\&', '\/', '.*', '(.*)', '(.*)', '\s+?', '(\d+?)', '\r\n'),
	);
	
	public function __construct()
	{
		if (!function_exists('curl_init')) {
			throw new Suco_Grab_Exception('CURL has been disabled');
		}
	}
	
	public function auth($url, $params, $cookiejar = null)
	{
		$this->_cookiejar = $cookiejar ? $cookiejar : './cookiejar.txt';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		return curl_exec($ch);
	}
	
	public function connect($url, $params = array(), $charset = null, $userAgent = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_URL, urldecode(trim($url)));
		if ($userAgent) { //使用代理链接
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		}
		if ($params) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		if ($this->_cookiejar) { //使用Cookie链接
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_cookiejar);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_cookiejar);
			curl_setopt($ch, CURLOPT_COOKIE, 0);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		$this->_content = curl_exec($ch);
		curl_close($ch);
		if (!$this->_content) {
			throw new Suco_Grab_Exception('Unable to get content');
		}
		if ($charset) {
			$this->_content = mb_convert_encoding($this->_content, 'utf-8', $charset);
		}
		return $this->_content;
	}
	
	public function matchAll($rule)
	{
		if (!$rule) {
			throw new Suco_Grab_Exception('请设置规则');
		}
		
		$rule = '#'.$this->_encodeRegEx($rule).'#Ui';
		preg_match_all($rule, $this->getContent(), $s);
		$s = array_unique($s[0]);
		
		//重建序列
		foreach ($s as $i) {
			$c[] = $i;
		}
		return $c;
	}

	public function matchBlock($rule)
	{
		if (!$rule) {
			throw new Suco_Grab_Exception('请设置规则');
		}
		
		$rule = '#'.$this->_encodeRegEx($rule).'#Uis';
		preg_match_all($rule, $this->getContent(), $s, PREG_SET_ORDER);
		for ($i = 0; $i < count($s); $i++) {
			array_shift($s[$i]);
		}
		return $s;
	}
	
	public function match($rule)
	{
		if (!$rule) {
			throw new Suco_Grab_Exception('请设置规则');
		}
		
		$rule = '#'.$this->_encodeRegEx($rule).'#isU';
		preg_match($rule, $this->getContent(), $s);
		return isset($s[1]) ? trim($s[1]) : null;
	}
	
	protected function _encodeRegEx($string)
	{
		return str_replace($this->_filter[0], $this->_filter[1], $string);
	}
	
	protected function _decodeRegEx($string)
	{
		return str_replace($this->_filter[1], $this->_filter[0], $string);
	}
	
	public function getContent()
	{
		return $this->_content;
	}
	
	public function __toString()
	{
		return $this->getContent();
	}
}