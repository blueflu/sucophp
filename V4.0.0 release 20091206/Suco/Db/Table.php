<?php
class Suco_Db_Table
{
	protected $_name;
	protected $_primary;
	protected $_prefix;
	
	protected static $_adapter;
	protected static $_fullName;

	public function __construct($name)
	{
		$this->_name = $name;
		if (!$this->_name) {
			require_once 'Suco/Db/Exception.php';
			throw new Suco_Db_Exception('[' . get_class($this) . '] 模型没有绑定数据表');
		}
		if (!$this->_prefix) {
			$this->_prefix = self::$_adapter->getTbPrefix();
		}
	}
	
	public function setPrimary($primary)
	{
		$this->_primary = $primary;
	}
	
	public function getPrimary()
	{
		return $this->_primary;
	}
	
	public function setAdapter(Suco_Db_Adapter_Abstract $adapter)
	{
		self::$_adapter = $adapter;
	}
	
	public function getAdapter()
	{
		return self::$_adapter;
	}
	
	public function select($cols = '*')
	{
		require_once 'Suco/Db/Select.php';
		$select = new Suco_Db_Select($this->getAdapter());
		$select->from($this->_prefix . $this->_name, $cols);

		return $select;
	}
	
	public function insert($data)
	{
		$data = $this->_filter($data, 'insert');
		return self::$_adapter->insert($this->_prefix . $this->_name, $data);
	}
	
	public function update($data, $cond = 1)
	{
		if (is_array($data)) {
			$data = $this->_filter($data, 'update');
		}
		
		if (is_int($cond)) {
			if (!$this->_primary) {
				require_once 'Suco/Db/Exception.php';
				throw new Suco_Db_Exception('没有绑定主键');
			}
			$cond = $this->getAdapter()->quoteInto("{$this->_primary} = ?", $cond);
		}
		self::$_adapter->update($this->_prefix . $this->_name, $data, $cond);
	}
	
	public function delete($cond = 1)
	{
		if (is_int($cond)) {
			if (!$this->_primary) {
				require_once 'Suco/Db/Exception.php';
				throw new Suco_Db_Exception('没有绑定主键');
			}
			$cond = $this->getAdapter()->quoteInto("{$this->_primary} = ?", $cond);
		}
		self::$_adapter->delete($this->_prefix . $this->_name, $cond);
	}
	
	public function find($cond)
	{
		if (is_int($cond)) {
			if (!$this->_primary) {
				require_once 'Suco/Db/Exception.php';
				throw new Suco_Db_Exception('没有绑定主键');
			}
			$cond = $this->getAdapter()->quoteInto("{$this->_primary} = ?", $cond);
		} elseif (is_array($cond)) {
			$parts = array();
			foreach($cond as $field => $value) {
				$parts[] = $this->getAdapter()->quoteInto("{$field} = ?", $value);
			}
			$cond = implode(' AND ', $parts);
		}
		
		require_once 'Suco/Db/Table/Row.php';
		return new Suco_Db_Table_Row($this, $this->fetchRow($cond), true);
	}
	
	public function create()
	{
		require_once 'Suco/Db/Table/Row.php';
		return new Suco_Db_Table_Row($this, null, false);
	}
	
	protected function _filter($data, $method)
	{
		$this->_validate($data, $method);
		$cols = self::$_adapter->getDescribeTable($this->_prefix . $this->_name);
		$fields = array();
		foreach ($cols as $col) {
			$field = $col['field'];
			if (isset($data[$field])) {
				$fields[$field] = $data[$field];
			}
			
			//强制转换数值类型
			if (isset($data[$field]) && self::$_adapter->isNumeric($col['type']) && !isset($col['identity'])) {
				$fields[$field] = intval($fields[$field]);
			}
			
			//非空字段且不是自增字段,当值为空时自动填值
			if (!$col['null'] && !isset($data[$field]) && !isset($col['identity']) && $method == 'insert') {
				if (self::$_adapter->isNumeric($col['type'])) {
					$fields[$field] = isset($col['default']) ? $col['default'] : 0;
				} else {
					$fields[$field] = isset($col['default']) ? $col['default'] : '';
				}
			}
		}
		
		return $fields;
	}

	/*
	 * 数据有效性检查
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function _validate($data)
	{
		$cols = self::$_adapter->getDescribeTable($this->_prefix . $this->_name);
		foreach ($cols as $col) {
			$field = $col['field'];
			//长度检查
			if (isset($data[$field]) && isset($col['length']) && $col['length'] < strlen($data[$field])) { 
				throw new Suco_Db_Exception("字段[{$field}] 长度不能超过{$col['length']}个字符.");
			}
			
			//类型检查
			if (isset($data[$field]) && $data[$field] && self::$_adapter->isNumeric($col['type']) && !is_numeric($data[$field]) ) {
				throw new Suco_Db_Exception("字段[{$field}] 数据类型不匹配.");
			}
			
			//检查主键是否为空
			if (isset($col['primary']) && !isset($col['identity']) && empty($data[$field])) {
				throw new Suco_Db_Exception("字段[{$field}] 是主键,不能为空.");
			}
		}
	}

	public function fetchAll($where = null, $order = null, $count = null, $offset = null)
	{
		$select = $this->select();
		if ($where) {
			$select->where($where);
		}
		if ($order) {
			$select->order($order);
		}
		if ($count) {
			$select->limit($count, $offset);
		}
		return $select->fetchAll();
	}
	
	public function fetchOnKey($key)
	{
		return $this->select()->fetchOnKey($key);			
	}
	
	public function fetchRow($where = null)
	{
		$select = $this->select();
		if ($where) {
			$select->where($where);
		}
		return $select->fetchRow();
	}
	
	public function fetchCols($col, $where = null)
	{
		$select = $this->select();
		if ($where) {
			$select->where($where);
		}
		return $select->fetchCols($col);
	}
	
	public function fetchCol($col, $where = null)
	{
		$select = $this->select();
		if ($where) {
			$select->where($where);
		}
		return $select->fetchCol($col);
	}
	
}
?>