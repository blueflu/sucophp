<?php

class Suco_Locale
{
	protected $_path;
	protected $_language = 'en_US';
	protected $_packages = array('global');
	protected $_tranlate = array();
	
	public function getInstance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}
	
	public function setup()
	{
		foreach ($this->_packages as $package) {
			$file = trim($this->_path, '/') . '/'
			  	  . trim($this->_language, '/') . '/'
				  . $package . '.lang.php';
			$this->parse($file);
		}
	}
	
	public function parse($file)
	{
		static $cache = array();
		if (!isset($cache[$file])) {
			if (!file_exists($file)) {
				return false;
			}
			$cache[$file] = true;
			$arr = @require_once $file;
			$this->_tranlate = array_merge($this->_tranlate, $arr);
			/*
			$string = file_get_contents($file);
			if (preg_match_all('#\{\@([T|S])\:(.*?)\}\s+\{\@([T|S])\:(.*?)\}#', $string, $arr, PREG_SET_ORDER)) {
				foreach ($arr as $row) {
					if ($row[1] == 'S') {
						$key = md5(trim($row[2]));
						$value = $row[4];
					} elseif ($row[3] == 'S') {
						$key = md5(trim($row[4]));
						$value = $row[2];					
					}
					$this->_tranlate[$key] = $value;
				}
			}*/
		}
	}
	
	public function setLanguage($language)
	{
		$this->_language = $language;
		return $this;
	}
	
	public function getLanguage()
	{
		return $this->_language;
	}
	
	public function setPath($path)
	{
		$this->_path = $path;
		return $this;
	}
	
	public function getPath()
	{
		return $this->_path;
	}
	
	public function addPackage($package)
	{
		if (is_array($package)) {
			$this->_packages = array_merge($this->_packages, $package);
		} else {
			$this->_packages[] = $package;
		}
		return $this;
	}
	
	public function getPackage()
	{
		return $this->_packages;
	}
		
	public function currency($amount)
	{
		return ($amount < 0 ? ' - ' : '').'&yen; ' . sprintf("%01.2f", abs($amount));
	}
	
	public function tranlate($string)
	{
		$this->setup();
		$key = trim($string);
		return isset($this->_tranlate[$key]) ? trim($this->_tranlate[$key]) : $string;
	}
}
function t($key) {
	return Suco_Locale::getInstance()->tranlate($key);
}