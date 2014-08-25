<?php

class Suco_Db_Table_Row implements ArrayAccess, Iterator, Countable
{
	protected $_columns;
	protected $_data;
	protected $_raw;
	protected $_clean;
	protected $_modified;
	protected $_valid;
	
	protected $_table;	
	protected $_stored = false;
	
	public function __construct($table, $row = array(), $store = false)
	{
		if ($table instanceof Suco_Db_Table_Abstract) {
			$this->_table = $table;
		} else {
			$this->_table = new $table();
		}
		
		$this->_stored = $store;
		
		if ($row) {
			$this->_raw = $row;
			$this->_clean = $row;
			$this->_data = $this->_table->outputFilter($row);
			$this->_columns = $this->_table->getColumns();
			$this->init();
		}
	}
	
	public function init() 
	{
		$referenceMap = $this->_table->getReferenceMap();
		$ref = array();
		foreach ($referenceMap as $class => $map) {
			if (!isset($this->_clean[$map['foreign']])) {
				require_once 'Suco/Db/Table/Exception.php';
				throw new Suco_Db_Table_Exception("外键{$map['foreign']}不存在");
			}
			$target = $map['target'];
			$columns = $map['columns'];
			$source = $map['source'];
			switch ($map['type']) {
				case 'hasone':
					$table = new $class();
					$ref = $table->find(array($source => $this->_raw[$target]))->toArray();
					$this->_clean = $this->_data = array_merge($this->_clean, $ref);
					break;
				case 'hasmany':
					$table = new $class();
					$row = $table->select($columns)
								 ->where("{$source} = ?", $this->_raw[$target])
								 ->fetchRow();
					$this->set(strtolower($class), $row);
					break;
			}
		}

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
		$this->_clean[$key] = $value;
		$this->_data[$key] = $value;
		$this->_modified[$key] = $value;
	}
	
	public function get($key)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}
	
	public function isStored()
	{
		return $this->_stored;
	}
	
	public function save($data = null)
	{
		if ($this->_stored)	{
			if (empty($this->_raw)) {
				require_once 'Suco/Db/Table/Exception.php';
				throw new Suco_Db_Table_Exception('被更新的记录不存在');
			}
			
			$data = array_merge($this->_modified, (array)$data);
			$identity = $this->_table->getIdentity();
			$cond = $this->_table->getAdapter()->quoteInto($identity . ' = ?', (int)$this->_raw[$identity]);
			$this->_table->update($data, $cond);
		} else {
			$this->_table->insert(array_merge($this->_clean, (array)$data));
		}
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
