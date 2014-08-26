<?php

class Suco_Db_Table_Row_Abstract implements ArrayAccess, Iterator, Countable
{
	protected $_data;
	protected $_valid;
	
	public function __construct($row)
	{
		$this->_data = $row;
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
		return isset($this->_data[$key]);
	}
	
	public function __unset($key)
	{
		unset($this->_data[$key]);
	}
		
	public function set($key, $value)
	{
		$this->_data[$key] = $value;
	}
	
	public function get($key)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}
	
	public function toArray()
	{
		return $this->_data;
	}
	
	public function toJson()
	{
		return json_encode($this->_data);
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
        return isset($this->_data[$offset]);
    }
	
	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}

    public function current()
    {
        return current($this->_data);
    }

    public function key()
    {
        return key($this->_data);
    }

    public function next()
    {
		$this->_valid = next($this->_data);
    }

    public function rewind()
    {
        $this->_valid = reset($this->_data);
    }
	
    public function valid()
    {
        return $this->_valid;
    }

    public function count()
    {
        return count($this->_data);
    }
}