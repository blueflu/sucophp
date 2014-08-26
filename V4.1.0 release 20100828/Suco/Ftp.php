<?php

class Suco_Ftp
{
	protected $_handle;
	
	public function __construct($host, $port, $user, $pass)
	{
		$this->_handle = ftp_connect($host, $port);
		echo ftp_login($this->_handle, $user, $pass);
	}
	
	public function upload($sourceFile, $destFile)
	{
		$file = fopen($sourceFile, 'r');
		$dir = ftp_pwd($this->_handle);
		echo $dir;
		ftp_nb_fput($this->_handle, 'haha.jpg', $file, FTP_BINARY);
	}
}