<?php

/**
 * Suco_Spider 爬虫类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Spider
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Spider
{
	/**
	 * 获取的内容
	 *
	 * @var string
	 */
	protected $_content;

	/**
	 * 获取的COOKIEJAR
	 *
	 * @var string
	 */
	protected $_cookiejar;

	/**
	 * 正则过滤转换
	 *
	 * @var string
	 */
	protected $_filter = array(
		0 => array('#', '.', ':', '(', ')', "'", '"', '?', '*', '&', '/', '{all}', '{data}', '{block}', '{space}', '{numeric}', '{cr}'),
		1 => array('\#', '\.', '\:', '\(', '\)', "\'", '\"', '\?', '\*', '\&', '\/', '.*', '(.*)', '(.*?)', '\s+?', '(\d+?)', '\r\n'),
	);

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		if (!function_exists('curl_init')) {
			throw new Suco_Spider_Exception('CURL has been disabled');
		}
	}

	/**
	 * 模拟登陆并获取COOKIEJAR
	 *
	 * @param string $url 登陆地址
	 * @param string $params 登陆参数
	 * @param string $cookiejar	COOKIEJAR保存路径
	 *
	 * @return string
	 */
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

	/**
	 * 链接目标URL并获取内容
	 *
	 * @param string $url
	 */
	public function connect($url, $params = array(), $charset = null, $userAgent = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
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
			throw new Suco_Spider_Exception('Unable to get content');
		}
		if ($charset) {
			$this->_content = mb_convert_encoding($this->_content, 'utf-8', $charset);
		}
		return $this->_content;
	}

	/**
	 * 解析内容列表
	 *
	 * @param string $rule
	 *
	 * @return array
	 */
	public function matchList($rule, $content = null)
	{
		if (!$rule) {
			throw new Suco_Spider_Exception('请设置规则');
		}
		if (!$content) {
			$content = $this->getContent();
		}

		$rule = '#'.$this->_encodeRegEx($rule).'#Ui';
		preg_match_all($rule, $content, $s);
		$s = array_unique($s[0]);

		//重建序列
		foreach ($s as $i) {
			$c[] = $i;
		}
		return $c;
	}

	/**
	 * 解析一组内容块
	 *
	 * @param string $rule
	 *
	 * @return array
	 */
	public function matchBlock($rule, $content = null)
	{
		if (!$rule) {
			throw new Suco_Spider_Exception('请设置规则');
		}
		if (!$content) {
			$content = $this->getContent();
		}

		$rule = '#'.$this->_encodeRegEx($rule).'#Uis';
		preg_match_all($rule, $content, $s, PREG_SET_ORDER);
		for ($i = 0; $i < count($s); $i++) {
			array_shift($s[$i]);
		}
		return $s;
	}

	/**
	 * 解析内容
	 *
	 * @param string $rule
	 *
	 * @return array
	 */
	public function match($rule, $content = null)
	{
		if (!$rule) {
			throw new Suco_Spider_Exception('请设置规则');
		}
		if (!$content) {
			$content = $this->getContent();
		}

		$rule = '#'.$this->_encodeRegEx($rule).'#isU';
		preg_match($rule, $content, $s);
		return isset($s[1]) ? trim($s[1]) : null;
	}

	/**
	 * 正则编码
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected function _encodeRegEx($string)
	{
		return str_replace($this->_filter[0], $this->_filter[1], $string);
	}

	/**
	 * 正则解码
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected function _decodeRegEx($string)
	{
		return str_replace($this->_filter[1], $this->_filter[0], $string);
	}

	/**
	 * 返回获取内容
	 *
	 * @return string
	 */
	public function getContent()
	{
		return $this->_content;
	}

	/**
	 * 打印获取内容
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getContent();
	}
}