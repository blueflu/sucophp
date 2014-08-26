<?php
/**
 * Suco_Controller_Response_Http HTTP 响应类
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

require_once 'Suco/Controller/Response/Interface.php';

class Suco_Controller_Response_Http implements Suco_Controller_Response_Interface
{
	protected $_body;
	protected $_headers;
	protected $_status = 200;
	protected $_send = false;

	/**
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct()
	{
		ob_start();
	}

	/**
	 * 析构函数
	 *
	 * @return void
	 */
	public function __destruct()
	{
		$this->send();
	}

	/**
	 * 追加内容
	 * @param string $content
	 * @param string $name
	 * @return slef
	 */
	public function appendBody($content, $name = 'default')
	{
		if (isset($this->_body[$name])) {
			$this->_body[$name] .= (string) $content;
		} else {
			$this->_body[$name] = (string) $content;
		}
		return $this;
	}

	/**
	 * 清空内容
	 * 如果提供  name 键值，则清空指定内容。否则清空全部
	 *
	 * @param string $name
	 * @return self
	 */
	public function clearBody($name = null)
	{
		if ($name != null) {
			unset($this->_body[$name]);
		} else {
			$this->_body = array();
		}
		return $this;
	}

	/**
	 * 输出内容
	 */
	public function outputBody()
	{
		$body = @implode('', $this->_body);
		echo $body;
	}

	/**
	 * 设置一个 Http 响应头
	 * @param $name
	 * @param $value
	 */
	public function setHeader($name, $value)
	{
		$this->_headers[$name] = $value;
		return $this;
	}

	/**
	 * 返回一个 Http 响应头
	 * @param $name
	 * @return string
	 */
	public function getHeader($name)
	{
		return isset($this->_headers[$name]) ? $this->_headers[$name] : null;
	}

	/**
	 * 清空所有 Http 响应头
	 */
	public function clearHeaders()
	{
		$this->_headers = array();
	}

	/**
	 * 设置 Content-Type 信息
	 * @param string $type
	 */
	public function setContentType($type)
	{
		$this->setHeader('Content-Type', $type);
		return $this;
	}

	/**
	 * 设置 Http 状态
	 * @param int $code
	 * @param string $message
	 */
	public function setStatus($code, $message = null)
	{
        if (!is_int($code) || (100 > $code) || (599 < $code)) {
            require_once 'Suco/Controller/Response/Exception.php';
            throw new Suco_Controller_Response_Exception('Invalid HTTP response code');
        }
		$this->_status = $code;
		if ($this->_status) {
			header('HTTP/1.0 ' . $this->_status . ' ' . $message);
		}
	}

	/**
	 * 发送所有Http头信息
	 */
	public function sendHeaders()
	{
		foreach ((array)$this->_headers as $name => $value) {
			header($name . ':' . $value);
		}
	}

	/**
	 * 重定向
	 * @param $url
	 */
	public function redirect($url)
	{
		$this->setStatus(301);
		$this->setHeader('Location', $url);
		$this->sendHeaders();

		exit;
	}

	/**
	 * 发送响应
	 */
	public function send()
	{
		if ($this->_send) return;

		$this->sendHeaders();
		$this->outputBody();
		$this->_send = true;
		ob_get_flush();
	}
}