<?php

class Suco_Db_Table_Abstract 
{
	protected $_name;
	protected $_schema;
	protected $_primary;
	protected $_identity;
	protected $_columns;
	protected $_adapter;
	protected $_metadata;
	protected $_rowClass;
	protected $_rowsetClass;
	protected $_defaultValues = array();
	protected $_noModifyColumns = array();
	
	
	/**
	 * 关系映射
	 * 
	 * @type 链接类型
	 * 	- hasone 一对一
	 *  - hasmany 一对多
	 * @foreign 链接外键
	 * @primary 链接主键
	 * @columns 引用列
	 *  
	 * @var array
	 */
	protected $_referenceMap = array();
	
	protected static $_defaultAdapter;
	
	public function __construct($table = null, $options = array())
	{
		if (!empty($options) && !is_array($options)) {
			$options = array('adapter' => $options);
		}

		if ($table) {
			$this->_name = $table;
		}
		$this->setOptions($options);
	}
	
	public function setOptions($options)
	{
		foreach ($options as $method => $param) {
			$method = 'set' . ucfirst($method);
			if (method_exists($this, $method)) {
				$this->$method($param);
			}
		}
		return $this;
	}
	
	public function setReferenceMap($map)
	{
		$this->_referenceMap = $map;
	}
	
	public function getReferenceMap()
	{
		return $this->_referenceMap;
	}
	
	public function setNoModifyColumns($columns)
	{
		$this->_noModifyColumns = $columns;
	}
	
	public function getNoModifyColumns()
	{
		return $this->_noModifyColumns;
	}
	
	public function setColumns($columns)
	{
		$this->_columns = $columns;
	}
	
	public function getColumns()
	{
		if (!$this->_columns) {
			$this->_setupMetadata();
		}
		return $this->_columns;
	}
	
	public function setSchema($schema)
	{
		$this->_schema = $schema;
		return $this;
	}
	
	public function getSchema()
	{
		return $this->_schema;	
	}
	
	public function setRowClass($class)
	{
		$this->_rowClass = $class;
		return $this;
	}
	
	public function getRowClass()
	{
		if (!$this->_rowClass) {
			require_once 'Suco/Db/Table/Row.php';
			$this->setRowClass('Suco_Db_Table_Row');
		}
		return $this->_rowClass;
	}
	
	public function setRowsetClass($class)
	{
		$this->_rowsetClass = $class;
		return $this;
	}
	
	public function getRowsetClass()
	{
		if (!$this->_rowsetClass) {
			require_once 'Suco/Db/Table/Rowset.php';
			$this->setRowsetClass('Suco_Db_Table_Rowset');
		}
		return $this->_rowsetClass;	
	}
	
	public function setTableName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	public function getTableName()
	{
		return ($this->_schema ? $this->_schema . '.' : null) . $this->_name;
	}
	
	public function setDefaultAdapter(Suco_Db_Adapter_Abstract $adapter)
	{
		self::$_defaultAdapter = $adapter;
	}
	
	public function getDefaultAdapter()
	{
		return self::$_defaultAdapter;
	}
	
	public function setAdapter(Suco_Db_Adapter_Abstract $adapter)
	{
		$this->_adapter = $adapter;
	}
	
	public function getAdapter()
	{
		if (!$this->_adapter && !self::$_defaultAdapter) {
			require_once 'Suco/Db/Exception.php';
			throw new Suco_Db_Exception('没有指定数据库适配器');
		}
		return $this->_adapter ? $this->_adapter : self::$_defaultAdapter;
	}
	
	public function setIdentity($id)
	{
		$this->_identity = $id;	
	}
	
	public function getIdentity()
	{
		if (!$this->_identity) {
			if ($this->_primary) {
				$this->setIdentity(is_array($this->_primary) ? $this->_primary[0] : $this->_primary);
			} else {
				$this->_setupMetadata();
			}
		}
		
		return $this->_identity;
	}
	
	public function getInsertId()
	{
		return $this->getAdapter()->getInsertId();
	}
	
	public function setDefaultValues($value)
	{
		$this->_defaultValues = $value;
	}
	
	public function getDefaultValues()
	{
		if (!$this->_defaultValues) {
			$this->_setupMetadata();
		}
		return $this->_defaultValues;
	}
	
	public function setMetadata($metadata)
	{
		$this->_metadata = $metadata;
		return $this;
	}
	
	public function getMetadata()
	{
		if (!$this->_metadata) {
			$this->_setupMetadata();
		}
		return $this->_metadata;
	} 
	
	public function fetchAll($where = null, $order = null, $count = null, $offset = null)
	{
		return $this->fetchRows($where, $order, $count, $offset);
	}
	
	public function fetchRows($where = null, $order = null, $count = null, $offset = null)
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
		return $select->fetchRows();	
	}
	
	public function fetchRow($where = null)
	{
		$this->_setupMetadata();
		$select = $this->select();
		if ($where) {
			$select->where($where);
		}
		$select->limit(0, 1);
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
	
	public function fetchOnKey($key)
	{
		return $this->select()->fetchOnKey($key);
	}
	
	public function create()
	{
		$rowClass = $this->getRowClass();
		return new Suco_Db_Table_Row($this, null, false);
	}
	
	public function find($cond)
	{
		static $cache = array();
		$key = is_array($cond) ? http_build_query($cond) : $cond;
		if (!isset($cache[$key])) {
			if (is_int($cond)) {
				$identity = $this->getIdentity();
				$cond = $this->getAdapter()->quoteInto("{$identity} = ?", $cond);
				$cache[$key] = $this->fetchRow($cond);
			} elseif (is_array($cond)) {
				$parts = array();
				foreach($cond as $field => $value) {
					$parts[] = $this->getAdapter()->quoteInto("{$field} = ?", $value);
				}
				$cond = implode(' AND ', $parts);
				$cache[$key] = $this->fetchRow($cond);
			} else {
				$cache[$key] = $this->fetchAll($cond);
			}
		}
		return $cache[$key];
	}
	
	public function select($cols = '*')
	{
		require_once 'Suco/Db/Table/Select.php';
		$select = new Suco_Db_Table_Select($this);
		$select->from($this->_name, $cols);
		return $select;
	}
	
	public function insert($data)
	{
		$this->_behavior('_insertBefore', $data);
		$this->getAdapter()->insert($this->getTableName(), $this->insertFilter($data));
		$this->_behavior('_insertAfter', $data);
	}
	
	public function update($data, $cond = 1)
	{
		if (is_array($data)) {
			foreach ($this->_noModifyColumns as $col) {
				if (isset($data[$col])) {
					require_once 'Suco/Db/Table/Exception.php';
					throw new Suco_Db_Table_Exception("禁止修改{$col}列");
				}
			}
		}
		
		if (is_int($cond)) {
			$identity = $this->getIdentity();
			$cond = $this->getAdapter()->quoteInto("{$identity} = ?", $cond);
		}
		$this->_behavior('_updateBefore', $data);
		$this->getAdapter()->update($this->getTableName(), is_array($data) ? $this->updateFilter($data) : $data, $cond);
		$this->_behavior('_updateAfter', $data);
	}
	
	public function delete($cond = 1)
	{
		$this->_behavior('_deleteBefore', $cond);
		if (is_int($cond)) {
			$identity = $this->getIdentity();
			$cond = $this->getAdapter()->quoteInto("{$identity} = ?", $cond);
		}
		$this->getAdapter()->delete($this->getTableName(), $cond);
		$this->_behavior('_deleteAfter', $cond);
	}
	
	public function insertFilter($data)
	{
		//添加默认值
		$_defaultValues = $this->getDefaultValues();
		foreach ($_defaultValues as $field => $value) {
			if (!isset($data[$field])) {
				$data[$field] = $value;
			}
		}
		return $this->inputFilter($data); 
	}
	
	public function updateFilter($data) { return $this->inputFilter($data); }
	public function outputFilter($data) { return $data; }
	
	public function inputFilter($data)
	{
		$metadata = $this->getMetadata();
		$columns = array();
		foreach ($metadata as $col) {
			$name = $col['FIELD'];
			//扔掉多余的列和自增字段
			if (isset($data[$name]) && !isset($col['IDENTITY'])) {
				$columns[$name] = $data[$name];
			}
			
			//强制转换数值类型
			if (isset($columns[$name]) && $this->getAdapter()->isNumeric($col['TYPE'])) {
				$columns[$name] = intval($columns[$name]);
			}
		}
		return $columns;
	}
	
	protected function _behavior($behavior, $params)
	{
		if (method_exists($this, $behavior)) {
			$this->$behavior($params);
		}
	}
	
	protected function _setupMetadata()
	{
		if ($this->_metadata) return;
		$this->setMetadata($this->getAdapter()->getDescribeTable($this->getTableName()));
		foreach ($this->_metadata as $col) {
			$this->_columns[] = $col['FIELD'];
			if (isset($col['PRIMARY']) && !$this->_primary) {
				$this->_primary[] = $col['FIELD'];
			}
			if (isset($col['IDENTITY']) && !$this->_identity) {
				$this->_identity = $col['FIELD'];
			}
			if (isset($col['DEFAULT']) && !$this->_defaultValues) {
				$this->_defaultValues[$col['FIELD']] = $col['DEFAULT'];
			}
			if (!$col['NULL'] && !isset($col['DEFAULT']) && !isset($col['IDENTITY'])) { //非空字段,且不是自增字段添加默认值
				$this->_defaultValues[$col['FIELD']] = $this->getAdapter()->isNumeric($col['TYPE']) ? 0 : '';
			}
		}
		if (is_array($this->_primary) && count($this->_primary) == 1) {
			$this->_primary = $this->_primary[0];
		}
		if (!$this->_identity) {
			$this->setIdentity(is_array($this->_primary) ? $this->_primary[0] : $this->_primary);
		}
	}
}