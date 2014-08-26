<?php
/**
 * Suco_Controller_Response_Interface 响应类接口
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

interface Suco_Controller_Response_Interface
{
	public function appendBody($content, $name = 'default');
	public function send();
}