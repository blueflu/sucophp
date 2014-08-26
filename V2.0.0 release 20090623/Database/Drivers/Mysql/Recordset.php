<?php
class SCN_Db_Recordset
{
	function SCN_Db_Recordset(&$result)
	{
		$this->resule = &$result;
	}
	
	function fetchAll()
	{
		while ($row = @mysql_fetch_array($this->resule, MYSQL_ASSOC)) {
			$recordset[] = $row;
		}

		return $recordset;
	}
	
	function fetchOnKey($key)
	{
		while ($row = mysql_fetch_array($this->resule, MYSQL_ASSOC)) {
			$recordset[$row[$key]] = $row;
		}

		return $recordset;
	}
	
	function fetchRow()
	{
		return @mysql_fetch_assoc($this->resule);
	}
	
	function fetchCol($field)
	{
		$row = mysql_fetch_array($this->resule, MYSQL_ASSOC);
		return $row[$field];
	}
	
	function fetchCols($field)
	{
		while ($row = mysql_fetch_array($this->resule, MYSQL_ASSOC)) {
			$recordset[] = $row[$field];
		}
		return $recordset;
	}
}
?>