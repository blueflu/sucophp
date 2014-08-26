<?php
/**
 * Suco_View_Abstract 视图抽象
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		View
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_View_Abstract
{
	protected $_request;
	protected $_response;
	protected $_locale;
	protected $_scriptPath;
	protected $_data = array();

	public function __construct()
	{
		$this->setRequest(Suco_Application::instance()->getRequest());
		$this->setResponse(Suco_Application::instance()->getResponse());
	}

	public function __set($index, $value)
	{
		$this->_data[$index] = $value;
	}

	public function __get($index)
	{
		if (isset($this->_data[$index])) {
			return $this->_data[$index];
		}
		return null;
	}

	public function __isset($index)
	{
		return isset($this->_data[$index]);
	}

	public function __unset($index)
	{
		unset($this->_data[$index]);
	}

	public function assign($index, $value = null)
	{
		if (is_array($index)) {
			$this->_data = array_merge($this->_data, $index);
		} else {
			$this->_data[$index] = $value;
		}
		return $this;
	}

	public function setRequest(Suco_Controller_Request_Interface $request)
	{
		$this->_request = $request;
		return $this;
	}

	public function getRequest()
	{
		if (!$this->_request) {
			$this->_request = Suco_Application::instance()->getRequest();
		}
		return $this->_request;
	}

	public function setResponse(Suco_Controller_Response_Interface $response)
	{
		$this->_response = $response;
		return $this;
	}

	public function getResponse()
	{
		if (!$this->_response) {
			$this->_response = Suco_Application::instance()->getResponse();
		}
		return $this->_response;
	}

	public function setLocale(Suco_Locale $locale)
	{
		$this->_locale = $locale;
		return $this;
	}

	public function getLocale()
	{
		if (!$this->_locale) {
			$this->_locale = Suco_Application::instance()->getLocale();
		}
		return $this->_locale;
	}

	public function setScriptPath($path)
	{
		$this->_scriptPath = $path;
		return $this;
	}

	public function getScriptPath()
	{
		return $this->_scriptPath;
	}
}