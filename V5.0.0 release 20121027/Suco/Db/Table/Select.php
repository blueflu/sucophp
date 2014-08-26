<?php
/**
 * Suco_Db_Table_Select 表 Select 操作
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */

require_once 'Suco/Db/Select.php';

class Suco_Db_Table_Select extends Suco_Db_Select
{
	protected $_table;

	/**
	 * 构造函数
	 *
	 * @param object $table
	 * @return void
	 */
	public function __construct(Suco_Db_Table_Abstract $table)
	{
		$this->_table = $table;
		parent::__construct($table->getAdapter());
	}

	/**
	 * 映射关系
	 *
	 * @param string $key
	 * @param string $joinType
	 * @return object
	 */
	public function reference($key, $joinType = parent::LEFT_JOIN)
	{
		$mapping = $this->_table->getReferenceMap();
		$reference = $mapping[$key];
		$tableClass = new $reference['class'];
		$joinTable = $tableClass->getTableName();
		$cond = "{$joinTable}.{$reference['target']} = {$this->_table->getTableName()}.{$this->_table->getIdentity()}";
		$cols = isset($reference['columns']) ? $reference['columns'] : null;

		return parent::join($joinTable, $cond, $cols);
	}

	/**
	 * 提取单条记录
	 *
	 * @param string $where
	 * @param string $order
	 * @return object
	 */
	public function fetchRow()
	{
		if (!$this->_parts[self::LIMIT_OFFSET]) {
			$this->limit(1);
		}
		$rowClass = $this->_table->getRowClass();
		return new $rowClass(parent::fetchRow(), $this->_table, true);
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
	public function fetchRows()
	{
		$rowsetClass = $this->_table->getRowsetClass();
		if ($this->getPart(Suco_Db_Select::PAGINATOR)) { //已分页的SQL用COUNT(*)统计
			if ($this->getTotal() < $this->getPart(Suco_Db_Select::LIMIT_COUNT)) {
				$this->limit(0, $this->getPart(Suco_Db_Select::LIMIT_OFFSET));
			}
		}
		return new $rowsetClass(parent::fetchRows(), $this->_table, $this);
	}

	/**
	 * 指定键值提取记录集
	 *
	 * @param string $key 键名 通常是字段名或字段别名
	 * @return object
	 */
	public function fetchOnKey($key)
	{
		$rowsetClass = $this->_table->getRowsetClass();
		return new $rowsetClass(parent::fetchOnKey($key), $this->_table, $this);
	}

	/**
	 * fetchRows 方法的别名
	 *
	 * @return object
	 */
	public function fetchAll()
	{
		return $this->fetchRows();
	}
}