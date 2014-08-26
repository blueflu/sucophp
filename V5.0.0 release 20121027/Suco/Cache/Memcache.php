<?php

/**
 * Suco_Cache_Memcache, Memcache 缓存封装
 * 完全继承原始 Memcache 类的所以特性
 *
 * @version		3.0 2009/09/01
 * @license		http://www.suconet.com/license
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Cache
 *
 * <b>Memcache 应用实例:</b>
 * <code>
 *	$memcache = new Suco_Cache_Memcache();
 *	$memcache->setServer(127.0.0.1);
 *	$memcache->setPort(11211)
 *	if (!$data = $memcache->load('block_id')) {
 *		$memcache->save('test content...', 3600);
 *	}
 *	echo $data;
 *	//test content
 * </code>
 *
 */

final class Suco_Cache_Memcache extends Memcache implements Suco_Cache_Interface
{
	protected static $_host = '127.0.0.1';
	protected static $_port = '11211';
	protected static $_persistent = false;
	protected static $_timeout = 1;

	protected static $_currentId = 'd1';

	/**
	 * 构造函数
	 * 连接Memcache服务器
	 */
	public function __construct()
	{
		if (self::$_persistent) {
			parent::pconnect(self::$_host, self::$_port, self::$_timeout);
		} else {
			parent::connect(self::$_host, self::$_port, self::$_timeout);
		}
	}

	/**
	 * 析构函数
	 * 关闭Memcache连接
	 */
	public function __destruct()
	{
		parent::close();
	}

	/**
	 * 单件实例
	 */
	public static function instance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * 设置是否常连接
	 * @param bool $flag
	 */
	public static function setPconnect($flag)
	{
		self::$_persistent = $flag;
	}

	/**
	 * 设置连接超时时间
	 * @param int $timeout
	 */
	public static function setConnectTimeout($timeout)
	{
		self::$_timeout = $timeout;
	}

	/**
	 * 设置服务器地址
	 * @param string $host
	 */
	public static function setServer($host)
	{
		self::$_host = $host;
	}

	/**
	 * 设置端口地址
	 * @param string $host
	 */
	public static function setPort($port)
	{
		self::$_port = $port;
	}

	/**
	 * 载入缓存块
	 *
	 * @param string|int $id
	 *
	 * @return mixed
	 */
	public static function load($id)
	{
		$memcache = self::instance();
		$memcache->_currentId = $id;
		return $memcache->get($id);
	}

	/**
	 * 保存缓存
	 *
	 * @param mixed $data 数据
	 * @param mixed $exp 有效期
	 * @param mixed $id 块ID
	 *
	 * @return mixed
	 */
	public static function save($data, $exp = 0, $id = null)
	{
		$memcache = self::instance();
		if (!$id) {
			$id = $memcache->_currentId;
		}
		return $memcache->set($id, $data, 0, $exp);
	}
}