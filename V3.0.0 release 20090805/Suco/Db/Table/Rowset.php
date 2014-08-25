<?php

class Suco_Db_Table_Rowset implements ArrayAccess, Iterator, Countable
{
	protected $_table;
	protected $_data;
	protected $_valid;
	
	public function __construct($table, $data)
	{
		if (!$table instanceof Suco_Db_Table_Abstract) {
			throw new Suco_Exception('指定的不是Suco_Db_Table对象');	
		}

		$this->_table = $table;
		$this->_data = $data;
	}
	
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}
	
	public function __get($key)
	{
		return $this->get($key);
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
        return $this->_table->outputFilter(current($this->_data));
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
