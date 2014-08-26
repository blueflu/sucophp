<?php

require_once 'Suco/Db/Result/Abstract.php';

class Suco_Db_Result_Mysql extends Suco_Db_Result_Abstract
{
	public function free()
	{
		if (is_resource($this->_result)) {
			mysql_free_result($this->_result);	
		}
	}
	
	public function fetchRow()
	{
		$row = @mysql_fetch_assoc($this->_result);

		if (get_magic_quotes_gpc()) { //解除转义
			if (is_array($row)) {
				foreach ($row as $i => $val) {
					$row[$i] = stripslashes($val);
				}
			} else {
				$row = stripslashes($row);
			}
		}
		return $row;
	}	
}