<?php
/*
 * Suco_Db_Select 类, 封装了部分常用查询操作
 *
 *
 * @version		3.0 2009/09/01 01:31
 * @author		blueflu (lqhuanle@163.com)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */

class Suco_Db_Select
{
    const DISTINCT       = 'distinct';
    const COLUMNS        = 'columns';
    const FROM           = 'from';
    const WHERE          = 'where';
    const GROUP          = 'group';
    const HAVING         = 'having';
    const ORDER          = 'order';
    const LIMIT_COUNT    = 'limitcount';
    const LIMIT_OFFSET   = 'limitoffset';
    const FOR_UPDATE     = 'forupdate';

    const INNER_JOIN     = 'inner join';
    const LEFT_JOIN      = 'left join';
    const RIGHT_JOIN     = 'right join';
    const FULL_JOIN      = 'full join';
    const CROSS_JOIN     = 'cross join';
    const NATURAL_JOIN   = 'natural join';

    const SQL_WILDCARD   = '*';
    const SQL_SELECT     = 'SELECT';
    const SQL_FROM       = 'FROM';
    const SQL_WHERE      = 'WHERE';
    const SQL_DISTINCT   = 'DISTINCT';
    const SQL_GROUP_BY   = 'GROUP BY';
    const SQL_ORDER_BY   = 'ORDER BY';
    const SQL_HAVING     = 'HAVING';
    const SQL_FOR_UPDATE = 'FOR UPDATE';
    const SQL_AND        = 'AND';
    const SQL_AS         = 'AS';
    const SQL_OR         = 'OR';
    const SQL_ON         = 'ON';
    const SQL_ASC        = 'ASC';
    const SQL_DESC       = 'DESC';
	const SQL_LIMIT		 = 'LIMIT';
	
	/*
	 * 数据库驱动
	 *
	 * @var Suco_Db_Adapter_Abstract_Abstract
	 */
	protected $_adpater;
	
	/*
	 * 构造查询的各个部分
	 *
	 * @var array
	 */
	protected $_parts;
	
	/*
	 * 当前查询的主表
	 *
	 * @var string
	 */
	protected $_currentTable;
	
	
	/*
	 * 构造函数
	 *
	 * @param Suco_Db_Adapter_Abstract $adapter
	 */
	public function __construct(Suco_Db_Adapter_Abstract $adapter)
	{
		$this->_adapter = $adapter;
		$this->init();
	}
	
	/*
	 * 初始化所有查询参数
	 */
	public function init()
	{
		$this->_parts = array(
			self::DISTINCT => false,
			self::COLUMNS => array(),
			self::FROM => array(),
			self::WHERE => array(),
			self::GROUP => array(),
			self::HAVING => array(),
			self::ORDER => array(),
			self::LIMIT_COUNT => 0,
			self::LIMIT_OFFSET => 0,
			self::FOR_UPDATE => false
		);
	}
	
	public function reset($part = null)
	{
		if ($part) {
			if ($part == 'limit') {
				$this->_parts[self::LIMIT_COUNT] = 0;
				$this->_parts[self::LIMIT_OFFSET] = 0;
			} elseif (is_array($part)) {
				foreach ($part as $item) {
					$this->reset($item);
				}
			} else {
				$this->_parts[$part] = array();
			}
		} else {
			$this->init();
		}
		return $this;
	}
	
	public function setTableAlias($table, $alias)
	{
		$this->_alias[$table] = $alias;
	}
	
	/*
	 * 返回数据表的唯一别名
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	public function getTableAlias($table)
	{
		return isset($this->_alias[$table]) ? $this->_alias[$table] : $table;
	}
	
	/*
	 * 格式化字段
	 *
	 * @param string $col
	 *
	 * @return string
	 */
	protected function _formatColumn($col)
	{
		$parts = $this->_adapter->addSymbol($col['field'], @$col['schema']);
		$parts = (isset($col['method']) ? "{$col['method']}({$parts})" : $parts);
		$parts = (isset($col['alias']) ? "{$parts} AS {$col['alias']}" : $parts);
		
		return $parts;
	}
	
	/*
	 * 解析字段名, 别名, 函数
	 *
	 * @param string $str
	 * @param string $table
	 *
	 * @return array
	 */
	protected function _parseColumn($str, $table = null)
	{
		if (preg_match('#^(.+)\s+' . self::SQL_AS . '\s+(.+)$#i', $str, $m)) { //检查别名
			$str = trim($m[1]);
			$col['alias'] = trim($m[2]);
		}
		if (preg_match('#^(.+)\((.*)\)$#i', $str, $m)) { //检查方法
			$str = trim($m[2]);
			$col['method'] = strtoupper(trim($m[1]));
		}
		if (preg_match('#^(.+)\.(.+)$#i', $str, $m)) { //检查引用
			$col['schema'] = $this->getTableAlias(trim($m[1]));
			$col['field'] = trim($m[2]);
		}
		if (!isset($col['field'])) {
			$col['field'] = trim($str);
		}
		if (!isset($col['schema'])) {
			$col['schema'] = $this->getTableAlias(trim($table));
		}
		if (@$col['method'] == 'COUNT' && $col['field'] == '*') {
			unset($col['schema']);
		}
		
		return $col;

	}
	
	/*
	 * 解析表名, 表别名
	 *
	 * @param string $str
	 *
	 * @return array
	 */
	protected function _parseTable($str)
	{
		if (preg_match('#^(.+)\s+' . self::SQL_AS . '\s+(.+)$#i', $str, $m)) { //检查别名
			$str = trim($m[1]);
			$from['alias'] = trim($m[2]);
		}
		if (preg_match('#^(.+)\.(.+)$#i', $str, $m)) { //检查引用
			$from['schema'] = trim($m[1]);
			$from['table'] = trim($m[2]);
		}
		if (!isset($from['table'])) {
			$from['table'] = $str;
		}
		if (isset($from['alias'])) {
			$this->setTableAlias($from['table'], $from['alias']);
		}
		
		return $from;
	}
	
	/*
	 * 格式化表名
	 *
	 * @param array $from
	 * @param bool $current
	 *
	 * @return string
	 */
	protected function _formatTable($from, $current = false)
	{
		$parts = $this->_adapter->addSymbol($from['table'], isset($from['schema']) ? $from['schema'] : null);
		$parts = (isset($from['alias']) ? "{$parts} AS {$from['alias']}" : $parts);
		if ($this->_currentTable != $from['table']) {
			$parts = $from['join'] ? "{$from['join']} {$parts}" : $parts;
			$parts = $from['cond'] ? "{$parts} ON({$from['cond']})" : $parts;
		}
		
		return $parts;
	}

	public function distinct($flag = false)
	{
		$this->_parts[self::DISTINCT] = $flag;
	}
	
	/*
	 * 添加字段子句
	 *
	 * @param string|array $cols
	 * @param string $table
	 *
	 * @return Suco_Db_Select
	 */
	public function columns($cols, $table = null)
	{
		if (!$cols) { return; }
		if (!is_array($cols)) {
			$cols = explode(',', $cols);
		}
		
		if ($table) {
			$from = $this->_parseTable($table);
			$table = $from['table'];
		}
		foreach ($cols as $col) {
			$column = $this->_parseColumn($col, $table);
			$key = (isset($column['table']) ? $column['table'] . '.' : null) . $column['field'];
			$this->_parts[self::COLUMNS][$key] = $column;
		}
		
		return $this;
	}
	
	/*
	 * 添加查询表子句
	 *
	 * @param string $table
	 * @param string|array $cols
	 *
	 * @return Suco_Db_Select
	 */
	public function from($table, $cols = null)
	{
		$from = $this->_parseTable($table);
		$this->_currentTable = $from['table'];
		return $this->join($table, null, $cols, self::INNER_JOIN);
	}
	
	/*
	 * 添加JOIN连接子句
	 *
	 * @param string $table
	 * @param string $cond
	 * @param string|array $cols
	 * @param string $joinType
	 *
	 * @return Suco_Db_Select
	 */
	public function join($table, $cond = null, $cols = null, $joinType = self::LEFT_JOIN)
	{
		$from = $this->_parseTable($table);
		$from['join'] = strtoupper($joinType);
		$from['cond'] = $cond;
		$this->_parts[self::FROM][$from['table']] = $from;
		$this->columns($cols, $from['table']);
		return $this;
	}

	/*
	 * 添加LEFT JOIN子句
	 *
	 * @param string $table
	 * @param string $cond
	 * @param string|array $cols
	 *
	 * @return Suco_Db_Select
	 */
	public function leftJoin($table, $cond, $cols = null)
	{
		return $this->join($table, $cond, $cols, 'LEFT JOIN');
	}
	
	/*
	 * 添加RIGHT JOIN子句
	 *
	 * @param string $table
	 * @param string $cond
	 * @param string|array $cols
	 *
	 * @return Suco_Db_Select
	 */
	public function rightJoin($table, $cond, $cols = null)
	{
		return $this->join($table, $cond, $cols, 'RIGHT JOIN');
	}
	
	/*
	 * 添加INNER JOIN子句
	 *
	 * @param string $table
	 * @param string $cond
	 * @param string|array $cols
	 *
	 * @return Suco_Db_Select
	 */
	public function innerJoin($table, $cond, $cols = null)
	{
		return $this->join($table, $cond, $cols, 'INNER JOIN');
	}

	/*
	 * 添加WHERE条件子句, 以 AND 连接
	 *
	 * @return Suco_Db_Select
	 */
	public function where()
	{
		$args = func_get_args();
		$expr = array_shift($args);
		$this->_parts[self::WHERE][] = $this->_where($expr, isset($args[0]) ? $args[0] : null, true);
		return $this;
	}
	
	/*
	 * 添加WHERE条件子句, 以 OR 连接
	 *
	 * @return Suco_Db_Select
	 */
	public function orWhere()
	{
		$args = func_get_args();
		$expr = array_shift($args);
		
		$this->_parts[self::WHERE][] = $this->_where($expr, $args[0], false);
		return $this;
	}

	/*
	 * 构造WHERE条件
	 *
	 * @return Suco_Db_Select
	 */
	protected function _where($expr, $params, $bool = true)
	{
		$cond = $this->_adapter->quoteInto($expr, $params);
		if ($this->_parts[self::WHERE]) {
			if ($bool) {
				$cond = self::SQL_AND . ' ' . $cond;
			} else {
				$cond = self::SQL_OR . ' ' . $cond;	
			}
		}
		return $cond;
	}
	
	public function having()
	{
		$args = func_get_args();
		$expr = array_shift($args);
		
		$this->_parts[self::HAVING][] = $this->_having($expr, $args, true);
		return $this;
	}
	
	public function orHaving()
	{
		$args = func_get_args();
		$expr = array_shift($args);
		
		$this->_parts[self::HAVING][] = $this->_having($expr, $args, false);
		return $this;
	}
	
	protected function _having($expr, $args, $bool = true)
	{
		$cond = $this->_adapter->quoteInto($expr, $args);
		if ($this->_parts[self::HAVING]) {
			if ($bool) {
				$cond = self::SQL_AND . ' ' . $cond;
			} else {
				$cond = self::SQL_OR . ' ' . $cond;	
			}
		}
		return $cond;
	}
	
	public function group($cols)
	{
		if (!is_array($cols)) {
			$cols = explode(',', $cols);
		}
		
		foreach ($cols as $col) {
			$this->_parts[self::GROUP][] = $this->_formatColumn($this->_parseColumn($col));
		}
		return $this;
	}
	
	public function order($expr)
	{
		if (!is_array($expr)) {
			$expr = explode(',', $expr);
		}
		
		foreach ($expr as $str) {
			if (preg_match('/(.*\W)(' . self::SQL_ASC . '|' . self::SQL_DESC . ')\b/si', $str, $m)) {
				$field = trim($m[1]);
				$method =  trim($m[2]);
			} else {
				$field = trim($str);
				$method = self::SQL_ASC;
			}
			$this->_parts[self::ORDER][] = $this->_formatColumn($this->_parseColumn($field, $this->_currentTable)) . ' ' . $method;
		}
		return $this;
	}
	
	public function paginator($pageSize, $currentPage)
	{
		$this->_parts[self::LIMIT_COUNT] = $pageSize * ($currentPage >= 1 ? $currentPage - 1 : $currentPage);
		$this->_parts[self::LIMIT_OFFSET] = $pageSize;
		
		return $this;
	}
	
	public function limit($count, $offset = 0)
	{
		$this->_parts[self::LIMIT_COUNT] = $count;
		$this->_parts[self::LIMIT_OFFSET] = $offset;
		
		return $this;
	}
	
	public function resetLimit()
	{
		$this->_parts[self::LIMIT_COUNT] = 0;
		$this->_parts[self::LIMIT_OFFSET] = 0;
		
		return $this;
	}
	
	public function forUpdate($bool = false)
	{
		$this->_parts[self::FOR_UPDATE] = $bool;
	}

	public function __call($method, $args)
	{
		if (substr($method, 0, 5) == 'fetch') {
			return $this->_adapter->execute($this->__toString())->$method(isset($args[0]) ? $args[0] : null);
		}
		if (substr($method, 0, 9) == 'quoteInto') {
			return $this->_adapter->quoteInto($args[0], $args[1]);
		}
		require_once 'Suco/Exception.php';
		throw new Suco_Exception("找不到方法 ".__CLASS__."::{$method}()");
	}
	
	public function getCount()
	{
		$this->reset(array(self::COLUMNS, self::LIMIT_OFFSET, self::LIMIT_COUNT));
		return $this->columns("count(*) AS result")->fetchCol('result');
	}

	public function __toString()
	{
		if (!$this->_parts[self::FROM]) {
			require_once 'Suco/Exception.php';
			throw new Suco_Db_Exception("未指定查询数据表 [FROM]");	
		}
		
		$sql[] = self::SQL_SELECT;
		if ($this->_parts[self::DISTINCT]) {
			$sql[] = self::SQL_DISTINCT;
		}
		foreach ($this->_parts[self::COLUMNS] as $col) {
			$cols[] = $this->_formatColumn($col);
		}
		$sql[] = isset($cols) ? implode(', ', $cols) : '*';
		$sql[] = self::SQL_FROM;
		foreach ($this->_parts[self::FROM] as $table) {
			$froms[] = $this->_formatTable($table);	
		}
		$sql[] = implode(' ', $froms);
		
		if ($this->_parts[self::WHERE]) {
			$sql[] = self::SQL_WHERE;
			$sql[] = implode(' ', $this->_parts[self::WHERE]);
		}
		
		if ($this->_parts[self::GROUP]) {
			$sql[] = self::SQL_GROUP_BY;
			$sql[] = implode(', ', $this->_parts[self::GROUP]);
		}
		
		if ($this->_parts[self::HAVING]) {
			$sql[] = self::SQL_HAVING;
			$sql[] = implode(' ', $this->_parts[self::HAVING]);
		}
		
		if ($this->_parts[self::ORDER]) {
			$sql[] = self::SQL_ORDER_BY;
			$sql[] = implode(', ', $this->_parts[self::ORDER]);
		}
		
		if ($this->_parts[self::LIMIT_COUNT] || $this->_parts[self::LIMIT_OFFSET]) {
			$sql[] = self::SQL_LIMIT;
			$sql[] = $this->_parts[self::LIMIT_COUNT] . (isset($this->_parts[self::LIMIT_OFFSET]) ? ',' . $this->_parts[self::LIMIT_OFFSET] : null);
		}
		
		if ($this->_parts[self::FOR_UPDATE]) {
			$sql[] = SQL_FOR_UPDATE;	
		}
		
		return implode(' ', $sql);
	}
	
}
?>