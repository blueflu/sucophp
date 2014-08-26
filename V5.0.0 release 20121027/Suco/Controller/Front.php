<?php
/**
 * Suco_Controller_Action 前置控制器
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

class Suco_Controller_Front
{
	protected $_options;

	protected $_request;
	protected $_response;
	protected $_router;
	protected $_locale;
	protected $_dispatcher;

	protected $_throwException = true;

	/**
	 * 设置请求对象
	 *
	 * @param object $request
	 * @return object Suco_Controller_Front
	 */
	public function setRequest($request = null)
	{
		if ($request instanceof Suco_Controller_Request_Interface) {
			$this->_request = $request;
		} else {
			require_once 'Suco/Controller/Request/Http.php';
			$this->_request = new Suco_Controller_Request_Http();
		}
		return $this;
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
	 * @return object Suco_Controller_Front
	 */
	public function setResponse($response = null)
	{
		if ($response instanceof Suco_Controller_Response_Interface) {
			$this->_response = $response;
		} else {
			require_once 'Suco/Controller/Response/Http.php';
			$this->_response = new Suco_Controller_Response_Http();
		}
		return $this;
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
	 * 设置路由对象
	 *
	 * @param object $router
	 * @return object Suco_Controller_Front
	 */
	public function setRouter($router = null)
	{
		if ($router instanceof Suco_Controller_Router_Interface) {
			$this->_router = $router;
		} else {
			require_once 'Suco/Controller/Router/Route.php';
			$this->_router = new Suco_Controller_Router_Route();
			$this->_router->setRequest($this->getRequest());
		}
		return $this;
	}

	/**
	 * 返回路由对象
	 *
	 * @return object
	 */
	public function getRouter()
	{
		if (!$this->_router) {
			$this->setRouter();
		}
		return $this->_router;
	}

	/**
	 * 设置本地化对象
	 *
	 * @param object $locale
	 * @return object Suco_Controller_Front
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
	 * @return object Suco_Controller_Front
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
	 * 运行控制器
	 *
	 * @param string $bootstrap 引导文件
	 * @return void
	 */
	public function run($bootstrap)
	{
		require_once $bootstrap;
		$this->_resource = new Bootstrap();

		try {
			$this->getRouter()->routing();
			$this->getDispatcher()
				 ->dispatch();
		} catch (Suco_Exception $e) {
			if ($this->getDispatcher()->isController('error', $this->getDispatcher()->getModule())) {
				$this->getDispatcher()
					 ->dispatch('error', 'default', $this->getDispatcher()->getModule(), array('error_handle' => $e));
			} else {
				echo $e->dump();
			}
		}

		$this->getResponse()->appendBody(ob_get_clean());
	}
}