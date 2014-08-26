<?php

class Suco_Db
{
	protected static $_dbo;
	
	public static function factory($dsn, $identify = 'default')
	{
		if (is_string($dsn)) {
			$dsn = self::parseDsn($dsn);
		} elseif ($dsn instanceof Suco_Config) {
			$dsn = $dsn->toArray();
		}
		
		if (!isset(self::$_dbo[$identify])) {
			$class = 'Suco_Db_Adapter_' . ucfirst($dsn['adapter']);
			require_once 'Suco/Db/Adapter/' . ucfirst($dsn['adapter']) . '.php';
			
			$db = new $class();
			$db->connect($dsn['host'], @$dsn['port'], $dsn['user'], $dsn['pass']);
			if (isset($dsn['name'])) {
				$db->selectdb($dsn['name']);
			}
			if (isset($dsn['charset'])) {
				$db->setCharset($dsn['charset']);
			}
			self::$_dbo[$identify] = $db;
		}
		return self::$_dbo[$identify];
	}
	
	public static function parseDsn($dsn)
	{
		$info = parse_url($dsn);
		return array(
			'adapter' => $info['scheme'],
			'user' => isset($info['user']) ? $info['user'] : null,
			'pass' => isset($info['pass']) ? $info['pass'] : null,
			'host' => isset($info['host']) ? $info['host'] : null,
			'port' => isset($info['port']) ? $info['port'] : null,
			'name' => isset($info['path']) ? str_replace(array('/', '\\'), '', $info['path']) : null,
		);
	}
	
	public static function dump()
	{
		$string = '';
		foreach ((array)self::$_dbo as $adapter) {
			$string .= $adapter->dump();	
		}
		return $string;
	}
}