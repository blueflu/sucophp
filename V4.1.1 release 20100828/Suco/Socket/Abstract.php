<?php

class Suco_Socket_Abstract
{
	protected $_handle;
	protected $_response = array();
	
	public function open($host, $port, $timeout = 10, $protocol = 'tcp')
	{
		$this->_handle = @fsockopen($protocol.'://'.$host, $port, $errno, $error, $timeout);
		if (!$this->_handle) {
			require_once 'Suco/Socket/Exception.php';
			throw new Suco_Socket_Exception("Unable to connect {$protocol}://{$host}");
		}
	}
	
	public function execute($cmd)
	{
		if (@!fwrite($this->_handle, $cmd."\r\n")) {
			require_once 'Suco/Socket/Exception.php';
			throw new Suco_Socket_Exception($cmd);
		}
		$response = fgets($this->_handle);
		$code = substr($response, 0, 3);
		
		$this->_response[] = $cmd;
		$this->_response[] = $response;
		
		if (!empty($code) && $code >= 400) {
			require_once 'Suco/Socket/Exception.php';
			throw new Suco_Socket_Exception($response, $code);
		}
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