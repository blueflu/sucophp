<?php
/**
 * Suco_Controller_Request_Abstract 请求抽象类
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Controller
 * -----------------------------------------------------------
 */

require_once 'Suco/Controller/Request/Interface.php';

class Suco_Controller_Request_Abstract implements Suco_Controller_Request_Interface, ArrayAccess, Iterator, Countable
{
	protected $_params;
	protected $_valid;

	protected $_moduleName;
	protected $_controllerName;
	protected $_actionName;

	protected $_moduleKey = 'module';
	protected $_controllerKey = 'controller';
	protected $_actionKey = 'action';

	/**
	 * 单件实例
	 * @return self
	 */
	public function instance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}

	public function set($key, $value)
	{
		$this->setParam($key, $value);
	}

	public function get($key)
	{
		return $this->getParam($key);
	}

	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function __isset($key)
	{
		$params = $this->getParams();
		return isset($params[$key]);
	}

	public function __unset($key)
	{
		unset($this->_params[$key]);
	}

	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

    public function offsetExists($offset)
    {
        return isset($this->_params[$offset]);
    }

	public function offsetUnset($offset)
	{
		unset($this->_params[$offset]);
	}

    public function current()
    {
        return current($this->_params);
    }

    public function key()
    {
        return key($this->_params);
    }

    public function next()
    {
		$this->_valid = next($this->_params);
    }

    public function rewind()
    {
        $this->_valid = reset($this->_params);
    }

    public function valid()
    {
        return $this->_valid;
    }

    public function count()
    {
        return count($this->_params);
    }

	/**
	 * 设置请求的模块名
	 * @param string $moduleName
	 * @return self
	 */
	public function setModuleName($moduleName)
	{
		$this->_moduleName = $moduleName;
		return $this;
	}

	/**
	 * 返回请求的模型名
	 * @return string
	 */
	public function getModuleName()
	{
		if (empty($this->_moduleName)) {
			$this->setModuleName($this->getParam($this->getModuleKey()));
		}
		return $this->_moduleName;
	}

	/**
	 * 设置请求的控制器名
	 * @param string $controllerName
	 * @return self
	 */
	public function setControllerName($controllerName)
	{
		$this->_controllerName = $controllerName;
		return $this;
	}

	/**
	 * 返回请求的控制器名
	 * @return string
	 */
	public function getControllerName()
	{
		if (empty($this->_controllerName)) {
			$this->setControllerName($this->getParam($this->getControllerKey()));
		}
		return $this->_controllerName;
	}

	/**
	 * 设置请求的动作名
	 * @param string $actionName
	 * @return self
	 */
	public function setActionName($actionName)
	{
		$this->_actionName = $actionName;
		return $this;
	}

	/**
	 * 返回请求的动作名
	 * @return string
	 */
	public function getActionName()
	{
		if (empty($this->_actionName)) {
			$this->setActionName($this->getParam($this->getActionKey()));
		}
		return $this->_actionName;
	}

	/**
	 * 设置模块键名
	 * @param $key
	 */
	public function setModuleKey($key)
	{
		$this->_moduleKey = $key;
	}

	/**
	 * 返回模块键名
	 * @return string
	 */
	public function getModuleKey()
	{
		return $this->_moduleKey;
	}

	/**
	 * 设置控制器键名
	 * @param $key
	 */
	public function setControllerKey($key)
	{
		$this->_controllerKey = $key;
	}

	/**
	 * 返回控制器键名
	 * @return string
	 */
	public function getControllerKey()
	{
		return $this->_controllerKey;
	}

	/**
	 * 设置动作键名
	 * @param $key
	 */
	public function setActionKey($key)
	{
		$this->_actionKey = $key;
	}

	/**
	 * 返回动作键名
	 * @return string
	 */
	public function getActionKey()
	{
		return $this->_actionKey;
	}
}