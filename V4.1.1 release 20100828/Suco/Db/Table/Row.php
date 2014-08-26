<?php

require_once 'Suco/Db/Table/Row/Abstract.php';

class Suco_Db_Table_Row extends Suco_Db_Table_Row_Abstract
{
	protected $_columns;
	protected $_raw;
	protected $_clean;
	protected $_modified;
	
	protected $_stored = false;
	protected $_table;
	
	public function __construct($table, $row = array())
	{
		if ($table instanceof Suco_Db_Table_Abstract) {
			$this->setTable($table);
		} else {
			$this->setTable(new $table());
		}
		
		if ($row) {
			$this->_stored = true;
			$this->_raw = $row;
			$this->_clean = $row;
			$this->_data = $this->getTable()->outputFilter($row);
			#$this->_columns = $this->getTable()->getColumns();
		}
	}
	
	public function __call($method, $params)
	{
		if (!method_exists($this->getTable(), $method)) {
			require_once 'Suco/Db/Table/Exception.php';
			throw new Suco_Exception('找不到方法' . get_class($this->getTable()) . '::' . $method);
		}
		$params = array_merge(array(0 => $this), $params);
		return call_user_func_array(array($this->getTable(), $method), $params);
	}
	
	public function exists()
	{
		return $this->_raw ? 1 : 0;
	}
	
	public function save($data = null)
	{
		if ($this->_stored)	{
			if (empty($this->_raw)) {
				require_once 'Suco/Db/Table/Exception.php';
				throw new Suco_Db_Table_Exception('被更新的记录不存在');
			}
			$data = array_merge((array)$this->_modified, (array)$data);
			$identity = $this->getTable()->getIdentity();
			$cond = $this->getTable()->getAdapter()->quoteInto($identity . ' = ?', (int)$this->_raw[$identity]);
			return $this->getTable()->update($data, $cond);
			//$this->getTable()->updateById($data, $this->_raw[$identity]);
		} else {
			return $this->getTable()->insert(array_merge((array)$this->_clean, (array)$data));
		}
	}
	
	public function remove()
	{
		$identity = $this->getTable()->getIdentity();
		$cond = $this->getTable()->getAdapter()->quoteInto($identity . ' = ?', (int)$this->_raw[$identity]);
		$this->getTable()->delete($cond);
		//$this->getTable()->deleteById((int)$this->_raw[$identity]);
	}
	
	public function set($key, $value)
	{
		$this->_clean[$key] = $value;
		$this->_data[$key] = $value;
		$this->_modified[$key] = $value;
	}
	
	public function get($key)
	{
		static $cache = array();
		//如果找到映射关系.则输出
		$referenceMap = $this->getTable()->getReferenceMap();
		
		if (isset($referenceMap[$key])) {
			$target = isset($referenceMap[$key]['source']) ? $referenceMap[$key]['source'] : $this->getTable()->getIdentity();
			if (!isset($cache[$key][$this->_raw[$target]])) {
				$cache[$key][$this->_raw[$target]] = $this->getReference($referenceMap[$key]);
			}
			return $cache[$key][$this->_raw[$target]];
		}
		
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}
	
	public function setTable($table)
	{
		$this->_table = $table;
	}
	
	public function getTable()
	{
		return $this->_table;
	}
	
	public function getReference($map)
	{
		$class = $map['class'];
		$source = isset($map['source']) ? $map['source'] : $this->getTable()->getIdentity();
		$target = $map['target'];
		$where = isset($map['where']) ? $map['where'] : 1;
		$order = isset($map['order']) ? $map['order'] : $this->getTable()->getIdentity().' ASC';
		$columns = isset($map['columns']) ? $map['columns'] : '*';
		
		if (!isset($this->_raw[$source]) && $this->_stored) {
			require_once 'Suco/Db/Table/Exception.php';
			throw new Suco_Db_Table_Exception("外键{$source}不存在");
		}
		
		switch ($map['type']) {
			case 'hasone':
				$table = new $class();
				$row = $table->select($columns)
							 ->where($where)
							 ->where("{$target} = ?", $this->_raw[$source])
							 ->order($order)
							 ->fetchRow();
				//绑定外键
				$row->$source = $this->_raw[$target];
				return $row;
			case 'hasmany':
				$table = new $class();
				return $table->select($columns)
							 ->where($where)
							 ->where("{$target} IN (".($this->_raw[$source] ? $this->_raw[$source] : 0).")")
							 ->order($order)
							 ->fetchRows();
		}
		
		require_once 'Suco/Db/Table/Exception.php';
		throw new Suco_Db_Table_Exception('不支持映射类型' . $map['type']);
	}
}
