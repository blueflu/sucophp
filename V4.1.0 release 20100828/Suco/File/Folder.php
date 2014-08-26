<?php

class Suco_File_Folder extends Suco_File
{
	/**
	 * 读取目录
	 * @params  string	$dir	目录路径
	 * @params  string	$mode	读取模式	'file' 只读取目录中的文件, 'dir' 只读取目录中的子目录
	 * @return  array
	 */
	public function read($dir, $mode = null)
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
	 * @params  string	$source	源路径
	 * @params  string	$dest	目标路径
	 * @return  null
	 */
	public function copy($source, $dest)
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
	 * @params  string	$source	源路径
	 * @params  string	$dest	目标路径
	 * @return  null
	 */
	public function move($source, $dest)
	{
		self::copy($source, $dest);
		self::rm($source);
	}
	
	public function delete($dir)
	{
		self::rm($dir);	
	}
	
	public function create($dir, $mode = 0777)
	{
		self::mk($dir, $mode);
	}
	
	public function clear($dir)
	{
		self::rm($dir);	
		self::mk($dir, 0777);
	}
	
	/**
	 * 创建目录
	 * @params  string	$dir	目录路径
	 * @return  null
	 */
	public function mk($dir, $mode = 0777)
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
	public function rm($dir)
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