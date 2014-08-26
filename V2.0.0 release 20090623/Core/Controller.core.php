<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * 主控制器
 * 框架中所有控制器必须继承此类
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */

abstract class SCN_Controller extends SCN_Base
{
	public $uri;
	public $curUser, $curManager;
	
	public function __construct()
	{
		global $URI;
		parent::__construct();
		$this->uri = $URI;
		$this->curUser = &$_SESSION[APP_KEY]['CUR_USER'];
		$this->curManager = &$_SESSION[APP_KEY]['CUR_MANAGER'];
	}
	
	public function output()
	{
		//输出调试信息
		if (DEBUG) {
			SCN_Output::append('<div style="margin-top:40px;">');
			SCN_Output::append($this->db ? $this->db->debug() : '');
			SCN_Output::append('</div>');
		}
		SCN_Output::display();
	}
}
?>