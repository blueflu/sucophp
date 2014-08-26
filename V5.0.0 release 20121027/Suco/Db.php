<?php
/**
 * Suco_Db 数据库工厂类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package 	Db
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Db
{
	/**
	 * 数据库对象集
	 * @var array
	 */
	protected static $_dbo;

	/**
	 * 当前数据库对象
	 * @var string
	 */
	protected static $_current;

	/**
	 * 数据库连接工厂
	 * 应用实例：<code>
	 *
	 * //字符串方式连接
	 * $db1 = Suco_Db::factory('mysql://root:123456@localhost:3306/dbname');
	 *
	 * //数组方式连接
	 * $db2 = Suco_Db::factory(array(
	 * 	'host' => 'localhost',
	 * 	'port' => '3306'
	 *  'user' => 'root',
	 * 	'pass' => '123456',
	 * 	'name' => 'dbname'
	 * ));
	 * </code>
	 *
	 * @param mixed $dsn
	 * @param string $identify
	 */
	public static function factory($dsn, $identify = null)
	{
		if (is_string($dsn)) {
			$dsn = self::parseDsn($dsn);
		} elseif ($dsn instanceof Suco_Config) {
			$dsn = $dsn->toArray();
		}

		if (!$identify) {
			$identify = md5(implode($dsn));
			self::$_current = $identify;
		}
		if (!isset(self::$_dbo[$identify])) {
			$class = 'Suco_Db_Adapter_' . ucfirst($dsn['adapter']);
			require_once 'Suco/Db/Adapter/' . ucfirst($dsn['adapter']) . '.php';
			$dsn['persistent'] = isset($dsn['persistent']) ? (bool)$dsn['persistent'] : false;

			$db = new $class();
			$db->connect($dsn['host'], $dsn['port'], $dsn['user'], $dsn['pass'], $dsn['persistent']);
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

	/**
	 * 返回指定数据库对象
	 *
	 * @param string $identity
	 * @return object
	 */
	public static function getDb($identify = null)
	{
		if (!$identify) {
			$identify = self::$_current;
		}
		return self::$_dbo[$identify];
	}

	/**
	 * 解析dsn连接参数
	 *
	 * @param string $dsn
	 * @return array
	 */
	public static function parseDsn($dsn)
	{
		$info = parse_url($dsn);
		@list($name, $charset) = explode('#', $info['path']);
		return array(
			'adapter' => $info['scheme'],
			'user' => isset($info['user']) ? $info['user'] : null,
			'pass' => isset($info['pass']) ? $info['pass'] : null,
			'host' => isset($info['host']) ? $info['host'] : null,
			'port' => isset($info['port']) ? $info['port'] : null,
			'name' => isset($name) ? str_replace(array('/', '\\'), '', $name) : null,
			'charset' => $charset ? $charset : null
		);
	}

	/**
	 * 打印SQL调试台
	 * 部署模式下请勿使用此方法
	 *
	 * @return string
	 */
	public static function dump()
	{
		$string = '';
		foreach ((array)self::$_dbo as $adapter) {
			$string .= $adapter->dump();
		}
		return $string;
	}
}