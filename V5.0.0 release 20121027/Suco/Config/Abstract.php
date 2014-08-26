<?php

/**
 * Suco_Config_Abstract 抽象类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package 	Config
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

require_once 'Suco/Config/Interface.php';

class Suco_Config_Abstract implements ArrayAccess, Iterator, Countable
{
	/**
	 * 保存配置数据
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * 配置文件路径
	 *
	 * @var string
	 */
	protected $_file = null;

	/**
	 * 迭代器接口变量
	 *
	 * @var bool
	 */
	protected $_vaild = false;

	/**
	 * __set 魔术方法
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * __get 魔术方法
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * 设置一项配置
	 * @param string $key 键
	 * @param mixed $value 值
	 *
	 * @return object 当前类
	 */
	public function set($key, $value = null)
	{
		if (is_array($value)) {
			$this->$key = new Suco_Config($value);
		} else {
			$this->$key = $value;
		}
		return $this;
	}

	/**
	 * 返回一项配置
	 *
	 * @param string $key 键
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->_data ? $this->_data[$key] : null;
	}

	/**
	 * 导入配置数组
	 *
	 * @param array $array
	 * @return void
	 */
	public function import($array)
	{
		foreach ($array as $key => $val) {
			$this->set($key, $val);
		}
	}

	/**
	 * 清空所以配置
	 *
	 * @return void
	 */
	public function clean()
	{
		$this->_data = array();
	}

	/**
	 * 将对象转为数组
	 *
	 * @return array
	 */
	public function toArray()
	{
		foreach ($this->_data as $key => $value) {
			if ($value instanceof Suco_Config_Interface) {
				$this->_data[$key] = $value->toArray();
			}
		}
		return $this->_data;
	}

	/**
	 * ArrayAccess 数组式访问 接口方法
	 *
	 * @param string $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * ArrayAccess 数组式访问 接口方法
	 *
	 * @param string $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * ArrayAccess 数组式访问 接口方法
	 *
	 * @param string $offset
	 * @return bool
	 */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

	/**
	 * ArrayAccess 数组式访问 接口方法
	 *
	 * @param string $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}

	/**
	 * Iterator 迭代器 接口方法
	 *
	 * @return mixed
	 */
    public function current()
    {
        return current($this->_data);
    }

	/**
	 * Iterator 迭代器 接口方法
	 *
	 * @return scalar
	 */
    public function key()
    {
        return key($this->_data);
    }

	/**
	 * Iterator 迭代器 接口方法
	 *
	 * @return void
	 */
    public function next()
    {
		$this->_valid = next($this->_data);
    }

	/**
	 * Iterator 迭代器 接口方法
	 *
	 * @return void
	 */
    public function rewind()
    {
        $this->_valid = reset($this->_data);
    }

	/**
	 * Iterator 迭代器 接口方法
	 *
	 * @return boolean
	 */
    public function valid()
    {
        return $this->_valid;
    }

	/**
	 * Countable 统计 接口方法
	 *
	 * @return int
	 */
    public function count()
    {
        return count($this->_data);
    }
}