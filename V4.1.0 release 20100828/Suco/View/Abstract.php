<?php 

require_once 'Suco/View/Interface.php';

class Suco_View_Abstract
{
	protected $_request;
	protected $_response;
	protected $_locale;
	protected $_scriptPath;
	protected $_data = array();
	
	public function __construct()
	{
		$this->setRequest(Suco_Application::getInstance()->getRequest());
		$this->setResponse(Suco_Application::getInstance()->getResponse());
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
			$this->_request = Suco_Application::getInstance()->getRequest();
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
			$this->_response = Suco_Application::getInstance()->getResponse();
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
			$this->_locale = Suco_Application::getInstance()->getLocale();
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