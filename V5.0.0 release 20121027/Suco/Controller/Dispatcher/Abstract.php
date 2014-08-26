<?php
/**
 * Suco_Controller_Dispatcher_Abstract 分发器抽象
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

require_once 'Suco/Controller/Dispatcher/Interface.php';

class Suco_Controller_Dispatcher_Abstract implements Suco_Controller_Dispatcher_Interface
{
	/**
	 * 当前模块
	 * @var string
	 */
	protected $_module;

	/**
	 * 当前控制器
	 * @var string
	 */
	protected $_controller;

	/**
	 * 当前动作
	 * @var string
	 */
	protected $_action;

	/**
	 * 默认模块
	 */
	protected $_defaultModule = 'default';

	/**
	 * 默认控制器
	 */
	protected $_defaultController = 'index';

	/**
	 * 默认动作
	 */
	protected $_defaultAction = 'default';

	/**
	 * 设置当前模块
	 *
	 * @param string $module
	 * @return void
	 */
	public function setModule($module = null)
	{
		$this->_module = $module ? $module : $this->getDefaultModule();
	}

	/**
	 * 返回当前模块
	 *
	 * @return string
	 */
	public function getModule()
	{
		return $this->_module;
	}

	/**
	 * 设置当前控制器
	 *
	 * @param string $controller
	 * @return void
	 */
	public function setController($controller = null)
	{
		$this->_controller = $controller ? $controller : $this->getDefaultController();
	}

	/**
	 * 返回当前控制器
	 *
	 * @return string
	 */
	public function getController()
	{
		while (true) {
			if (strpos($this->_controller, '_')) {
				$delimit = substr($this->_controller, strpos($this->_controller, '_'), 2);
				$this->_controller = str_replace($delimit, strtoupper(substr($delimit, 1)), $this->_controller);
			} else {
				break;
			}
		}
		return $this->_controller;
	}

	/**
	 * 设置当前动作
	 *
	 * @param string $action
	 * @return void
	 */
	public function setAction($action)
	{
		$this->_action = $action ? $action : $this->getDefaultAction();
	}

	/**
	 * 返回当前动作
	 *
	 * @return void
	 */
	public function getAction()
	{
		return $this->_action;
	}

	/**
	 * 设置默认模块
	 *
	 * @param string $module
	 * @return void
	 */
	public function setDefaultModule($module)
	{
		$this->_defaultModule = $module;
	}

	/**
	 * 返回默认模块
	 *
	 * @return void
	 */
	public function getDefaultModule()
	{
		return $this->_defaultModule;
	}

	/**
	 * 设置默认控制器
	 *
	 * @param string $controller
	 * @return void
	 */
	public function setDefaultController($controller)
	{
		$this->_defaultController = $controller;
	}

	/**
	 * 返回默认控制器
	 *
	 * @return void
	 */
	public function getDefaultController()
	{
		return $this->_defaultController;
	}

	/**
	 * 设置默认动作
	 *
	 * @param string $action
	 * @return void
	 */
	public function setDefaultAction($action)
	{
		$this->_defaultAction = $action;
	}

	/**
	 * 返回默认动作
	 *
	 * @return void
	 */
	public function getDefaultAction()
	{
		return $this->_defaultAction;
	}
}