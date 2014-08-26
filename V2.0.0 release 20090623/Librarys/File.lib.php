<?php
/*
 * SCN_Timer #2008/6/12 16:20:06#
 * 文件操作类
 *
 * @version				1.5
 * @author				blueflu (lqhuanle@163.com)
 * @copyright			Copyright (c) 2008, Suconet, Inc.
 * @license				http://www.suconet.com/license
 * -----------------------------------------------------------
 * @example:
	SCN_File::append('./test.txt', 'testing...') or die('写入失败');
	echo 'Content: ' . SCN_File::read('./test.txt');
	SCN_File::copy('./test.txt', './ts/file1.txt');
	SCN_File::move('./test.txt', './ts/file2.txt');
	SCN_File::delete('test.txt');
	
	//文件上传实例
	<form enctype="multipart/form-data" method="post">
		<input type="file" name="img" />
		<input type="submit" />
	</form>
	echo SCN_File::upload($_FILES['img'], './', array('swf'));
 */

if (!defined('DS')) define(DS, DIRECTORY_SEPARATOR);


class SCN_File
{

	/**
	 * 返回一个单件实例
	 * @return  object
	 */
	function &getInstance() {
		static $instance = array();
		if (!isset($instance[0]) || !$instance[0]) {
			$instance[0] =& new SCN_File();
		}
		return $instance[0];
	}
	
	/**
	 * 批量上传
	 * @params  string $file 文件
	 * @params  string $dest 目标路径
	 * @params  string $allowExt 允许格式
	 * @params  string $maxSize 允许容量
	 * @return  string
	 */
	function multiUpload($files, $dest = './upload/', $allowExt = array('jpg', 'gif', 'bmp', 'png', 'swf', 'flv'), $maxSize = null)
	{
		$length = count($files['name']);
		for ($i=0; $i<=$length; $i++) {
			if ($files['error'][$i] === 0) {
				$nf['name'] = $files['name'][$i];
				$nf['type'] = $files['type'][$i];
				$nf['tmp_name'] = $files['tmp_name'][$i];
				$nf['error'] = $files['error'][$i];
				$nf['size'] = $files['size'][$i];
				print_r($nf);
				$fileUrl[] = $this->upload($nf, $dest, $allowExt, $maxSize);
			}
		}
		return $fileUrl;
	}
	
	/**
	 * 上传文件
	 * @params  string $file 文件
	 * @params  string $dest 目标路径
	 * @params  string $allowExt 允许格式
	 * @params  string $maxSize 允许容量
	 * @return  string
	 */
	function upload($file, $dest = './upload/', $allowExt = array('jpg', 'gif', 'bmp', 'png', 'swf', 'flv'), $maxSize = null)
	{
		$self = &SCN_File::getInstance();
		
		//默认限制2M
		$maxSize || $maxSize = 2048000;
		if ($file['size'] > $maxSize) {
			die ('The file size is out of range' . $maxSize);
		}
		
		//禁止上传格式
		$denyExt = array('php', 'asp', 'jsp', 'aspx', 'html', 'js', 'css');
		$ext = pathinfo($file['name']);
		$ext = strtolower($ext['extension']);

		if (!in_array($ext, $allowExt) || in_array($ext, $denyExt)) {
			die ('The file format is illegal, the system is only allow the this '.implode(',', $allowExt).' format');
		}
		
		$dest = $dest . date('Ymd') . '/';
		$fileName = md5(microtime()) . '.' . $ext;
		
		if (!is_dir($dest)) mkdir($dest, 0777); 
		
		if (!$self->copy($file['tmp_name'], $dest . $fileName)) {
			die ('The file upload fail');
		}
		
		return $dest . $fileName;
	}

	/**
	 * 写文件
	 * @params  string $file 文件名
	 * @params  string $content 文件内容
	 * @params  string $mode 写模式 a追加, w写入
	 * @return  bool
	 */
	function write($file, $content, $mode = 'w')
	{
		if (is_writable(dirname($file))) {
			$handle = fopen($file, $mode);
			flock($handle, LOCK_EX);
			fwrite($handle, $content);
			flock($handle, LOCK_UN);
			fclose($handle);
			return true;
		}
		return false;
	}

	/**
	 * 追加文件内容
	 * @params  string $file 文件名
	 * @params  string $content 文件内容
	 * @return  bool
	 */

	function append($file, $content)
	{
		$self = &SCN_File::getInstance();
		return $self->write($file, $content, 'a');
	}

	/**
	 * 读文件
	 * @params  string $file 文件名
	 * @return  string
	 */
	
	function read($file, $mode = 'r')
	{
		if (is_readable($file)) {
			$handle = fopen($file, $mode);
			flock($handle, LOCK_EX);
			$content = fread($handle, filesize($file));
			flock($handle, LOCK_UN);
			fclose($handle);
			return $content;
		}
	}

	/**
	 * 删除文件
	 * @params  string $file 文件名
	 * @return  bool
	 */
	
	function delete($file)
	{
		if (is_file($file)) {
			unlink($file);
			return true;
		}
		return false;
	}

	/**
	 * 复制文件
	 * @params  string $source 源文件
	 * @params  string $dest 目标文件
	 * @return  bool
	 */
	
	function copy($source, $dest)
	{
		if (is_file($source)) {
			copy($source, $dest);
			return true;
		}
		return false;
	}


	/**
	 * 移动文件
	 * @params  string $source 源文件
	 * @params  string $dest 目标文件
	 * @return  null
	 */
	
	function move($source, $dest)
	{
		$self = &SCN_File::getInstance();
		$self->copy($source, $dest);
		$self->delete($source);
	}
	
}

?>