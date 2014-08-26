<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * 数据工厂
 * 通过 SCN_Db_Factory::connect(mysql://user:pass@localhost/database)
 * 来装载不同的数据库驱动及方法
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */

class SCN_Db_Factory
{
	function connect($dsn)
	{
		if (!is_array($dsn)) $dsn = self::parseDsn($dsn);
		require_once 'Drivers/' . ucfirst($dsn['engine']) . '/Driver.php';
		return new SCN_Db_Driver($dsn);
	}

	function parseDsn($params)
	{
		$info = parse_url($params);
		$dsn = array(
			'engine' => $info['scheme'],
			'user' => $info['user'],
			'pass' => $info['pass'],
			'host' => $info['host'],
			'port' => $info['port'],
			'name' => str_replace(array('/', '\\'), '', $info['path']),
		);

		return $dsn;
	}
}

?>