<?php 

require_once 'Suco/Config/Interface.php';

class Suco_Config_Abstract implements Suco_Config_Interface, ArrayAccess, Iterator, Countable
{
	protected $_data = array();
	protected $_file = null;
	protected $_vaild = false;
	
	public function save($file = null)
	{
		$file = $file ? $file : $this->_file;
		$content = $this->_generate($this->toArray());
		file_put_contents($file, $content);
	}
	
	public function set($offset, $value = null)
	{
		if (is_array($value)) {
			$this->$offset = new Suco_Config($value);
		} else {
			$this->$offset = $value;
		}
		return $this;
	}
	
	public function get($offset)
	{
		if (!array_key_exists($offset, $this->_data)) {
			require_once 'Suco/Config/Exception.php';
			throw new Suco_Config_Exception('没有找到指定键值');
		}
		return $this->_data[$offset];
	}
	
	public function __set($offset, $value)
	{
		$this->_data[$offset] = $value;
	}
	
	public function __get($offset)
	{
		return $this->_data[$offset];
	}
	
	public function clean()
	{
		$this->_data = array();	
	}
	
	public function toArray()
	{
		foreach ($this->_data as $key => $value) {
			if ($value instanceof Suco_Config_Interface) {
				$this->_data[$key] = $value->toArray();
			}
		}
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