<?php 

/**
 * 封装用户请求
 * @author Blueflu
 *
 */

require_once 'Suco/Controller/Request/Abstract.php';

class Suco_Controller_Request_Http extends Suco_Controller_Request_Abstract
{
	protected $_params;
	
	/**
	 * 返回完整请求地址
	 * @return string
	 */
	public function getRequestUri()
	{
		return $this->getServer('REQUEST_URI');
	}
	
	/**
	 * 返回请求基地址
	 * @return unknown_type
	 */
	public function getBaseUrl()
	{
		static $baseUrl;
		
		if (!isset($baseUrl)) {
			$baseUrl = $this->getServer('SCRIPT_NAME');
			if ($baseUrl != substr($this->getRequestUri(), 0, strlen($baseUrl))) {
				$baseUrl = $this->getBasePath();
			}
		}
		
		return rtrim($baseUrl, '/');
	}
	
	/**
	 * 返回请求基目录
	 * @return string
	 */
	public function getBasePath()
	{
		static $basePath;
		
		if (!isset($basePath)) {
			if ($this->getServer('SCRIPT_NAME') != null) {
				$basePath = dirname($this->getServer('SCRIPT_NAME'));
			}
			if (substr(PHP_OS, 0, 3) == 'WIN') {
				$basePath = str_replace('\\', '/', $basePath);
			}
		}
		return trim($basePath, '/');
	}
	
	public function setParams($params)
	{
		$this->_params = array_merge((array)$this->_params, $params);
	}
	
	public function getParams()
	{
		return array_merge((array)$this->_params, $this->getQuerys());
	}
	
	public function setParam($key, $value)
	{
		$this->_params[$key] = $value;
	}
	
	/**
	 * 返回指定参数
	 * @return mixed
	 */
	public function getParam($key)
	{
		$params = $this->getParams();
		return isset($params[$key]) ? $params[$key] : null;
	}
	
	/**
	 * 返回所有QUERY参数
	 * @return array
	 */
	public function getQuerys()
	{
		static $querys;
		if (!isset($querys)) {
			parse_str($this->getServer('QUERY_STRING'), $querys);
		}
		return $querys;
	}
	
	/**
	 * 返回SERVER环境
	 * @param string $key
	 * @return mixed
	 */
	public function getServer($key)
	{
		return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
	}
	
	/**
	 * 返回客户端IP
	 * @return string
	 */
	public function getClientIp()
	{
		if ($this->getServer('HTTP_CLIENT_IP') != null) {
			$ip = $this->getServer('HTTP_CLIENT_IP');
		} elseif ($this->getServer('HTTP_X_FORWARDED_FOR') != null) {
			$ip = $this->getServer('HTTP_X_FORWARDED_FOR');
		} elseif ($this->getServer('REMOTE_ADDR') != null) {
			$ip = $this->getServer('REMOTE_ADDR');
		}
		return $ip;
	}

	/**
	 * 返回PATH_INFO信息
	 * @return string
	 */
	public function getPathInfo()
	{
		return $this->getServer('PATH_INFO');
	}
	
	public function getHost()
	{
		return $this->getServer('HTTP_HOST');
	}
	
	/**
	 * 判断请求方式是否为AJAX
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->getServer('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';
	}
	
	/**
	 * 判断请求方式是否为GET
	 * @return bool
	 */
	public function isGet()
	{
		return $this->getMethod() == 'GET';
	}
	
	/**
	 * 判断请求方式是否为POST
	 * @return bool
	 */
	public function isPost()
	{
		return $this->getMethod() == 'POST';
	}
	
	/**
	 * 返回请求方式
	 * @return string
	 */
	public function getMethod()
	{
		return $this->getServer('REQUEST_METHOD');
	}
}