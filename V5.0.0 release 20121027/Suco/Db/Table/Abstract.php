<?php
/**
 * Suco_Db_Table_Abstract 表业务抽象
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */

abstract class Suco_Db_Table_Abstract
{
	//protected $_name;
	//protected $_primary;
	/**
	 * 表前缀
	 * @var string
	 */
	protected $_prefix;

	/**
	 * schema
	 * @var string
	 */
	protected $_schema;

	/**
	 * 自增主键
	 * @var string
	 */
	protected $_identity;

	/**
	 * 表字段
	 * @var array
	 */
	protected $_columns;

	/**
	 * 适配器
	 * @var array
	 */
	protected $_adapter;

	/**
	 * 表META信息
	 * @var array
	 */
	protected $_metadata;

	/**
	 * 指定 row 类
	 * @var string
	 */
	protected $_rowClass;

	/**
	 * 指定 rowset 类
	 * @var string
	 */
	protected $_rowsetClass;

	/**
	 * 表默认值
	 * @var array
	 */
	protected $_defaultValues;

	/**
	 * 禁止修改的列
	 * @var array
	 */
	protected $_noModifyColumns;

	/**
	 * 刷新缓存标识
	 * @var bool
	 */
	protected $_refreshCache = false;

	/**
	 * Array2String 分割符
	 * @var string
	 */
	protected $_arraySeparator = ',';


	/**
	 * 关系映射
	 * @var array
	 */
	protected $_referenceMap = array();

	/**
	 * 默认数据库适配器
	 * @var object
	 */
	protected static $_defaultAdapter;

	/**
	 * 构造方法
	 *
	 * @param string $tbName 表名
	 * @param array $options 设置选项
	 */
	public function __construct($tbName = null, $options = array())
	{
		if (!empty($options) && !is_array($options)) {
			$options = array('adapter' => $options);
		}

		if ($tbName) {
			$this->_name = $tbName;
		}

		if (!$this->_name) {
			//require_once 'Suco/Db/Table/Exception.php';
			//throw new Suco_Db_Table_Exception(get_class($this).'对象没有绑定数据表');
		}
		$this->setOptions($options);
		$this->init();
	}

	/**
	 * 初始化方法
	 */
	public function init() {}

	/**
	 * 类配置方法
	 * @example
	 * $tb = new Suco_Db_Table('tbname', array(
	 * 	'columns' => array('id', 'name')
	 * 	'identity' => 'id'
	 * 	......
	 * ));
	 */
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

	/**
	 * 设置映射关系
	 *
	 * @param array $map
	 *
	 * $map 数组结构
	 * @type 链接类型
	 * 	- hasone 一对一
	 *  - hasmany 一对多
	 * @foreign 链接外键
	 * @primary 链接主键
	 * @columns 引用列
	 */
	public function setReferenceMap($map)
	{
		$this->_referenceMap = $map;
	}

	/**
	 * 设置映射关系
	 *
	 * @return array
	 */
	public function getReferenceMap()
	{
		return $this->_referenceMap;
	}

	/**
	 * 设置禁止修改的列
	 *
	 * @param array
	 */
	public function setNoModifyColumns($columns)
	{
		$this->_noModifyColumns = $columns;
	}

	/**
	 * 返回禁止修改的列
	 *
	 * @return array
	 */
	public function getNoModifyColumns()
	{
		return $this->_noModifyColumns;
	}

	/**
	 * 设置列
	 *
	 * @return array
	 */
	public function setColumns($columns)
	{
		$this->_columns = $columns;
	}

	/**
	 * 返回列
	 *
	 * @return array
	 */
	public function getColumns()
	{
		if (!$this->_columns) {
			$this->_setupMetadata();
		}
		return $this->_columns;
	}

	/**
	 * 设置SCHEMA
	 *
	 * @return string
	 */
	public function setSchema($schema)
	{
		$this->_schema = $schema;
		return $this;
	}

	/**
	 * 返回SCHEMA
	 *
	 * @return string
	 */
	public function getSchema()
	{
		return $this->_schema;
	}

	/**
	 * 设置 row 类名
	 *
	 * @param string $class
	 */
	public function setRowClass($class)
	{
		$this->_rowClass = $class;
		return $this;
	}

	/**
	 * 返回 row 类名
	 *
	 * @return string
	 */
	public function getRowClass()
	{
		if (!$this->_rowClass) {
			require_once 'Suco/Db/Table/Row.php';
			$this->setRowClass('Suco_Db_Table_Row');
		}
		return $this->_rowClass;
	}

	/**
	 * 设置 rowset 类名
	 *
	 * @param string $class
	 * @return string
	 */
	public function setRowsetClass($class)
	{
		$this->_rowsetClass = $class;
		return $this;
	}

	/**
	 * 返回 rowset 类名
	 *
	 * @return string
	 */
	public function getRowsetClass()
	{
		if (!$this->_rowsetClass) {
			require_once 'Suco/Db/Table/Rowset.php';
			$this->setRowsetClass('Suco_Db_Table_Rowset');
		}
		return $this->_rowsetClass;
	}

	/**
	 * 返回表对象名称
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * 设置表对象名称
	 *
	 * @param string $name
	 * @return object;
	 */
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}

	/**
	 * 返回数据表名称
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return ($this->_schema ? $this->_schema . '.' : null) . ($this->_prefix ? $this->_prefix : null) . $this->_name;
	}

	/**
	 * 设置自增字段
	 *
	 * @param string $field
	 */
	public function setIdentity($field)
	{
		$this->_identity = $field;
	}

	/**
	 * 返回自增字段
	 *
	 * @return string
	 */
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

	/**
	 * 返回最后插入的ID
	 *
	 * @return int
	 */
	public function getInsertId()
	{
		return $this->getAdapter()->getInsertId();
	}

	/**
	 * 设置默认值
	 *
	 * @param array $values
	 * @return object
	 */
	public function setDefaultValues($values)
	{
		$this->_defaultValues = $values;
		return $this;
	}

	/**
	 * 返回默认值
	 *
	 * @return array
	 */
	public function getDefaultValues()
	{
		if (!$this->_defaultValues) {
			$this->_setupMetadata();
		}
		return $this->_defaultValues;
	}

	/**
	 * 设置Metadata
	 *
	 * @param array $metadata
	 * @return object
	 */
	public function setMetadata($metadata)
	{
		$this->_metadata = $metadata;
		return $this;
	}

	/**
	 * 返回Metadata
	 *
	 * @return array
	 */
	public function getMetadata()
	{
		if (!$this->_metadata) {
			$this->_setupMetadata();
		}
		return $this->_metadata;
	}

	/**
	 * fetchRows 方法的别名
	 *
	 * @return object
	 */
	public function fetchAll($where = null, $order = null, $count = null, $offset = null)
	{
		return $this->fetchRows($where, $order, $count, $offset);
	}

	/**
	 * 提取记录集
	 *
	 * @param string $where
	 * @param string $order
	 * @param int $count
	 * @param int $offset
	 * @return object
	 */
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

	/**
	 * 提取单条记录
	 *
	 * @param string $where
	 * @param string $order
	 * @return object
	 */
	public function fetchRow($where = null, $order = null)
	{
		//$this->_setupMetadata();
		$select = $this->select();
		if ($where) {
			$select->where($where);
		}
		if ($order) {
			$select->order($order);
		}
		$select->limit(1);
		return $select->fetchRow();
	}

	/**
	 * 提取单列多项记录
	 *
	 * @param string $col 要提取的列名
	 * @param string $where 指定条件
	 * @return array
	 */
	public function fetchCols($col, $where = null)
	{
		$select = $this->select();
		if ($where) {
			$select->where($where);
		}
		return $select->fetchCols($col);
	}

	/**
	 * 提取单列项记录
	 *
	 * @param string $col 要提取的列名
	 * @param string $where 指定条件
	 * @return mixed
	 */
	public function fetchCol($col, $where = null)
	{
		$select = $this->select();
		if ($where) {
			$select->where($where);
		}
		return $select->fetchCol($col);
	}

	/**
	 * 指定键值提取记录集
	 *
	 * @param string $key 键名 通常是字段名或字段别名
	 * @return object
	 */
	public function fetchOnKey($key)
	{
		return $this->select()->fetchOnKey($key);
	}

	/**
	 * 模拟创建一条空记录
	 * 通常配合 Suco_Db_Table_Row 对象的 save 方法一起使用
	 * 如：<code>
	 * $tb = $db->table('product');
	 * $row = $tb->create()
	 * $row->title = 'test';
	 * $row->price = 100;
	 * $row->save(); //执行save时，会将此记录insert到数据库中，否则不会操作数据库
	 * </code>
	 *
	 * @param string $key 键名 通常是字段名或字段别名
	 * @return object
	 */
	public function create()
	{
		$rowClass = $this->getRowClass();
		return new Suco_Db_Table_Row(null, $this, false);
	}

	/**
	 * 查询方法
	 * 根据传入条件返回记录或记录集
	 * 如：<code>
	 * $tb = $db->table('product');
	 *
	 * #参数为Int类型时，将以主键ID查询一笔记录并返回 Suco_Db_Table_Row
	 * $row = $tb->find(2); //Query: where id = 2
	 *
	 * #参数为Array类型时，将以数组的键值查询一笔记录并返回 Suco_Db_Table_Row
	 * $row = $tb->find(array('price' => 100, 'title' => 'test'));
	 * //Query: where price = 100 AND title = 'test'
	 *
	 * #参数为String类型时，将以此作为where条件查询多笔记录并返回 Suco_Db_Table_Rows
	 * $rows = $tb->find('price > 100'); //Query: where price > 100
	 * </code>
	 *
	 * @param mixed $cond 查询条件
	 * @return object
	 */
	public function find($cond)
	{
		static $cache = array();
		$key = is_array($cond) ? http_build_query($cond) : $cond;
		if (!isset($cache[$key]) || $this->_refreshCache) {
			if (intval($cond) == $cond) {
				$identity = $this->getIdentity();
				$cond = $this->getAdapter()->quoteInto("{$identity} = ?", (int)$cond);
				$cache[$key] = $this->fetchRow($cond);
			} elseif (is_array($cond)) {
				$parts = array();
				foreach($cond as $field => $value) {
					$parts[] = $this->getAdapter()->quoteInto("{$field} = ?", $value);
				}
				$cond = implode(' AND ', $parts);
				$cache[$key] = $this->fetchRow($cond);
			} else {
				$cache[$key] = $this->fetchRows($cond);
			}
		}
		return $cache[$key];
	}

	/**
	 * 设置表别名，并返回 Suco_Db_Select 对象
	 *
	 * @param string $alias
	 * @return object
	 */
	public function alias($alias)
	{
		require_once 'Suco/Db/Table/Select.php';
		$select = new Suco_Db_Table_Select($this);
		$select->from($this->getTableName() . ' AS ' . $alias);
		return $select;
	}

	/**
	 * 开启SELECT语句，返回 Suco_Db_Select 对象
	 *
	 * @param string|array $cols
	 * @return object
	 */
	public function select($cols = '*')
	{
		require_once 'Suco/Db/Table/Select.php';
		$select = new Suco_Db_Table_Select($this);
		$select->from($this->getTableName(), $cols);
		return $select;
	}

	/**
	 * 执行INSERT语句插入记录
	 *
	 * @param array $data 数组的键名对应字段名，键值对应字段值
	 * @return int 返回主键ID
	 */
	public function insert($data)
	{
		$this->validation($data, 'insert');
		
		$data = $this->insertFilter($data);
		$this->_behavior('_insertBefore', array($data));
		$this->getAdapter()->insert($this->getTableName(), $data);
		$this->_behavior('_insertAfter', array($data, $this->getInsertId()));
		$this->_refreshCache = 1;

		return $this->getInsertId();
	}

	/**
	 * 执行UPDATE语句更新记录
	 * 例：<code>
	 * $db->table('product')->update('price = 200', 'title = \'test\''); #将标题为test的价格改为200
	 * #也可以这样写
	 * $db->table('product')->update(array('price' => 200), 'title = ?', 'test');
	 * </code>
	 *
	 * @param array|string $data 数组的键名对应字段名，键值对应字段值
	 * @param string $cond 设定条件，支持参数化查询条件
	 * @return void
	 */
	public function update($data, $cond = null)
	{
		$args = func_get_args();
		if (isset($args[2])) {
			$cond = $this->getAdapter()->quoteInto($cond, $args[2]);
		}
		if (is_numeric($cond)) {
			throw new Suco_Db_Exception(__CLASS__.'::update 方法禁止传入int型');
		}

		if (is_array($data)) {
			$this->validation($data, 'update');
			foreach ((array)$this->_noModifyColumns as $col) {
				if (isset($data[$col])) {
					require_once 'Suco/Db/Table/Exception.php';
					throw new Suco_Db_Table_Exception("禁止修改{$col}列");
				}
			}
			$data = $this->updateFilter($data);
		}

		$this->_behavior('_updateBefore', array($data, $cond));
		$this->getAdapter()->update($this->getTableName(), $data, $cond);
		$this->_behavior('_updateAfter', array($data, $cond));
		$this->_refreshCache = 1;
	}

	/**
	 * 以主键ID为条件，执行UPDATE语句更新记录
	 * 例：<code>
	 * $db->table('product')->updateById('title = \'test\'', 2); 将主键ID=2的标题改为test
	 * </code>
	 *
	 * @param array|string $data 数组的键名对应字段名，键值对应字段值
	 * @param int $id 设定主键ID值
	 * @return void
	 */
	public function updateById($data, $id)
	{
		$identity = $this->getIdentity();
		$cond = $this->getAdapter()->quoteInto("{$identity} = ?", (int)$id);
		$this->update($data, $cond);
	}

	/**
	 * 执行DELETE语句删除记录
	 * 例：<code>
	 * $db->table('product')->delete('price > 100'); 删除价格大于100的记录
	 * </code>
	 *
	 * @param string $cond 设定条件，支持参数化查询条件
	 * @return void
	 */
	public function delete($cond = null)
	{
		$args = func_get_args();
		if (isset($args[1])) {
			$cond = $this->getAdapter()->quoteInto($cond, $args[1]);
		}
		if (is_numeric($cond)) {
			throw new Suco_Db_Exception(__CLASS__.'::update 方法禁止传入int型');
		}

		$this->_behavior('_deleteBefore', $cond);
		$this->getAdapter()->delete($this->getTableName(), $cond);
		$this->_behavior('_deleteAfter', $cond);
		$this->_refreshCache = 1;
	}

	/**
	 * 以主键ID为条件，执行DELETE语句删除记录
	 * 例：<code>
	 * $db->table('product')->deleteById(2); 删除主键ID=2的记录
	 * </code>
	 * @param int $id 设定主键ID值
	 * @return void
	 */
	public function deleteById($id)
	{
		$identity = $this->getIdentity();
		$cond = $this->getAdapter()->quoteInto("{$identity} = ?", (int)$id);
		$this->delete($cond);
	}

	/**
	 * 执行COUNT查询
	 * 例：<code>
	 * $tb = $db->table('product');
	 * $tb->count('price > 100'); //SELECT count(*) AS result FROM product WHERE price > 100
	 * </code>
	 *
	 * @param string $cond 设定条件
	 * @return int
	 */
	public function count($cond)
	{
		$args = func_get_args();
		if (isset($args[1])) {
			$cond = $this->getAdapter()->quoteInto($cond, $args[1]);
		}
		return $this->select()->where($cond)->getTotal();
	}

	/**
	 * 数据有效性验证方式 (保留方法)
	 *
	 * @param array $data 传入数据
	 * @param string $method 调用方法 insert或update
	 * @return void
	 */
	public function validation($data, $method)
	{
	}

	/**
	 * 执行 insert 前的数据过滤
	 *
	 * @param array $data 传入数据
	 * @return array
	 */
	public function insertFilter($data)
	{
		//添加默认值
		$_defaultValues = $this->getDefaultValues();
		foreach ($_defaultValues as $field => $value) {
			if (!isset($data[$field])) {
				$data[$field] = $value;
			}
		}
		return $this->filter($this->inputFilter($data));
	}

	/**
	 * 执行 update 前的数据过滤
	 *
	 * @param array $data 传入数据
	 * @return array
	 */
	public function updateFilter($data) { return $this->filter($this->inputFilter($data)); }

	/**
	 * 执行 insert和update 前的数据过滤
	 *
	 * @param array $data 传入数据
	 * @return array
	 */
	public function inputFilter($data) { return $data; }

	/**
	 * 输出记录前的数据过滤
	 *
	 * @param array $data 传入数据
	 * @return array
	 */
	public function outputFilter($data) { return $data; }

	/**
	 * 数据过滤,此方法在调用insert和update前会被自动调用
	 */
	public function filter($data)
	{
		$metadata = $this->getMetadata();
		$columns = array();
		foreach ($metadata as $field => $col) {
			//扔掉多余的列
			if (!isset($data[$field])) { continue; }
			//if (isset($col['IDENTITY'])) { continue; }

			$columns[$field] = $data[$field];

			//强制转换数值类型
			if (isset($columns[$field]) && $this->getAdapter()->isNumeric($col['TYPE'])) {
				$columns[$field] = floatval($columns[$field]);
			} else {
				$columns[$field] = strval($columns[$field]);
			}

			//强制转换数组
			if (is_array($data[$field])) {
				foreach ($data[$field] as $key => $val) {
					if (!$val) unset($data[$field][$key]);
				}
				$columns[$field] = implode($this->_arraySeparator, $data[$field]);
			}

			//强制截取
			if (isset($columns[$field]) && isset($col['LENGTH']) && strlen($columns[$field]) > $col['LENGTH']) {
				$columns[$field] = substr($columns[$field], 0, $col['LENGTH']);
			}

		}
		return $columns;
	}

	/**
	 * 解析参数化查询
	 *
	 * @param string $expr 表达式
	 * @param array $params 参数
	 * @return string
	 */
	public function quoteInto($expr, $params)
	{
		return Suco_Db_Adapter_Abstract::quoteInto($expr, $params);
	}

	/**
	 * 指定字段返回一个唯一数
	 *
	 * @param string $column
	 * @param int $length
	 * @return int
	 */
	public function getUniqueCode($column = '', $length = 8)
	{
		$metadata = $this->getMetadata();
		$result = $this->select('MAX('.($column ? $column : $this->_identity).') AS result')->fetchCol('result') + 1;
		if (strlen($result) < $length) {
			$s = $length - strlen($result);
			$z = str_repeat('0', $s);
			$result = date('Ymd') . $z . $result;
		} elseif (strlen($result) > $length) {
			$result = substr($result, $length * -1);
			$result = date('Ymd') . $result;
		}

		return $result;
	}

	/**
	 * 行为插件，当系统发现有定义行为方法时将自动调用方法
	 * 当前系统内置以下行为
	 * _insertBefore, _insertAfter
	 * _updateBefore, _updateAfter
	 * _deleteBefore, _deleteAfter
	 * 如：<code>
	 * class Product extends Suco_Db_Table
	 * {
	 * 		protected function _deleteAfter()
	 * 		{
	 * 			//删除产品后执行关联操作, 如删除产品评论，相册等
	 * 		}
	 * }
	 * </code>
	 *
	 *
	 * @param string $behavior
	 * @param array $params
	 * @return int
	 */
	protected function _behavior($behavior, $params)
	{
		if (method_exists($this, $behavior)) {
			call_user_func_array(array($this, $behavior), (array)$params);
		}
		//查找触发器
		foreach ($this->getTriggers() as $trigger) {
			$trigger->observer($this, $behavior, $params);
		}
	}

	/**
	 * 获取 Metadata
	 *
	 * @return void
	 */
	protected function _setupMetadata()
	{
		if ($this->_metadata) return;
		$this->setMetadata($this->getAdapter()->getDescribeTable($this->getTableName()));
		foreach ($this->_metadata as $field => $col) {
			$this->_columns[] = $field;
			if (isset($col['PRIMARY']) && !$this->_primary) {
				$this->_primary[] = $field;
			}
			if (isset($col['IDENTITY']) && !$this->_identity) {
				$this->_identity = $field;
			}
			if (isset($col['DEFAULT']) && !$this->_defaultValues) {
				$this->_defaultValues[$field] = $col['DEFAULT'];
			}
			if ($col['NOT_NULL'] && !isset($col['DEFAULT']) && !isset($col['IDENTITY'])) { //非空字段,且不是自增字段添加默认值
				$this->_defaultValues[$field] = $this->getAdapter()->isNumeric($col['TYPE']) ? 0 : '';
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