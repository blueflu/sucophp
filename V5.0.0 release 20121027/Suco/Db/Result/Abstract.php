<?php
/**
 * 返回记录集抽象类
 *
 * @version		3.0 2009/09/01 01:31
 * @author		blueflu (lqhuanle@163.com)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */

abstract class Suco_Db_Result_Abstract
{
	protected $_result;
	
	public function __construct($result, $fieldNameLower = false)
	{
		$this->_result = &$result;
	}

	abstract public function free();
	
	abstract public function fetchRow();
		
	public function fetchRows()
	{
		$recordset = array();
		while ($row = $this->fetchRow()) {
			$recordset[] = $row;
		}

		return $recordset;
	}
	
	public function fetchCol($field)
	{
		$row = $this->fetchRow();
		return $row[$field];
	}
		
	public function fetchCols($field)
	{
		$recordset = array();
		while ($row = $this->fetchRow()) {
			$recordset[] = $row[$field];
		}
		return $recordset;
	}

	public function fetchAll()
	{
		return $this->fetchRows();
	}
	
	public function fetchOnKey($key)
	{
		$recordset = array();
		while ($row = $this->fetchRow()) {
			$recordset[$row[$key]] = $row;
		}

		return $recordset;
	}
	
	public function __destruct()
	{
		$this->free();	
	}
}
?>