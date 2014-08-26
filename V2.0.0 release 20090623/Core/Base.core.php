<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * SCN_Base 公共类
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */
 
class SCN_Base extends SCN_Loader
{
	public function __construct()
	{
		$this->load = &$this;

		global $self;
		$self = $this;
	}
	
	public static function &getKernel()
	{
		global $kernel, $self;
		if (is_object($kernel)) {
			return $kernel;
		}
		return $self->load;
	}
}

?>