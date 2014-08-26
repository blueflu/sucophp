<?php
/**
 * Suco_Controller_Router_Route 路由类
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 * 配置实例
 *
 * <code>
 * $route = new Suco_Controller_Router_Route();
 *
 * #设置模块路由
 * $route->addRoute('default', new Suco_Controller_Router_Route_Module(
 *		':module/:controller/:action/*',
 *		array(
 *			#当没有指定相关请求时使用默认值路由
 *			'defaults' => array(
 *				'module' => 'default',
 *				'controller' => 'index',
 *				'action' => 'default'
 *		))
 *	));
 * #设置正则路由
 * $route->addRoute('news', new Suco_Controller_Router_Route_Regex(
 *		'^/news\/(\d+).html', //正向解析
 *		array(
 *			//控制器映射
 *			'defaults' => array(
 *				'module' => 'default',
 *				'controller' => 'news',
 *				'action' => 'detail'
 *			),
 *			'mapping' => array(
 *				1 => 'id',
 *			),
 *			'reverse' => 'news/%d.html' //反向解析
 *		)
 *	))
 *
 * </code>
 *
 */

require_once 'Suco/Controller/Router/Interface.php';

class Suco_Controller_Router_Route implements Suco_Controller_Router_Interface
{
	const ROUTE_MAPPING = 'mapping';
	const ROUTE_PATTERN = 'pattern';
	const ROUTE_REVERSE = 'reverse';
	const ROUTE_DEFAULTS = 'defaults';

	protected $_request;
	protected $_routes = array();
	protected $_currentRoute;
	protected $_delimit = '/';
	protected $_domainMapping = array();

	/**
	 * 参数设置方法
	 *
	 * @param array $options
	 */
	public function setOptions($options)
	{
		foreach ($options as $key => $option) {
			$method = 'set' . ucfirst($key);
			$this->$method($option);
		}
	}

	/**
	 * 设置路由
	 *
	 * @param object $routes
	 * @return void
	 */
	public function setRoutes($routes)
	{
		foreach ($routes as $name => $route) {
			$this->addRoute($name, $route);
		}
	}

	/**
	 * 返回路由
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->_routes;
	}

	/**
	 * 设置请求对象
	 *
	 * @param object $request
	 * @return object
	 */
	public function setRequest(Suco_Controller_Request_Interface $request)
	{
		$this->_request = $request;
	}

	/**
	 * 返回请求对象
	 *
	 * @param array $options
	 */
	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * 设置域路由
	 *
	 * @param array $mapping
	 * @return object
	 */
	public function setDomainMapping($mapping)
	{
		$this->_domainMapping = $mapping;
		return $this;
	}

	/**
	 * 返回域路由
	 *
	 * @return array
	 */
	public function getDomainMapping()
	{
		return $this->_domainMapping;
	}

	/**
	 * 添加路由规则
	 *
	 * @param string $name
	 * @param object $route
	 * @return object
	 */
	public function addRoute($name, $route)
	{
		if (is_array($route)) {
			$adapter = $route['type'];
			$pattern = $route['pattern'];
			require_once str_replace('_', '/', $route['type']) . '.php';
			$this->_routes[$name] = new $adapter($pattern, $route);
		} elseif ($route instanceof Suco_Controller_Router_Route_Abstract) {
			$this->_routes[$name] = $route;
		} else {
			require_once 'Suco/Controller/Router/Exception.php';
			throw new Suco_Controller_Router_Exception('无效设置');
		}
		return $this;
	}

	/**
	 * 移除路由规则
	 *
	 * @param string $name
	 * @return void
	 */
	public function removeRoute($name)
	{
		unset($this->_routes[$name]);
	}

	/**
	 * 清空路由规则
	 *
	 * @return void
	 */
	public function clearRoute()
	{
		$this->_routes = array();
	}

	/**
	 * 正向路由
	 *
	 * @return void
	 */
	public function routing()
	{
		$this->_request->setRouter($this);

		$routes = array_reverse($this->_routes);
		foreach ($routes as $name => $route) {
			if ($params = $route->match($this->_request)) {
				if ($this->_domainMapping) {
					$host = $this->_request->getHost();
					if (isset($this->_domainMapping[$host])) {
						$params[$this->_request->getModuleKey()] = $this->_domainMapping[$host];
					}
				}
				$this->_currentRoute = $name;
				$this->_request->setQuerys($params);
				return;
			}
		}
	}

	/**
	 * 反向路由
	 *
	 * @param string|array $querys 查询请求
	 * @param string $name 指定路由对象
	 * @return void
	 */
	public function reverse($querys, $name = null)
	{
		if (!$querys) {
			return null;
		} elseif (is_string($querys) && strpos($querys, $this->_delimit) !== false) { //已经转换
			return $querys;
		} elseif (!is_array($querys)) {
			parse_str($querys, $options);
		}

		$moduleKey = $this->_request->getModuleKey();
		$controllerKey = $this->_request->getControllerKey();
		$actionKey = $this->_request->getActionKey();

		//如果链接是以&开头的.则为追加查询参数
		if (is_string($querys) && substr($querys, 0, 1) == '&') {
			$options = array_merge($this->_request->getQuerys(), array(
				$moduleKey => $this->_request->getModuleName(),
				$controllerKey => $this->_request->getControllerName(),
				$actionKey => $this->_request->getActionName(),
			), $options);
		}

    	//补齐URL
    	if (isset($options[$controllerKey]) && !isset($options[$moduleKey])) {
    		$options[$moduleKey] = $this->_request->getModuleName();
    	}
    	if (isset($options[$actionKey]) && !isset($options[$controllerKey])) {
    		$options[$moduleKey] = $this->_request->getModuleName();
    		$options[$controllerKey] = $this->_request->getControllerName();
    	}

    	$options = array_merge(array($moduleKey => '', $controllerKey => '', $actionKey => ''), $options);
    	$routes = array_reverse($this->_routes);
		$baseUrl = Suco_Application::instance()->getRequest()->getHost()
			. Suco_Application::instance()->getRequest()->getBaseUrl();
		if ($this->_domainMapping) {
			$module = $options[$moduleKey];
			foreach ($this->_domainMapping as $host => $mod) {
				if ($mod == $module) {
					$baseUrl = "http://" . $host
						. Suco_Application::instance()->getRequest()->getBaseUrl();
					break;
				}
			}
		}

    	if (isset($routes[$name])) {
    		if ($url = $routes[$name]->reverseMatch($options)) {
				$url == '/' && $url = '';
				return trim($baseUrl, '/') . '/' . $url;
			}
    	} else {
			foreach ($routes as $name => $route) {
				if ($url = $route->reverseMatch($options)) {
					$url == '/' && $url = '';
					return trim($baseUrl, '/') . '/' . $url;
				}
			}
    	}

		foreach ($options as $key => $val) {
			if (empty($val)) {
				unset($options[$key]);
			}
		}
		return '?' . http_build_query($options);
	}
}