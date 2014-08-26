<?php

class Suco_Gather
{
	protected $_content;
	protected $_filter = array(
		0 => array('.', ':', '(', ')', "'", '"', '?', '*', '&', '/', '{all}', '{data}', '{block}', '{space}', '{numeric}'),
		1 => array('\.', '\:', '\(', '\)', "\'", '\"', '\?', '\*', '\&', '\/', '.*', '(.*)', '(.*)', '\s+', '(\d+)'),
	);
	
	public function __construct()
	{
		if (!function_exists('curl_init')) {
			throw new Suco_Exception('CURL has been disabled');
		}
	}
	
	public function connect($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, urldecode(trim($url)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		$this->_content = curl_exec($ch);
		curl_close($ch);
		if (!$this->_content) {
			throw new Suco_Exception('Unable to get content');
		}
	}
	
	public function parseMatch($rule)
	{
		if (!$rule) {
			throw new Suco_Exception('请设置规则');
		}
		
		$rule = '#'.$this->_encodeRegEx($rule).'#Ui';
		preg_match_all($rule, $this->getContent(), $s);
		$s = array_unique($s[0]);
		return $s;
	}

	public function parseBlock($rule)
	{
		if (!$rule) {
			throw new Suco_Exception('请设置规则');
		}
		
		$rule = '#'.$this->_encodeRegEx($rule).'#Ui';
		preg_match_all($rule, $this->getContent(), $s, PREG_SET_ORDER);
		for ($i = 0; $i < count($s); $i++) {
			array_shift($s[$i]);
		}
		return $s;
	}
	
	public function parse($rule)
	{
		if (!$rule) {
			throw new Suco_Exception('请设置规则');
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