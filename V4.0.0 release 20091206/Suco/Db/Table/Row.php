<?php

class Suco_Db_Table_Row
{
	protected $_table;
	protected $_data;
	
	protected $_stored = false;
	
	public function __construct($table, $data, $stored = false)
	{
		if ($table instanceof Suco_Db_Table) {
			$this->_table = $table;
		} else {
			throw new Suco_Exception('指定的不是Suco_Db_Table对象');	
		}
		if (isset($data)) {
			$this->_data = $data;
		}
		$this->_stored = $stored;
	}
	
	public function save($data = null)
	{
		$data = array_merge($this->_data, (array)$data);	
		if ($this->_stored)	{
			$primary = $this->_table->getPrimary();
			$cond = $this->_table->getAdapter()->quoteInto($primary . ' = ?', (int)$data[$primary]);
			$this->_table->update($this->_data, $cond);
		} else {
			$this->_table->insert($this->_data);
		}
	}
	
	public function __set($field, $value)
	{
		$this->_data[$field] = $value;
	}
	
	public function __get($field)
	{
		return $this->_data[$field];
	}
}
