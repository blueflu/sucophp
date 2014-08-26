<?php

require_once 'Suco/Db/Result/Abstract.php';

class Suco_Db_Result_Mssql extends Suco_Db_Result_Abstract
{
	public function free()
	{
		if (is_resource($this->_result)) {
			mssql_free_result($this->_result);	
		}
	}
	
	public function fetchRow()
	{
		$row = mssql_fetch_assoc($this->_result);
		return $row;
	}	
}