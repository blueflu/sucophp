<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * 输出控制
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */

class SCN_Output
{

	var $buf;
	
	function &getInstance() {
		static $instance = array();
		if (!isset($instance[0]) || !$instance[0]) {
			$instance[0] =& new SCN_Output();
		}
		return $instance[0];
	}
	
	function _start()
	{
		ob_start();
	}
	
	function _end()
	{
		$self = &SCN_Output::getInstance();
		$self->buf .= ob_get_contents();
		ob_end_clean();
		ob_end_flush();
		if (ord(substr($self->buf,0,1)) == 239) {
			$self->buf = substr($self->buf, 3);
		}
	}
		
	function append($str)
	{
		$self = &SCN_Output::getInstance();
		$self->buf .= $str;
	}
	
	function clear()
	{
		ob_end_clean();
		ob_end_flush();
		$self = &SCN_Output::getInstance();
		$self->buf = null;
	}
		
	function replace($s1, $s2)
	{
		$self = &SCN_Output::getInstance();
		$self->buf = preg_replace($s1, $s2, $self->buf);
	}
	
	function display()
	{
		$self = &SCN_Output::getInstance();	
		echo $self->buf;
	}
	
	function getBuf()
	{
		$self = &SCN_Output::getInstance();	
		return $self->buf;
	}

}
?>