<?php

/**
 * Suco_Cache_File, 文件缓存
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Cache
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 * @example
	$cache = new Suco_Cache_File();
	if (!$data = $cache->load('block_id')) {
		$cache->save('test content...', 3600);
	}
	echo $data;
 */

final class Suco_Cache_File implements Suco_Cache_Interface
{
	/**
	 * 缓存文件保存目录
	 *
	 * @var string
	 */
	protected static $_cacheDirectory = './appdata/caches/';

	/**
	 * 当前缓存块ID
	 *
	 * @var string
	 */
	protected static $_currentId = 'd1';

	/**
	 * 缓存文件后缀
	 *
	 * @var string
	 */
	protected static $_fileSuffix = '.cache';

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
	 * 设置缓存目录
	 *
	 * @param string $directory
	 */
	public static function setCacheDirectory($directory)
	{
		self::instance()->_cacheDirectotry = $directory;
	}

	/**
	 * 返回缓存目录
	 */
	public static function getCacheDirectory()
	{
		return self::instance()->_cacheDirectotry;
	}

	/**
	 * 删除缓存块
	 *
	 * @param string|int $id
	 *
	 * @return bool
	 */
	public static function delete($id)
	{
		$file = self::instance()->getCacheDirectory() . md5($id) . self::$_fileSuffix;
		return Suco_File::delete($file);
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
		self::instance()->_currentId = $id;
		$file = self::instance()->getCacheDirectory() . md5($id) . self::$_fileSuffix;
		if (!$body = Suco_File::read($file)) {
			return false;
		}

		list($header, $content) = explode("\r\n---------content---------\r\n", $body);
		parse_str($header, $header);

		if (($header['time'] + $header['exp']) > time() || !$header['exp']) {
			if ($header['type'] == 'array' || $header['type'] == 'object') {
				return unserialize($content);
			}
			return $content;
		}
		return false;
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
		if (!$id) { $id = self::instance()->_currentId; }
		$file = self::instance()->getCacheDirectory() . md5($id) . '.cache';

		$type = gettype($data);
		if ($type == 'array' || $type == 'object') {
			$data = serialize($data);
		}
		$header = 'time='.time().'&'.'exp='.$exp.'&type='.$type;
		$data = $header . "\r\n---------content---------\r\n" . $data;
		Suco_File::write($file, $data);
	}
}