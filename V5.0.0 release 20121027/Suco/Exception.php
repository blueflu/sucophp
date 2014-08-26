<?php
/**
 * Suco_Exception 框架异常类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @package		Exception
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Exception extends Exception
{
	/**
	 * 打印异常信息
	 * 部署模式下请勿使用此方法
	 *
	 * @return string
	 */
	public function dump()
	{
		return '<div style="font-family:\'Courier New\'; font-size:12px;">'
				. '<h2 style="font-weight:bold; font-size:24px;">['.strtoupper(get_class($this)).']</h2>'
				. '<span style="font-size:18px; font-weight:bold; margin:20px;">'.$this->getMessage().'</span>'
				. '<ul><li>' . str_replace("\n", '</li><li>', $this->getTraceAsString()) . '</li></ul>'
				. '</div>';
	}
}