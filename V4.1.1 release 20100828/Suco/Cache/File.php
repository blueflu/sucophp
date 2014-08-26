<?php

class Suco_Cache_File extends Suco_Cache
{
	protected $_cacheDirectory = './appdata/caches/';
	
	public function getInstance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}
	
	public function setCacheDirectory($directory)
	{
		self::getInstance()->_cacheDirectotry = $directory;
	}
	
	public function getCacheDirectory()
	{
		return self::getInstance()->_cacheDirectotry;
	}
	
	public function load($id)
	{
		self::getInstance()->_currentId = $id;
		$file = self::getInstance()->getCacheDirectory() . md5($id) . '.cache';
		if (!$body = Suco_File::read($file)) {
			return false;
		}

		list($header, $content) = explode("\r\n---------content---------\r\n", $body);
		parse_str($header, $header);
		
		if (($header['time'] + $header['exp']) > time() || !$header['exp']) {
			if ($header['type'] == 'array' || $header['type'] == 'object') {
				return unserialize($content);
			}
			return $content;
		}
		return false;
	}
	
	public function save($data, $exp = 0, $id = null)
	{
		if (!$id) { $id = self::getInstance()->_currentId; }
		$file = self::getInstance()->getCacheDirectory() . md5($id) . '.cache';
		
		$type = gettype($data);
		if ($type == 'array' || $type == 'object') {
			$data = serialize($data);
		}
		$header = 'time='.time().'&'.'exp='.$exp.'&type='.$type;
		$data = $header . "\r\n---------content---------\r\n" . $data;
		Suco_File::write($file, $data);
	}
}