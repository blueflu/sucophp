<?php
/**
 * Suco_Db_Table_Row 数据行对象
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */
require_once 'Suco/Object.php';

class Suco_Db_Table_Row extends Suco_Object
{
	protected $_columns;
	protected $_raw;
	protected $_clean;
	protected $_modified;

	protected $_stored = false;
	protected $_table;

	/**
	 * 构造函数
	 *
	 * @param array $row
	 * @param string|object $table
	 * @return void
	 */
	public function __construct($row, $table = null)
	{
		if ($table instanceof Suco_Db_Table_Abstract) {
			$this->setTable($table);
		} elseif ($table) {
			$this->setTable(new $table());
		}

		if ($row) {
			$this->_stored = true;
			$this->_raw = $row;
			$this->_clean = $row;
			if ($this->getTable()) {
				$this->_data = $this->getTable()->outputFilter($row);
			} else {
				$this->_data = $row;
			}
			#$this->_columns = $this->getTable()->getColumns();
		}
	}

	/**
	 * __call 魔术方法
	 *
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 */
	public function __call($method, $params)
	{
		if (!method_exists($this->getTable(), $method)) {
			require_once 'Suco/Db/Table/Exception.php';
			throw new Suco_Exception('找不到方法' . get_class($this->getTable()) . '::' . $method);
		}
		$params = array_merge(array(0 => $this), $params);
		return call_user_func_array(array($this->getTable(), $method), $params);
	}

	/**
	 * 检查行对象是否存在
	 *
	 * @return bool
	 */
	public function exists()
	{
		return $this->_raw ? 1 : 0;
	}

	/**
	 * 保存行数据
	 */
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
			$this->getTable()->update($data, $cond);
			return $data;
		} else {
			$id = $this->getTable()->getIdentity();
			$data[$id] = $this->getTable()->insert(array_merge((array)$this->_clean, (array)$data));
			return $data;
		}
	}

	/**
	 * 删除行数据
	 *
	 * @return void
	 */
	public function remove()
	{
		$identity = $this->getTable()->getIdentity();
		$cond = $this->getTable()->getAdapter()->quoteInto($identity . ' = ?', (int)$this->_raw[$identity]);
		$this->getTable()->delete($cond);
		//$this->getTable()->deleteById((int)$this->_raw[$identity]);
	}

	/**
	 * 设置一项数据
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value)
	{
		$this->_clean[$key] = $value;
		$this->_data[$key] = $value;
		$this->_modified[$key] = $value;
	}

	/**
	 * 返回一项数据
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		static $cache = array();
		//如果找到映射关系.则输出
		if ($this->getTable()) {
			$referenceMap = $this->getTable()->getReferenceMap();

			if (isset($referenceMap[$key])) {
				$target = isset($referenceMap[$key]['source']) ? $referenceMap[$key]['source'] : $this->getTable()->getIdentity();
				if (!isset($cache[$key][$this->_raw[$target]])) {
					$cache[$key][$this->_raw[$target]] = $this->getReference($referenceMap[$key]);
				}
				return $cache[$key][$this->_raw[$target]];
			}
		}

		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}

	/**
	 * 设置行对象相关的表对象
	 *
	 * @param object $table
	 * @return void
	 */
	public function setTable($table)
	{
		$this->_table = $table;
	}

	/**
	 * 返回相关的表对象
	 *
	 * @return object
	 */
	public function getTable()
	{
		return $this->_table;
	}

	/**
	 * 解析映射关键并返回相关结果
	 *
	 * @param array $map
	 * @return object
	 */
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
				$table = Suco_Model::factory($class);
				$row = $table->select($columns)
							 ->where($where)
							 ->where("{$target} = ?", $this->_raw[$source])
							 ->order($order)
							 ->fetchRow();
				//绑定外键
				if (@$this->_raw[$target]) {
					$row->$source = $this->_raw[$target];
				}
				return $row;
			case 'hasmany':
				$table = Suco_Model::factory($class);
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
