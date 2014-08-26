<?php

require_once 'Suco/Db/Select.php';

class Suco_Db_Table_Select extends Suco_Db_Select
{
	protected $_table;
	
	public function __construct(Suco_Db_Table_Abstract $table)
	{
		$this->_table = $table;
		parent::__construct($table->getAdapter());
	}
	
	/*
	public function reference($key, $joinType = parent::LEFT_JOIN)
	{
		$mapping = $this->_table->getReferenceMap();
		$reference = $mapping[$key];
		$tableClass = new $reference['class'];
		$joinTable = $tableClass->getTableName();
		$cond = "{$joinTable}.{$reference['target']} = {$this->_table->getTableName()}.{$this->_table->getIdentity()}";
		$cols = isset($reference['columns']) ? $reference['columns'] : null;
		
		return parent::join($joinTable, $cond, $cols);
	}
	*/
	
	public function fetchRow()
	{
		if (!$this->_parts[self::LIMIT_OFFSET]) {
			$this->limit(1);
		}
		$rowClass = $this->_table->getRowClass();
		return new $rowClass($this->_table, parent::fetchRow(), true);
	}
	
	public function fetchRows()
	{
		$rowsetClass = $this->_table->getRowsetClass();
		return new $rowsetClass($this->_table, $this, parent::fetchRows());
	}
	
	public function fetchAll()
	{
		return $this->fetchRows();
	}
}