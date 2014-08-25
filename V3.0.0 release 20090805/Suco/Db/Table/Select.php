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
	
	public function fetchRow()
	{
		$rowClass = $this->_table->getRowClass();
		return new Suco_Db_Table_Row($this->_table, parent::fetchRow(), true);
	}
	
	public function fetchRows()
	{
		$rowsetClass = $this->_table->getRowsetClass();
		return new Suco_Db_Table_Rowset($this->_table, parent::fetchRows());
	}
	
	public function fetchAll()
	{
		return $this->fetchRows();
	}
}