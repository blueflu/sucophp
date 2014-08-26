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
		if (self::getServer('HTTP_X_REWRITE_URL')) {
			return self::getServer('HTTP_X_REWRITE_URL');
		} elseif (self::getServer('REQUEST_URI')) {
			return self::getServer('REQUEST_URI');
		}
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
	
	public function getDomain()
	{
		return $_SERVER['SERVER_NAME'];
	}
	
	public function getSubdomain()
	{
		$domain = $this->getDomain();
		$d = explode('.', $domain);
		if (count($d) > 2) {
			return $d[0];
		}
		return;
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
	
	public function getPosts()
	{
		return $_POST;
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
		$this->_querys = array_merge((array)$querys, (array)$this->_querys, $_GET);
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
		if (self::getServer('HTTP_X_REWRITE_URL')) {
			$pathinfo = self::getServer('HTTP_X_REWRITE_URL');
		} elseif (self::getServer('PATH_INFO')) {
			$pathinfo = self::getServer('PATH_INFO');
		} elseif (self::getServer('ORIG_PATH_INFO')) {
			$pathinfo = self::getServer('ORIG_PATH_INFO');
			$pathinfo = str_replace(self::getServer('SCRIPT_NAME'), null, $pathinfo);
		}
		return $pathinfo;
	}
	
	public function getHost()
	{
		$protocol = self::getServer('HTTPS') == 'on' ? 'https://' : 'http://';
		return $protocol . self::getServer('HTTP_HOST');
	}
	
	
	/**
	 * 返回请求方式
	 * @return string
	 */
	public function getMethod()
	{
		return self::getServer('REQUEST_METHOD');
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
	
	public function post($key)
	{
		return isset($_POST[$key]) ? $_POST[$key] : null;
	}
	
	public function query($key)
	{
		return isset($_GET[$key]) ? $_GET[$key] : null;
	}

	public function file($key)
	{
		return isset($_FILES[$key]) ? $_FILES[$key] : null;
	}
}