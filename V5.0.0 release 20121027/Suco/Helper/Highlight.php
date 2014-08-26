<?php
/**
 * Suco_Helper_Highlight 字符高亮
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Highlight implements Suco_Helper_Interface
{
	protected $_str;
	protected $_keyword;

	public static function callback($args)
	{
		return new self($args[0], $args[1]);
	}

	public function __construct($str, $keyword)
	{
		$this->_str = $str;
		$this->_keyword = $keyword;
	}

	public function __toString()
	{
		$str = $this->_str;
		if (!empty($this->_keyword)) {
			$keywords = explode(" ", $this->_keyword);
			foreach ($keywords as $keyword) {
				$str = preg_replace('#('.$keyword.')#i', '<font color="red">$1</font>', $str);
			}
		}
		return $str;
	}
}