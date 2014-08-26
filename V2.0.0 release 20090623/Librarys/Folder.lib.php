<?php
/*
 * SCN_Timer #2008/6/12 16:20:06#
 * 目录操作类
 *
 * @version				1.5
 * @author				blueflu (lqhuanle@163.com)
 * @copyright			Copyright (c) 2008, Suconet, Inc.
 * @license				http://www.suconet.com/license
 * -----------------------------------------------------------
 * @example:
	SCN_Folder::mk('./source');
	SCN_Folder::copy('./source', './dest');
	SCN_Folder::move('./source', './move');
	SCN_Folder::rm('./source');
	print_r(SCN_Folder::read('./', FILE));
 */
if (!defined('DS')) define(DS, DIRECTORY_SEPARATOR);
SCN_Loader::import('File.lib', LIBS_DIR);

define('FILE',	'file');
define('DIR',	'dir');

class SCN_Folder extends SCN_File
{

	/**
	 * 返回一个单件实例
	 * @return  object
	 */
	function &getInstance() {
		static $instance = array();
		if (!isset($instance[0]) || !$instance[0]) {
			$instance[0] =& new SCN_Folder();
		}
		return $instance[0];
	}
	
	/**
	 * 读取目录
	 * @params  string	$dir	目录路径
	 * @params  string	$mode	读取模式	FILE 只读取目录中的文件, DIR 只读取目录中的子目录
	 * @return  array
	 */
	function read($dir, $mode = null)
	{
		$dir = realpath($dir) . DS;
		if (is_dir($dir) && $dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == '.' || $file == '..') { continue; }
				if ($mode == FILE && filetype($dir . $file) != FILE) {
					continue;
				} elseif ($mode == DIR && filetype($dir . $file) != DIR) {
					continue;
				}

				$folders[] = array(
					'name' => $file,
					'path' => $dir,
					'type' => filetype($dir . DS . $file),
					'readable' => is_readable($dir . DS . $file),
					'size' => filesize($dir . DS . $file),
					'time' => filemtime($dir . DS . $file),
				);
			}
			closedir($dh);
			return $folders;
		}
		return false;
	}
	
	/**
	 * 复制目录
	 * @params  string	$source	源路径
	 * @params  string	$dest	目标路径
	 * @return  null
	 */
	function copy($source, $dest)
	{
		static $files;
		$self = &SCN_Folder::getInstance();
		$source = realpath($source . DS);
		$dest = $dest . DS;

		if (!is_dir($dest)) {
			$self->mk($dest);
		}

		if (is_dir($source)) {
			$dirs = $self->read($source);

			foreach ((array)$dirs as $var) {
				switch ($var['type']) {
					case DIR:
						$var['name'] .= DS;
						$self->copy($var['path'] . $var['name'] . DS, $dest . $var['name']);
						continue;
					case FILE:
						parent::copy($var['path'] . $var['name'], $dest . $var['name']);
						continue;
				}
			}
			return true;
		}
	}
	
	/**
	 * 移动目录
	 * @params  string	$source	源路径
	 * @params  string	$dest	目标路径
	 * @return  null
	 */
	function move($source, $dest)
	{
		$self = &SCN_Folder::getInstance();
		$self->copy($source, $dest);
		$self->rm($source);
	}
	
	/**
	 * 创建目录
	 * @params  string	$dir	目录路径
	 * @return  null
	 */
	function mk($dir, $mode = 0777)
	{
		if (!is_dir($dir)) {
			mkdir($dir, $mode);
		}
	}
	
	/**
	 * 删除目录
	 * @params  string	$dir	目录路径
	 * @return  null
	 */
	function rm($dir)
	{
		$self = &SCN_Folder::getInstance();
		if (is_dir($dir)) {
			$dirs = $self->read($dir);
			foreach ((array)$dirs as $val) {
				switch ($val['type']) {
					case DIR:
						$self->rm($val['path'] . DS . $val['name'] . DS);
						continue;
					case FILE:
						parent::delete($val['path'] . DS . $val['name']);
						continue;
				}
			}
			rmdir($dir);
			return true;
		}
	}

}
?>