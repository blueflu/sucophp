<?php

require_once 'Suco/Db/Result/Abstract.php';

class Suco_Db_Result_Sqlsrv extends Suco_Db_Result_Abstract
{
	public function free()
	{
		if (is_resource($this->_result)) {
			sqlsrv_free_stmt($this->_result);	
		}
	}
	
	public function fetchRow()
	{
		$row = sqlsrv_fetch_array($this->_result, SQLSRV_FETCH_ASSOC);
		return $row;
	}	
}