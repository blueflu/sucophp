<?php
/**
 * Suco_Object 对象类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Object
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Object implements ArrayAccess, Iterator, Countable
{
	/**
	 * 保存配置数据
	 *
	 * @var array
	 */
	protected $_data;

	/**
	 * 迭代器接口变量
	 *
	 * @var bool
	 */
	protected $_valid;

	/**
	 * 构造函数
	 *
	 * @param array $data
	 * @return void
	 */
	public function __construct($data = array())
	{
		$this->_data = $data;
	}

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
	 * __isset 魔术方法
	 *
	 * @param string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		return isset($this->_data[$key]);
	}

	/**
	 * __unset 魔术方法
	 *
	 * @param string $key
	 * @return void
	 */
	public function __unset($key)
	{
		unset($this->_data[$key]);
	}

	/**
	 * 设置一项数据
	 * @param string $key 键
	 * @param mixed $value 值
	 *
	 * @return object 当前类
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;
	}

	/**
	 * 返回一项数据
	 *
	 * @param string $key 键
	 * @return mixed
	 */
	public function get($key)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}

	/**
	 * 将对象转为数组
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->_data;
	}

	/**
	 * 将对象转为JSON
	 *
	 * @return string JSON格式
	 */
	public function toJson()
	{
		return json_encode($this->_data);
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