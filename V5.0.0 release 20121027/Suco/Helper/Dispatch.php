<?php
/**
 * Suco_Helper_Dispatch 分发器
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Dispatch implements Suco_Helper_Interface
{
	public static function callback($args)
	{
		return self::dispatch($args[0], $args[1], $args[2], $args[3]);
	}

	public static function dispatch($controller, $action = null, $module = null, $params = null)
	{
		try {
			$dispatcher = Suco_Application::instance()->getDispatcher();
			$dispatcher->dispatch($controller, $action, $module, $params);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}