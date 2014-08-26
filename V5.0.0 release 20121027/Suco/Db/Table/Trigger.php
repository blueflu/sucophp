<?php

class Suco_Db_Table_Trigger
{
	protected $_table;

	final public function observer(&$table, $behavior, $data)
	{
		$this->_table = $table;
		if (method_exists($this, $behavior)) {
			call_user_method_array($behavior, $this, $data);
		}
	}
}