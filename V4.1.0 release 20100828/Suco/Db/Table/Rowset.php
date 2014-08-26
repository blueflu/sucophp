<?php

require_once 'Suco/Db/Table/Row/Abstract.php';

class Suco_Db_Table_Rowset extends Suco_Db_Table_Row_Abstract
{
	protected $_table;
	protected $_select;
	
	public function __construct($table, $select, $data)
	{
		if (!$table instanceof Suco_Db_Table_Abstract) {
			throw new Suco_Exception('指定的不是Suco_Db_Table对象');	
		}

		$this->_table = $table;
		$this->_select = $select;
		$this->_data = $data;
	}
	
	public function __call($method, $params)
	{
		if (substr($method, 0, 3) == 'get') {
			if (!method_exists($this->getTable(), $method)) {
				require_once 'Suco/Db/Table/Exception.php';
				throw new Suco_Exception('找不到方法' . get_class($this->getTable()) . '::' . $method);
			}
			$params = $params ? $params : array(0 => $this);
			return call_user_func_array(array($this->getTable(), $method), $params);
		}
	}

    public function current()
    {
        return new Suco_Db_Table_Row($this->_table, current($this->_data));
    }
    
    public function total()
    {
    	return count($this->_data);
    }
	
	public function select()
	{
		return $this->_select;
	}
	
	public function table()
	{
		return $this->_table;
	}
}