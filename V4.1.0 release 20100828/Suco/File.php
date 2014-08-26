<?php 

class Suco_File
{
	/**
	 * 批量上传
	 * @params  string $file 文件
	 * @params  string $dest 目标路径
	 * @params  string $allowExt 允许格式
	 * @params  string $maxSize 允许容量
	 * @return  string
	 */
	function multiUpload($files, $dest = 'uploads/', $allowTypes = array(), $maxSize = 2048000)
	{
		$urls = array();
		$length = count($files['name']);
		for ($i=0; $i<$length; $i++) {
			if ($files['error'][$i] === 0) {
				$urls[] = self::upload(array(
					'name' => $files['name'][$i],
					'type' => $files['type'][$i],
					'tmp_name' => $files['tmp_name'][$i],
					'error' => $files['error'][$i],
					'size' => $files['size'][$i]
				), $dest, $allowTypes, $maxSize);
			}
		}
		return $urls;
	}
	
	/**
	 * 上传文件
	 * 
	 * @param string $file
	 * @param string $dest
	 * @param array $allowTypes
	 * @param int $maxSize
	 * @return string
	 */
	public function upload($file, $dest = 'uploads/', $allowTypes = array(), $maxSize = 2048000)
	{
		if (!$file) { return; }
		
		if ($file['size'] > $maxSize) {
			Suco_Loader::loadClass('Suco_File_Exception');
			throw new Suco_File_Exception("The file size is out of range {$maxSize}");
		}
		
		//禁止上传的格式
		$denyTypes = array('php', 'asp', 'jsp', 'aspx', 'html', 'js', 'css');
		$ext = pathinfo($file['name']);
		$ext = strtolower($ext['extension']);

		if (($allowTypes && !in_array($ext, $allowTypes)) || in_array($ext, $denyTypes)) {
			Suco_Loader::loadClass('Suco_File_Exception');
			throw new Suco_File_Exception('The file format is illegal, the system is only allow the this '.implode(',', $allowTypes).' format');
		}
		
		$dest = $dest . date('Ymd') . '/';
		$fileName = md5(microtime()) . '.' . $ext;
		
		if (!is_dir($dest)) mkdir($dest, 0777); $dest = $dest . date('H') . '/'; if (!is_dir($dest)) mkdir($dest, 0777); 
		
		if (!self::copy($file['tmp_name'], $dest . $fileName)) {
			Suco_Loader::loadClass('Suco_File_Exception');
			throw new Suco_File_Exception('The file upload fail');
		}

		return $dest . $fileName;
	}

	/**
	 * 复制文件
	 * 
	 * @param string $source
	 * @param string $dest
	 * @return bool
	 */
	public function copy($source, $dest)
	{
		if (is_file($source)) {
			copy($source, $dest);
			return true;
		}
		return false;
	}

	/**
	 * 删除文件
	 * 
	 * @param string $file
	 * @return bool
	 */
	public function delete($file)
	{
		if (is_file($file)) {
			unlink($file);
			return true;
		}
		return false;
	}
	
	/**
	 * 移动文件
	 * 
	 * @param string $source
	 * @param string $dest
	 */
	public function move($source, $dest)
	{
		self::copy($source, $dest);
		self::delete($source);
	}
	
	/**
	 * 写文件
	 * 
	 * @param string $file
	 * @param string $content
	 * @param string $mode w新建, a追加
	 * @return bool
	 */
	public function write($file, $content, $mode = 'w')
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
	 * 读文件
	 * 
	 * @param string $file
	 * @param string $mode
	 * @return string
	 */
	public function read($file, $mode = 'r')
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

}