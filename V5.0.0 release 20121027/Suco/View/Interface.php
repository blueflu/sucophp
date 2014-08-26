<?php
/**
 * Suco_View_Interface 视图接口
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		View
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

interface Suco_View_Interface
{
	public function assign($index, $value = null);
	public function render($file);
	public function fragmentStart();
	public function fragmentEnd();
	public function partial($file, $data = null);
	public function output($file, $data = null);
}