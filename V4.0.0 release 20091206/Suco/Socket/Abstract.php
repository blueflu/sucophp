<?php

class Suco_Socket_Abstract
{
	protected $_handle;
	protected $_response = array();
	
	public function open($host, $port, $timeout = 10)
	{
		$this->_handle = @fsockopen($host, $port, $errno, $error, $timeout);
		if (!$this->_handle) {
			require_once 'Suco/Socket/Exception.php';
			throw new Suco_Socket_Exception($errno . $error);
		}
	}
	
	public function execute($cmd)
	{
		if (!fputs($this->_handle, $cmd."\r\n")) {
			require_once 'Suco/Socket/Exception.php';
			throw new Suco_Socket_Exception($cmd);
		}
		$this->_response[] = $cmd;
		$this->_response[] = fgets($this->_handle, 512) . "\r\n";
	}
	
	public function close()
	{
		fclose($this->_handle);
	}
	
	public function getResponse()
	{
		return $this->_response;
	}
}