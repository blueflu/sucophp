<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * 主模型
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */
 
abstract class SCN_Model extends SCN_Base
{
	
	function __construct()
	{
		$this->initModel();	
	}

	/*
	 * 初始化 
	 * @params object $obj
	 */
	
	function &initModel()
	{
		$kernel = &SCN_Base::getKernel();

		foreach (array_keys(get_object_vars($kernel)) as $key) {
			if (!isset($this->$key) 
				&& is_object($kernel->$key) 
				&& $key != str_replace(strtolower(MOD_SUFFIX), '', get_class($this)) 
				&& $key != 'tpl'
				) {
				$this->$key = &$kernel->$key;
			}
		}

	}
	
}
?>