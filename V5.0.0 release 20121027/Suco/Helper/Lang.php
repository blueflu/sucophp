<?php
/**
 * Suco_Helper_Lang 加载语言包
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Lang implements Suco_Helper_Interface
{
	public static function callback($args)
	{
		return Suco_Application::instance()
			->getLocale()
			->tranlate($args[0]);
	}
}