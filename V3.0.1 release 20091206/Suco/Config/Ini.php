<?php 

require_once 'Suco/Config.php';
class Suco_Config_Ini extends Suco_Config
{
	protected $_file;
	protected $_data;
	protected $_extendSeparator = ':';
	protected $_arraySeparator = '.';
	
	public function __construct($file)
	{
		if (!file_exists($file)) {
			require_once 'Suco/Config/Exception.php';
			throw new Suco_Config_Exception("找不到配置文件 {$file}");
		}
		
		$this->_file = realpath($file);
		$data = $this->_parse();
		
		parent::__construct($data);
	}
	
	protected function _generate($array)
	{
		return null;
	}
	
	protected function _processKey($config, $key, $val)
	{
		if (strpos($key, $this->_arraySeparator) !== false) {
			$pieces = explode($this->_arraySeparator, $key, 2);
			if (!isset($config[$pieces[0]])) {
				if ($pieces[0] === '0' && !empty($config)) {
					$config = array($pieces[0] => $config);
				} else {
					$config[$pieces[0]] = array();
				}
			}
			$config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $val);
		} else {
			$config[$key] = $val;
		}
		return $config;
	}
	
	protected function _parse()
	{
		$iniArray = parse_ini_file($this->_file, true);
		
		$data = array(); $arr = array();
		foreach ($iniArray as $key => $value) {
			$arr = array();
			foreach ($value as $k => $v) {
				$arr = array_merge_recursive($arr, $this->_processKey(array(), $k, $v));
			}
			
			$iniArray[$key] = $value = $arr ? $arr : $value;
			$keys = explode($this->_extendSeparator, $key);	
			switch (count($keys)) {
				case 1:
					$data[$keys[0]] = $value;
					break;
				case 2:
					$data['_extends'][$keys[1]] = $this->merge($data[$keys[0]], $iniArray[$key]);
					break;
			}
		}
		return $data;
	}
	
	public function merge($arr1, $arr2)
	{
		$data = array();
		foreach ($arr1 as $key => $val) {
			if (isset($arr2[$key])) {
				if (is_array($val)) {
					$data[$key] = $arr2[$key];
				}
			}
		}
		return $data;
	}
}