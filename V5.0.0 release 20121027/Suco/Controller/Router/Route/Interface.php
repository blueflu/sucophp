<?php
/**
 * Suco_Controller_Router_Interface 路由规则类接口
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

Interface Suco_Controller_Router_Route_Interface
{
	public function match($request);
	public function reverseMatch($options);
}