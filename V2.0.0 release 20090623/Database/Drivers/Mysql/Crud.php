<?php
/*
 * SCN_Db_Crud #2008/6/14 2:39:40#
 * 封装SQL操作
 *
 * @version				1.0
 * @package				Helper
 * @author				blueflu (lqhuanle@163.com)
 * @copyright			Copyright (c) 2008, Suconet, Inc.
 * @license				http://www.suconet.com/license
 * -----------------------------------------------------------
 * @example:
		...
 * @modified:
 * 2009/06/23 18:08 filterData() 增加了Not NULL 字段自动填值功能 --- by [Blueflu]
 */

class SCN_Db_Crud
{
	protected $_tbName, $tbAlias, $dbo;
	protected $sTable, $sAlias, $sFields, $sWhere, $sJion, $sGroup, $sLimit, $sOrder;
	
	private $insertSql = 'INSERT INTO %s (%s)VALUES(%s)';
	private $updateSql = 'UPDATE %s SET %s WHERE %s';
	private $deleteSql = 'DELETE FROM %s WHERE %s';
	
	function __construct($tbName, $tbAlias, &$dbo)
	{
		$this->dbo = &$dbo;
		$this->setTable($tbName, $tbAlias);
		$this->setFields($this->tbAlias . '.*');
	}
	
	function init()
	{
		$this->sFields = $this->tbAlias . '.*';
		$this->sWhere = NULL;
		$this->sOrder = NULL;
		$this->sLimit = NULL;
		$this->sGroup = NULL;
		$this->sJoin = NULL;
	}
	
	function addSpecialChar(&$str, $key = 0, $prefix = '')
	{
		if ( '*' == $str || false !== strpos($str, '(') || false !== strpos($str, 'AS') || false !== strpos($str, 'as') || false !== strpos($str, '.') || false !== strpos($str, '`')) return $str;
		$prefix = ($prefix ? (false !== strpos($prefix, '`') ? $prefix : '`' . $prefix . '`') . '.' : '');
		$str = $prefix . '`' . trim($str) . '`';

		return $str;
	}
	
	function addEscapeString($string)
	{
		if(!get_magic_quotes_gpc() && is_string($string)){
			return $string !== NULL ? '\'' . mysql_escape_string($string) . '\'' : 'NULL';
		} else {
			return '\'' . $string . '\'';	
		}
	}
	
	function setTable($tbName, $tbAlias = 0)
	{
		$this->tbName = $tbName;
		$this->tbAlias = $tbAlias ? $tbAlias : $tbName;
		$this->sTable = $this->addSpecialChar($tbName) . ($tbAlias ? ' AS ' . $tbAlias : '');
	}
	
	function setAlias($tbAlias)
	{
		$this->tbAlias = $tbAlias ? $tbAlias : $tbName;
	}
	
	function setFields($fields)
	{
		if (!$fields) { return; }
		if (!is_array($fields)) $fields = explode(',', $fields);
		array_walk($fields, array($this, 'addSpecialChar'), $this->tbAlias);
		$this->sFields = implode(', ', $fields);
	}
	
	function setWhere($condition, $alias = 0, $annex = 0)
	{
		if (!$condition) { $this->sWhere = NULL; return; }
		if ($this->sJoin) {
			//$sAlias = $alias ? $alias : $this->tbAlias;
			$find = '/(\S+.) (\=|\!\=|\>|\<|\>\=|\<\=|like|in)/i';
			$replace = ($alias ? $alias . '.' : '') . '\\1 \\2';
			$condition = preg_replace($find, $replace, $condition);
		}
		
		$this->sWhere = $annex && $this->sWhere ? $this->sWhere . ' AND ' . $condition : 'WHERE ' . $condition;
	}
	
	function setOrder($fields)
	{
		if (!$fields) { $this->sOrder = NULL; return; }
		is_array($fields) || $fields = explode(',', $fields);
		foreach ($fields as $k => $field) {
			list($f, $m) = explode(' ', trim($field));
			$fields[$k] = $this->addSpecialChar($f, 0, $this->tbAlias) . ' ' . $m;
		}
		$this->sOrder = 'ORDER BY ' . implode(', ', $fields);
	}
	
	function setLimit($limit, $start = 0)
	{
		if (!$limit) { $this->sLimit = NULL; return; }
		$this->sLimit = 'LIMIT ' . ($start ? ($start - 1) * $limit : 0) . ',' . $limit;
	}
	
	function setJoin($table, $pk, $fk, $fields = 0, $alias = 0, $method = 'LEFT JOIN')
	{
		$table = $this->dbo->tbPrefix . $table;
		$this->sJoin .= ' ' . $method . ' ' . $this->addSpecialChar($table) . ($alias ? ' AS ' . $alias : '');
		$this->sJoin .= ' ON (' . $this->tbAlias . '.' . $pk . ' = ' . ($alias ? $alias : $table) . '.' . $fk . ')';
		
		if ($fields) {
			$fields = explode(',', $fields);
			array_walk($fields, array($this, 'addSpecialChar'), $alias ? $alias : $table);
			$this->sFields .= ', ' . implode(',', $fields);
		} else {
			$this->sFields .= ', ' . ($alias ? $alias : $table) . '.*';
		}
	}
	
	function setGroup($field)
	{
		$this->sGroup = 'GROUP BY ' . $field;
	}

	function getFields($table = 0)
	{
		static $fields;
		if (!$fields[$this->tbName]) {
			$table || $table = $this->tbName;
			$fields[$this->tbName] = $this->dbo->execute('SHOW columns FROM ' . $table);
			$fields[$this->tbName] = $fields[$this->tbName]->fetchAll();
		}
		return $fields[$this->tbName];
	}
	
	function filterData($data, $autoValue = 1)
	{
		$fields = $this->getFields($this->tbName);
		foreach ($fields as $field) {
			if ($data[$field['Field']]) {
				$fData[$field['Field']] = $data[$field['Field']];
			}
			
			if ($autoValue) {
				if ($field['Null'] == 'NO' && $field['Key'] != 'PRI' && !$data[$field['Field']]) {
					$type = preg_replace('#\(.*\)#', '', $field['Type']);
					if ($type == 'int' || $type == 'tinyint' || $type == 'smallint' || $type == 'mediumint' || $type == 'decimal') {
						$fData[$field['Field']] = 0;
					} else {
						$fData[$field['Field']] = '';
					}
				}
			}
		}
		return $fData;
	}
	
	function insert($data, $table = 0)
	{
		$table || $table = $this->tbName;
		foreach ((array)$data as $field => $value) {
			$fields[] = $this->addSpecialChar(trim($field));
			$values[] = $this->addEscapeString(trim($value));
		}
		
		$qStr = sprintf($this->insertSql, $table, @implode(',', $fields), @implode(',', $values));
		$this->dbo->execute($qStr);
		$this->insertId = mysql_insert_id($this->dbo->linkId);
		
		return $this->insertId;
	}
	
	function update($data, $condition = 1, $table = 0)
	{
		$table || $table = $this->tbName;

		if (is_array($data)) {
			foreach ($data as $field => $value) {
				if (!is_null(trim($value)) && trim($value) !== '' ) {
					$item[] = $this->addSpecialChar(trim($field)) . '=' . $this->addEscapeString(trim($value));
				} else {
					$item[] = $this->addSpecialChar(trim($field)) . '= NULL';
				}
			}
			$qStr = sprintf($this->updateSql, $table, @implode(',', $item), $condition);
		} elseif (is_string($data)) {
			$qStr = sprintf($this->updateSql, $table, $data, $condition);
		}
		$this->dbo->execute($qStr);
	}
	
	function delete($condition, $table = 0)
	{
		$table || $table = $this->tbName;
		$qStr = sprintf($this->deleteSql, $table, $condition);
		$this->dbo->execute($qStr);
	}
	
	function select()
	{
		$qStr = 'SELECT ' . $this->sFields . ' ' .
				'FROM ' . $this->sTable . ' ' . 
				($this->sJoin ? $this->sJoin . ' ' : '') . 
				($this->sWhere ? $this->sWhere . ' ' : '') .
				($this->sGroup ? $this->sGroup . ' ' : '') .
				($this->sOrder ? $this->sOrder . ' ' : '') .
				($this->sLimit ? $this->sLimit . ' ' : '');
		$this->result = $this->dbo->execute($qStr);	
		return $this->result;
	}
	
	function count()
	{
		$qStr = 'SELECT count(*) as RecordCount ' .
				'FROM ' . $this->sTable . ' ' . 
				($this->sJoin ? $this->sJoin . ' ' : '') . 
				($this->sWhere ? $this->sWhere . ' ' : '') .
				($this->sGroup ? $this->sGroup . ' ' : '') .
				($this->sOrder ? $this->sOrder . ' ' : '') .
				($this->sLimit ? $this->sLimit . ' ' : '');
		$this->result = $this->dbo->execute($qStr);	
		$count = $this->result->fetchCol('RecordCount');
		return $count[0];
	}
}
?>