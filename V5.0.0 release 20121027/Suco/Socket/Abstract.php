<?php
/**
 * Suco_Socket_Abstract Socket 操作抽象类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Socket
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Socket_Abstract
{
	/**
	 * Socket 句柄
	 * @var resource
	 */
	protected $_handle;

	/**
	 * 响应集
	 * @var array
	 */
	protected $_responses = array();

	/**
	 * 析构函数
	 *
	 * @return void
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Suco_Socket_Abstract::connect 方法别名
	 */
	public function open($host, $port, $timeout = 10, $protocol = 'tcp')
	{
		$this->connect($host, $port, $timeout, $protocol);
	}

	/**
	 * 连接主机
	 *
	 * @param string $host 主机
	 * @param int $port 端口
	 * @param int $timeout 超时时间
	 * @param string $protocol 协议
	 * @return void
	 */
	public function connect($host, $port, $timeout = 10, $protocol = 'tcp')
	{
		$this->_handle = @fsockopen($protocol.'://'.$host, $port, $errno, $error, $timeout);
		if (!$this->_handle) {
			require_once 'Suco/Socket/Exception.php';
			throw new Suco_Socket_Exception("Unable to connect {$protocol}://{$host}");
		}
	}

	/**
	 * 执行命令并返回主机响应
	 *
	 * @param string $cmd
	 * @return string
	 */
	public function execute($cmd)
	{
		if (!fwrite($this->_handle, $cmd."\r\n")) {
			require_once 'Suco/Socket/Exception.php';
			throw new Suco_Socket_Exception($cmd);
		}
		$response = fgets($this->_handle);
		$code = substr($response, 0, 3);

		$this->_responses[] = $cmd;
		$this->_responses[] = $response;

		if (!empty($code) && $code >= 400) {
			require_once 'Suco/Socket/Exception.php';
			throw new Suco_Socket_Exception($response, $code);
		}

		return $response;
	}

	/**
	 * 关闭Socket连接
	 *
	 * @return void;
	 */
	public function close()
	{
		fclose($this->_handle);
	}

	/**
	 * 返回响应信息
	 *
	 * @return array
	 */
	public function getResponses()
	{
		return $this->_responses;
	}
}