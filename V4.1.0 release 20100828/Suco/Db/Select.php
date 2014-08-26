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
	const USE_INDEX		 = 'useindex';

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
    const SQL_USE_INDEX	 = 'USE INDEX';
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
	 * 启动缓存
	 *
	 * @var bool
	 */
	protected $_cache = 1;
	
	
	/*
	 * 构造函数
	 *
	 * @param Suco_Db_Adapter_Abstract $adapter
	 */
	public function __construct(Suco_Db_Adapter_Abstract $adapter)
	{
		$this->setAdapter($adapter);
		$this->init();
	}
	
	public function setAdapter(Suco_Db_Adapter_Abstract $adapter)
	{
		$this->_adapter = $adapter;
		return $this;
	}
	
	public function getAdapter()
	{
		return $this->_adapter;
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
			self::USE_INDEX => array(),
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
		$parts = (isset($col['method']) ? "{$col['method']}({$col['field']})" : $parts);
		$parts = (isset($col['alias']) ? "{$parts} AS {$col['alias']}" : $parts);
		return $parts;
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
		if (preg_match('#[^\']^(.+)\.(.+)$#i', $str, $m)) { //检查引用
			$col['schema'] = $this->getTableAlias(trim($m[1]));
			$col['field'] = trim($m[2]);
		}
		if (!isset($col['field'])) {
			$col['field'] = trim($str);
		}
		if (!isset($col['schema'])) {
			$col['schema'] = $this->getTableAlias(trim($table));
		}
		if (isset($col['method']) && $col['method'] == 'COUNT' && $col['field'] == '*') {
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
	public function alias($name)
	{
		$this->reset(self::COLUMNS);
		$this->from($this->_currentTable . ' AS ' . $name, '*');
		return $this;
	}
	*/
	
	public function distinct($flag = false)
	{
		$this->_parts[self::DISTINCT] = $flag;
		return $this;
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
			preg_match('#\((.*)\)#si', $cols, $m); $tmp = $m[1];
			$cols = str_replace($tmp, '#tmp_sql#', $cols);
			$cols = explode(',', $cols);
			foreach ($cols as $i => $col) {
				$cols[$i] = str_replace('#tmp_sql#', $tmp, $col);
			}
		}
		
		if ($table) {
			$from = $this->_parseTable($table);
			$table = $from['table'];
		}
		foreach ($cols as $col) {
			$column = $this->_parseColumn($col, $table);
			$key = (isset($column['schema']) ? $column['schema'] . '.' : null) . $column['field'];
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
		/* 同表无法连接
		$this->_parts[self::FROM][$from['table']] = $from;
		*/
		$this->_parts[self::FROM][$table] = $from;
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
	 * 添加USE INDEX子句
	 *
	 * @param string $table
	 * @param string $cond
	 * @param string|array $cols
	 *
	 * @return Suco_Db_Select
	 */
	public function useindex($index = null, $table = null)
	{
		if (!$table) { $table = $this->_currentTable; }
		$this->_parts[self::USE_INDEX][$table][] = $index;
		return $this;
	}
	
	/*
	 * 设置缓存
	 *
	 * @param bool $flag
	 *
	 * @return Suco_Db_Select
	 */
	public function cache($flag)
	{
		$this->_cache = $flag;
		return $this;
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
		
		$this->_parts[self::WHERE][] = $this->_where($expr, isset($args[0]) ? $args[0] : null, false);
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
	
	public function order($expr, $table = '')
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
			$this->_parts[self::ORDER][] = $this->_formatColumn($this->_parseColumn($field, $table)) . ' ' . $method;
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
	
	public function match($fields, $keyword, $mode = null, $accurate = 0)
	{
		//$mode = 'WITH QUERY EXPANSION';
		$keyword = $this->_adapter->addQuote($keyword);
		$sql = "MATCH({$fields}) AGAINST({$keyword} {$mode})";
		$this->columns($sql . 'AS match_score');
		$this->where($sql.($accurate ? (' >= '.$accurate) : ''));
		$this->order('match_score DESC');
		
		return $this;
	}
	
	public function resetLimit()
	{
		$this->_parts[self::LIMIT_COUNT] = 0;
		$this->_parts[self::LIMIT_OFFSET] = 0;
		
		return $this;
	}
	
	public function getTotal($field = '*')
	{
		static $cache = array();
		$cacheId = implode(array_keys($this->_parts[self::FROM])) . implode($this->_parts[self::WHERE]);
		
		if (!isset($cache[$cacheId])) {
			$this->reset(array(self::COLUMNS, self::LIMIT_OFFSET, self::LIMIT_COUNT, self::ORDER));
			$cache[$cacheId] = $this->columns("count({$field}) AS result")->fetchCol('result');
			/*
			$where = implode(' ', $this->_parts[self::WHERE]);
			$this->reset(array(self::COLUMNS, self::LIMIT_OFFSET, self::LIMIT_COUNT, self::ORDER, self::WHERE));
			$cache[$cacheId] = $this->columns("SUM(CASE WHEN ".($where ? $where : 1)." THEN 1 ELSE 0 END) as result")->limit(1)->fetchCol('result');
			*/
		}
		return $cache[$cacheId];
	}
	
	public function forUpdate($bool = false)
	{
		$this->_parts[self::FOR_UPDATE] = $bool;
	}
	
	public function getPart($part = null)
	{
		if (!$part) return $this->_parts;
		return $this->_parts[$part];
	}

	public function __call($method, $args)
	{
		if (substr($method, 0, 5) == 'fetch') {
			$sql = $this->__toString();
			if ($this->_cache) {
				//暂时关闭缓存
				//static $caches = array(); $key = md5($sql.$method);
				//if (!isset($caches[$key])) {
					$caches[$key] = $this->_adapter->execute($sql)->$method(isset($args[0]) ? $args[0] : null);
				//}
				return $caches[$key];
			} else {
				return $this->_adapter->execute($sql)->$method(isset($args[0]) ? $args[0] : null);
			}
		}
		if (substr($method, 0, 9) == 'quoteInto') {
			return $this->_adapter->quoteInto($args[0], $args[1]);
		}
		require_once 'Suco/Exception.php';
		throw new Suco_Exception("找不到方法 ".__CLASS__."::{$method}()");
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
			$useindex = isset($this->_parts[self::USE_INDEX][$table['table']]) ? ' USE INDEX('.implode(',', $this->_parts[self::USE_INDEX][$table['table']]).')' : '';
			$froms[] = $this->_formatTable($table) . $useindex;	
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
			$sql[] = $this->_parts[self::LIMIT_COUNT] . ($this->_parts[self::LIMIT_OFFSET] ? ',' . $this->_parts[self::LIMIT_OFFSET] : null);
		}
		
		if ($this->_parts[self::FOR_UPDATE]) {
			$sql[] = SQL_FOR_UPDATE;	
		}
		
		return implode(' ', $sql);
	}
	
}
?>