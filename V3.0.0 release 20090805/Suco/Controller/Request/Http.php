<?php 

/**
 * 封装用户请求
 * @author Blueflu
 *
 */

require_once 'Suco/Controller/Request/Abstract.php';

class Suco_Controller_Request_Http extends Suco_Controller_Request_Abstract
{
	protected $_router;
	protected $_params;
	protected $_querys;
	
	public function setRouter($router = null)
	{
		if ($router instanceof Suco_Controller_Router_Interface) {
			$this->_router = $router;
		} else {
			require_once 'Suco/Controller/Router/Route.php';
			$this->_router = new Suco_Controller_Router_Route();
			$this->_router->setRequest($this->getRequest());
		}
	}
	
	public function getRouter()
	{
		if (!$this->_router) {
			$this->setRouter();
		}
		return $this->_router;
	}
	
	/**
	 * 返回完整请求地址
	 * @return string
	 */
	public function getRequestUri()
	{
		return self::getServer('REQUEST_URI');
	}
	
	/**
	 * 返回请求基地址
	 * @return unknown_type
	 */
	public function getBaseUrl()
	{
		static $baseUrl;
		
		if (!isset($baseUrl)) {
			$baseUrl = self::getServer('SCRIPT_NAME');
			if ($baseUrl != substr(self::getRequestUri(), 0, strlen($baseUrl))) {
				$baseUrl = self::getBasePath();
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
			if (self::getServer('SCRIPT_NAME') != null) {
				$basePath = dirname(self::getServer('SCRIPT_NAME'));
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
		return array_merge((array)$this->_params, self::getQuerys(), $_GET, $_POST);
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
		$params = self::getParams();
		return isset($params[$key]) ? $params[$key] : null;
	}
	
	/**
	 * 返回所有QUERY参数
	 * @return array
	 */
	public function getQuerys()
	{
		if (!isset($this->_querys)) {
			parse_str(self::getServer('QUERY_STRING'), $querys);
			$this->setQuerys($querys);
		}
		return $this->_querys;
	}
	
	public function setQuerys($querys)
	{
		$this->_querys = array_merge((array)$querys, (array)$this->_querys);
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
		if (self::getServer('HTTP_CLIENT_IP') != null) {
			$ip = self::getServer('HTTP_CLIENT_IP');
		} elseif (self::getServer('HTTP_X_FORWARDED_FOR') != null) {
			$ip = self::getServer('HTTP_X_FORWARDED_FOR');
		} elseif (self::getServer('REMOTE_ADDR') != null) {
			$ip = self::getServer('REMOTE_ADDR');
		}
		return $ip;
	}

	/**
	 * 返回PATH_INFO信息
	 * @return string
	 */
	public function getPathInfo()
	{
		return self::getServer('PATH_INFO');
	}
	
	public function getHost()
	{
		return self::getServer('HTTP_HOST');
	}
	
	/**
	 * 判断请求方式是否为AJAX
	 * @return bool
	 */
	public function isAjax()
	{
		return self::getServer('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';
	}
	
	/**
	 * 判断请求方式是否为GET
	 * @return bool
	 */
	public function isGet()
	{
		return self::getMethod() == 'GET';
	}
	
	/**
	 * 判断请求方式是否为POST
	 * @return bool
	 */
	public function isPost()
	{
		return self::getMethod() == 'POST';
	}
	
	/**
	 * 返回请求方式
	 * @return string
	 */
	public function getMethod()
	{
		return self::getServer('REQUEST_METHOD');
	}
}