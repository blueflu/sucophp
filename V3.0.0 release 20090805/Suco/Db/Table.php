<?php 

class Suco_Db_Table extends Suco_Db_Table_Abstract
{
	public function __construct()
	{
		if (!$this->_name) {
			require_once 'Suco/Db/Table/Exception.php';
			throw new Suco_Db_Table_Exception(get_class($this).'对象没有绑定数据表');
		}
		parent::__construct($this->_name);
	}
}