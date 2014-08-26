<?php
/**
 * Suco_File_Folder 类, 目录操作封装
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		File
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_File_Folder extends Suco_File
{
	/**
	 * 读取目录
	 *
	 * @param  string	$dir	目录路径
	 * @param  string	$mode	读取模式	'file' 只读取目录中的文件, 'dir' 只读取目录中的子目录
	 * @return  array
	 */
	public static function read($dir, $mode = null)
	{
		$folders = array();
		$dir = realpath($dir) . '\\';
		if (is_dir($dir) && $dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == '.' || $file == '..') { continue; }
				if ($mode == 'file' && filetype($dir . $file) != 'file') {
					continue;
				} elseif ($mode == 'dir' && filetype($dir . $file) != 'dir') {
					continue;
				}

				$folders[] = array(
					'name' => $file,
					'path' => $dir,
					'type' => filetype($dir . '\\' . $file),
					'readable' => is_readable($dir . '\\' . $file),
					'size' => filesize($dir . '\\' . $file),
					'time' => filemtime($dir . '\\' . $file),
				);
			}
			closedir($dh);
			return $folders;
		}
		return false;
	}

	/**
	 * 复制目录
	 *
	 * @param  string	$source	源路径
	 * @param  string	$dest	目标路径
	 * @return  null
	 */
	public static function copy($source, $dest)
	{
		static $files;
		$source = realpath($source . '\\');
		$dest = $dest . '\\';

		if (!is_dir($dest)) {
			self::mk($dest);
		}

		if (is_dir($source)) {
			$dirs = self::read($source);

			foreach ((array)$dirs as $var) {
				switch ($var['type']) {
					case 'dir':
						$var['name'] .= '\\';
						self::copy($var['path'] . $var['name'] . '\\', $dest . $var['name']);
						continue;
					case 'file':
						parent::copy($var['path'] . $var['name'], $dest . $var['name']);
						continue;
				}
			}
			return true;
		}
	}

	/**
	 * 移动目录
	 *
	 * @param  string	$source	源路径
	 * @param  string	$dest	目标路径
	 * @return  null
	 */
	public static function move($source, $dest)
	{
		self::copy($source, $dest);
		self::rm($source);
	}

	/**
	 * rm 方法别名
	 */
	public static function delete($dir)
	{
		self::rm($dir);
	}

	/**
	 * mk 方法别名
	 */
	public static function create($dir, $mode = 0777)
	{
		self::mk($dir, $mode);
	}

	/**
	 * 清空目录
	 *
	 * @param  string	$dir	路径
	 * @return  null
	 */
	public static function clear($dir)
	{
		self::rm($dir);
		self::mk($dir, 0777);
	}

	/**
	 * 创建目录
	 *
	 * @param  string	$dir	目录路径
	 * @param  int	$mode		权限
	 * @return  bool
	 */
	public static  function mk($folder, $mode = 0777)
	{
	    $reval = false;
	    if (!file_exists($folder)) {
	        @umask(0);
	        preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);
	        $base = ($atmp[0][0] == '/') ? '/' : '';
	        foreach ($atmp[1] AS $val) {
	            if ('' != $val) {
	                $base .= $val;
	                if ('..' == $val || '.' == $val) {
	                    $base .= '/';
	                    continue;
	                }
	            }
	            else {
	                continue;
	            }
	            $base .= '/';
	            if (!file_exists($base)) {
	                if (@mkdir(rtrim($base, '/'), $mode)) {
	                    @chmod($base, $mode);
	                    $reval = true;
	                }
	            }
	        }
	    }
	    else {
	        $reval = is_dir($folder);
	    }
	    clearstatcache();
	    return $reval;
	}

	/**
	 * 删除目录
	 *
	 * @param  string	$dir	目录路径
	 * @return  null
	 */
	public static function rm($dir)
	{
		if (is_dir($dir)) {
			$dirs = self::read($dir);
			foreach ((array)$dirs as $val) {
				switch ($val['type']) {
					case 'dir':
						self::rm($val['path'] . '\\' . $val['name'] . '\\');
						continue;
					case 'file':
						parent::delete($val['path'] . '\\' . $val['name']);
						continue;
				}
			}
			rmdir($dir);
			return true;
		}
	}
}