<?php
/**
 * Suco_Locale 本地化封装 [未完善]
 * 此类主要用于本地化的语言，数字，货币等信息格式的处理
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Locale
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Locale
{
	protected $_path;
	protected $_language = 'en_US';
	protected $_packages = array('global');
	protected $_tranlate = array();
	protected static $_loaded = array();

	/**
	 * 单件实例
	 *
	 * @return object
	 */
	public static function instance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * 装载语言包
	 *
	 * @return void
	 */
	public function setup()
	{
		foreach ($this->_packages as $package) {
			$file = trim($this->_path, '/') . '/'
			  	  . trim($this->_language, '/') . '/'
				  . $package . '.lang.php';
			$this->parse($file);
		}
	}

	/**
	 * 解析语言包
	 *
	 * @param string $file 语言包文件地址
	 * @return void
	 */
	public function parse($file)
	{
		if (!isset($this->_loaded[$file])) {
			if (!file_exists($file)) {
				return false;
			}
			$this->_loaded[$file] = true;
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

	/**
	 * 设置当前语言
	 *
	 * @param string $language
	 * @return object
	 */
	public function setLanguage($language)
	{
		$this->_language = $language;
		return $this;
	}

	/**
	 * 返回当前语言
	 *
	 * @param string $language
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->_language;
	}

	/**
	 * 设置语言包目录
	 *
	 * @param string $path
	 * @return object
	 */
	public function setPath($path)
	{
		$this->_path = $path;
		return $this;
	}

	/**
	 * 返回语言包目录
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * 返回已载入的语言包
	 *
	 * @return array
	 */
	public function getLoaded()
	{
		return $this->_loaded;
	}

	/**
	 * 添加语言包
	 *
	 * @return string
	 */
	public function addPackage($package)
	{
		if (is_array($package)) {
			$this->_packages = array_merge($this->_packages, $package);
		} else {
			$this->_packages[] = $package;
		}
		return $this;
	}

	/**
	 * 设置语言包集合
	 *
	 * @return string
	 */
	public function setPackages($packages)
	{
		$this->_packages = $packages;
	}

	/**
	 * 返回语言包集合
	 *
	 * @return string
	 */
	public function getPackages()
	{
		return $this->_packages;
	}

	/**
	 * 根据语言包设置翻译字符
	 *
	 * @param string $string
	 * @return string
	 */
	public function tranlate($string)
	{
		$this->setup();
		$key = trim($string);
		return isset($this->_tranlate[$key]) ? trim($this->_tranlate[$key]) : $string;
	}
}