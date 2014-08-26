<?php
/**
 * MongoDB 数据库驱动
 *
 * @version		3.0 2009/09/01 01:31
 * @author		blueflu (lqhuanle@163.com)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */
require_once 'Suco/Db/Adapter/Abstract.php';

class Suco_Db_Adapter_Mongo extends Suco_Db_Adapter_Abstract
{
	/**
	 * 数据库连接
	 *
	 * @param array $dsn
	 * @param bool $persistent 是否进行持久连接
	 */
	public function connect($host, $port, $user, $pass, $persistent = false)
	{
		$this->_dsn = array(
			'host' => $host,
			'port' => $port,
			'user' => $user,
			'pass' => $pass
		);

		if ($port) {
			$host = "{$host}:{$port}";
		}

		if ($persistent) {
			$this->_linkId = @mysql_pconnect($host, $user, $pass);
		} else {
			$this->_linkId = @mysql_connect($host, $user, $pass, true);
		}

		if (!$this->_linkId) {
			require_once 'Suco/Db/Exception.php';
			throw new Suco_Db_Exception("无法连接数据库服务器. [".mysql_error()."]", 1001);
		}
	}

	/**
	 * 打开指定数据库
	 *
	 * @param string $dnName
	 */
	public function selectdb($dbName)
	{
		$this->_dsn['name'] = $dbName;
		$this->execute("USE `{$dbName}`");
	}

	/**
	 * 执行一条sql查询
	 *
	 * @param string $sql
	 *
	 * @return Suco_Db_Result_Abstract
	 */
	public function execute($sql)
	{
		$beginTime = $this->_getMicrotime();
		$result = mysql_query($sql, $this->_linkId);
		$executeTime = $this->_getMicrotime() - $beginTime;
		$this->_querys[] = array(
			'runtime'	=> $executeTime,
			'query'		=> $sql,
			'result'	=> $this->getAffectedRows(),
		);
		$this->_totalExecuteTime+= $executeTime;
		$this->_totalExecuteQuantity++;

		if (mysql_errno($this->_linkId)) {
			require_once 'Suco/Db/Exception.php';
			throw new Suco_Db_Exception("{$sql} 执行失败. [".mysql_error()."]", 1002);
		}

		require_once 'Suco/Db/Result/Mysql.php';
		return new Suco_Db_Result_Mysql($result);
	}

	/**
	 * 关闭当前数据库连接
	 */
	public function close()
	{
		mysql_close($this->_linkId);
	}

	/**
	 * 启动事务处理
	 */
	public function beginTrans()
	{
		$this->execute("START TRANSACTION");
	}

	/**
	 * 回滚
	 */
	public function rollback()
	{
		$this->execute("ROLLBACK");
	}

	/**
	 * SAVEPOINT
	 */
	public function savepoint($key)
	{
		$this->execute("SAVEPOINT {$key}");
	}

	/**
	 * 提交事务
	 */
	public function commit()
	{
		$this->execute("COMMIT");
	}

	/**
	 * 添加关键字符号
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function addSymbol($string)
	{
		if (false !== strpos($string, '(') || false !== strpos($string, 'AS')
			|| false !== strpos($string, 'as')
			|| false !== strpos($string, '`')) {
			return $string;
		}
		$keywords = explode('.', $string);
		foreach ($keywords as $pos => $keyword) {
			if ('*' == $keyword) {
				continue;
			}
			$keywords[$pos] = '`' . trim($keyword) . '`';
		}

		return implode('.', $keywords);
	}

	/**
	 * 添加记录方法
	 *
	 * @param array $data
	 * @return int
	 */
	public function insert($table, $data)
	{
		if (!$data) { return false; }
		if ($data instanceof Suco_Db_Table_Row) {
			$data = $data->toArray();
		}
		if (!is_array($data)) {
			require_once 'Suco/Db/Exception.php';
			throw new Suco_Db_Exception('被插入记录必须是一个数组');
		}
		foreach ((array)$data as $field => $value) {
			$fields[] = $this->addSymbol($field);
			$values[] = $this->addQuote($value);
		}
		$qStr = sprintf('INSERT INTO %s (%s)VALUES(%s)', $this->addSymbol($table), @implode(',', $fields), @implode(',', $values));
		$this->execute($qStr);

		return $this->getInsertId();
	}

	/**
	 * 更新记录方法
	 *
	 * @param string|array $data
	 * @param string $cond
	 * @return int
	 */
	public function update($table, $data, $cond = 1)
	{
		if (!$data) { return false; }
		if (is_array($data)) {
			foreach ($data as $field => $value) {
				$item[] = $this->addSymbol($field) . '=' . $this->addQuote($value);
			}
			$data = @implode(',', $item);
		}

		$qStr = sprintf('UPDATE %s SET %s WHERE %s', $this->addSymbol($table), $data, $cond);
		$this->execute($qStr);
	}

	/**
	 * 删除记录方法
	 *
	 * @param string $cond
	 * @return int
	 */
	public function delete($table, $cond = 1)
	{
		$qStr = sprintf('DELETE FROM %s WHERE %s', $this->addSymbol($table), $cond);
		$this->execute($qStr);
	}
}
?>