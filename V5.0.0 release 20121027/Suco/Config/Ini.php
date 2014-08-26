<?php

/**
 * Suco_Config_Ini Ini 配置文件解析类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Config
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Config_Ini extends Suco_Object implements Suco_Config_Interface
{
	/**
	 * 配置文件路径
	 * @var string
	 */
	protected $_file;
	/**
	 * 键值分割符
	 * @var string
	 */
	protected $_extendSeparator = ':';
	/**
	 * 数组分割符
	 * @var string
	 */
	protected $_arraySeparator = '.';

	/**
	 * 处理键名
	 *
	 * @param array $config
	 * @param string $key
	 * @param mixed $val
	 * @return array
	 */
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

	/**
	 * 解析配置文件
	 * @param string $file
	 * @return array
	 */
	protected function _parse($file)
	{
		$iniArray = parse_ini_file($file, true);

		$data = array(); $arr = array();
		foreach ($iniArray as $key => $value) {
			if (!is_array($value)) {
				$data[$key] = $value;
				continue;
			}

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
					$data['_extends'][$keys[1]] = array_merge($data[$keys[0]], $iniArray[$key]);
					break;
			}
		}
		return $data;
	}

	/**
	 * 格式化配置文本
	 * @param array $array
	 * @return void
	 */
	protected function _formatText($array)
	{
		$string = '';
		foreach ((array)$array as $key => $row) {
			if (!is_array($row)) {
				$string .= "{$key} = {$row}\r\n";
				unset($array[$key]);
			}
		}

		//将数组置后
		foreach ((array)$array as $key => $row) {
			$string .= "[{$key}]\r\n";
			$string .= $this->_formatText($row) . "\r\n";
		}
		return $string;
	}

	/**
	 * 导入配置数组
	 *
	 * @param array $array
	 * @return void
	 */
	public function import($array)
	{
		foreach ($array as $key => $val) {
			$this->set($key, $val);
		}
	}

	/**
	 * 清空所以配置
	 *
	 * @return void
	 */
	public function clean()
	{
		$this->_data = array();
	}

	/**
	 * 保存配置到指定文件
	 * @param string $file	为空以当前文件名保存
	 * @return void
	 */
	public function save($file = null)
	{
		$file = $file ? $file : $this->_file;
		file_put_contents($file, $this->_formatText($this->toArray()));
	}

	/**
	 * 载入并解析配置文件
	 * @param string $file	为空以当前文件名保存
	 * @return void
	 */
	public function load($file)
	{
		if (!file_exists($file)) {
			require_once 'Suco/Config/Exception.php';
			throw new Suco_Config_Exception("找不到配置文件 {$file}");
		}

		$this->_file = realpath($file);
		$data = $this->_parse($file);
		$this->import($data);

		return $this;
	}

}