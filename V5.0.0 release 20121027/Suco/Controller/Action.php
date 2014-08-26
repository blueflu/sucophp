<?php
/**
 * Suco_Controller_Action 动作控制器抽象
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

abstract class Suco_Controller_Action
{
	protected $_request;
	protected $_response;
	protected $_dispatcher;
	protected $_view;
	protected $_params;

	/**
	 * 初始化动作控制器
	 */
	public function init() {}

	/**
	 * 设置控制器参数
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function setParam($key, $value)
	{
		$this->_params[$key] = $value;
	}

	/**
	 * 返回控制器参数
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getParam($key)
	{
		return $this->_params[$key];
	}

	/**
	 * 设置控制器参数集
	 *
	 * @param array $params
	 */
	public function setParams($params)
	{
		$this->_params = $params;
	}

	/**
	 * 返回控制器参数集
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * 返回当前模块目录
	 *
	 * @return string
	 */
	public function getModulePath()
	{
		return dirname($this->getControllerPath());
	}

	/**
	 * 返回当前控制器目录
	 *
	 * @return string
	 */
	public function getControllerPath()
	{
		static $path;
		if (!isset($path)) {
			$path = Suco_Application::instance()
					->getDispatcher()
					->getControllerDirectory();
		}

		return $path;
	}

	/**
	 * 设置请求对象
	 *
	 * @return string
	 */
	public function setRequest(Suco_Controller_Request_Interface $request = null)
	{
		if ($request != null) {
			$this->_request = $request;
		} else {
			$this->_request = Suco_Application::instance()->getRequest();
		}
	}

	/**
	 * 返回请求对象
	 *
	 * @return object
	 */
	public function getRequest()
	{
		if (!$this->_request) {
			$this->setRequest();
		}
		return $this->_request;
	}

	/**
	 * 设置响应对象
	 *
	 * @param object $response
	 * @return object
	 */
	public function setResponse(Suco_Controller_Response_Interface $response = null)
	{
		if ($response != null) {
			$this->_response = $response;
		} else {
			$this->_response = Suco_Application::instance()->getResponse();
		}
	}

	/**
	 * 返回响应对象
	 *
	 * @return object
	 */
	public function getResponse()
	{
		if (!$this->_response) {
			$this->setResponse();
		}
		return $this->_response;
	}

	/**
	 * 设置本地化对象
	 *
	 * @param object $locale
	 * @return object
	 */
	public function setLocale($locale = null)
	{
		if ($locale instanceof Suco_Locale) {
			$this->_locale = $locale;
		} else {
			require_once 'Suco/Locale.php';
			$this->_locale = new Suco_Locale();
		}
		return $this;
	}

	/**
	 * 返回本地化对象
	 *
	 * @return object
	 */
	public function getLocale()
	{
		if (!$this->_locale) {
			$this->setLocale();
		}

		return $this->_locale;
	}

	/**
	 * 设置分发器对象
	 *
	 * @param object $dispatcher
	 * @return object
	 */
	public function setDispatcher($dispatcher = null)
	{
		if ($dispatcher instanceof Suco_Controller_Dispatcher_Interface) {
			$this->_dispatcher = $dispatcher;
		} else {
			require_once 'Suco/Controller/Dispatcher/Standard.php';
			$this->_dispatcher = new Suco_Controller_Dispatcher_Standard($this->getRequest(), $this->getResponse());
		}
		return $this;
	}

	/**
	 * 返回分发器对象
	 *
	 * @return object
	 */
	public function getDispatcher()
	{
		if (!$this->_dispatcher) {
			$this->setDispatcher();
		}

		return $this->_dispatcher;
	}

	/**
	 * 设置视图对象
	 *
	 * @param object $view
	 * @return object
	 */
	public function setView(Suco_View_Interface $view = null)
	{
		if ($view != null ) {
			$this->_view = $view;
		} else {
			$this->_view = new Suco_View();
		}
		return $this;
	}

	/**
	 * 返回视图对象
	 *
	 * @return object
	 */
	public function getView()
	{
		if (!$this->_view) {
			$this->setView();
		}

		return $this->_view;
	}

	/**
	 * 重定向
	 *
	 * @param string $url
	 * @param string $method #php|js
	 * @return void
	 */
	public function redirect($url, $method = 'php')
	{
		if ($this->getRequest()->isAjax()) { //禁止Ajax方式跳转
			return;
		}
		$url = new Suco_Helper_Url($url);
		switch ($method) {
			case 'php':
				$this->_response->redirect($url);
				break;
			case 'js':
				echo '<script>window.location = \''.$url.'\';</script>';
				break;
		}
	}

	/**
	 * 开始动作分发
	 *
	 * @param string $action
	 * @return void
	 */
	public function dispatch($action)
	{
		$this->init();
		$this->__call($this->_formatActionName($action));
	}

	/**
	 * __call 魔术方法
	 *
	 * @param string $method
	 * @param array $args
	 * @return void
	 */
	public function __call($method, $args = array())
	{
		while (true) {
			if (strpos($method, '_')) {
				$delimit = substr($method, strpos($method, '_'), 2);
				$method = str_replace($delimit, strtoupper(substr($delimit, 1)), $method);
			} else {
				break;
			}
		}
		if (substr($method, 0, 2) == 'do') {
			if (!method_exists($this, $method)) {
				require_once 'Suco/Controller/Dispatcher/Exception.php';
				throw new Suco_Controller_Dispatcher_Exception("没有找到指定动作 {$method}");
			}
			return call_user_func(array($this, $method));
		}

		require_once 'Suco/Exception.php';
		throw new Suco_Exception('Not Found Method ' . $method);
	}

	/**
	 * 格式化动作名
	 *
	 * @param string $action
	 * @return string
	 */
	protected function _formatActionName($action)
	{
		return 'do' . ucfirst($action);
	}
}