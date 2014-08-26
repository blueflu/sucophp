<?php

/**
 * Suco_Config_Json Json 配置文件解析类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Config
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Config_Json extends Suco_Object implements Suco_Config_Interface
{
	/**
	 * 配置文件路径
	 * @var string
	 */
	protected $_file;

	/**
	 * 导入配置数组
	 *
	 * @param array $array
	 * @return void
	 */
	public function import($array)
	{
		foreach ((array)$array as $key => $val) {
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
		file_put_contents($file, $this->_formatText(json_encode($this->toArray())));
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
		$data = file_get_contents($this->_file);
		$data = json_decode($data, 1);
		$this->import($data);
		return $this;
	}

	protected function _formatText($json) {

		$result = '';
		$pos = 0;
		$strLen = strlen($json);
		$indentStr = '	';
		$newLine = "\n";
		$prevChar = '';
		$outOfQuotes = true;

		for ($i=0; $i<=$strLen; $i++) {
			// Grab the next character in the string.
			$char = substr($json, $i, 1);
			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;
				// If this character is the end of an element,
				// output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}
			// Add the character to the result string.
			$result .= $char;
			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}
				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}
			$prevChar = $char;
		}
		return $result;
	}

}