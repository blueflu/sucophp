<?php
/**
 * SQL Server 2005 以上版本的 数据库驱动
 *
 * @version		3.0 2009/09/01 01:31
 * @author		blueflu (lqhuanle@163.com)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */
require_once 'Suco/Db/Adapter/Abstract.php';

class Suco_Db_Adapter_Sqlsrv extends Suco_Db_Adapter_Abstract
{
	/**
	 * 指定数据库的数值类型
	 *
	 * @var array
	 */
	protected $_numericDataTypes = array('int', 'mediumint', 'smallint', 'bigint', 'tinyint', 'double', 'float', 'decimal');

	/**
	 * 设置数据库使用字符集
	 *
	 * @param string $charset
	 */
	public function setCharset($charset)
	{
		//$version = $this->getVersion();
		/*
		$version = 5;
		if ($version >= 5) {
			$this->execute("SET NAMES {$charset}");
		} else {
			$this->execute("SET character_set_client={$charset}");
			$this->execute("SET character_set_connection={$charset}");
		}
		*/
	}

	/**
	 * 返回最后一次插入ID
	 */
	public function getInsertId()
	{
		//return mysql_insert_id($this->_linkId);
	}

	/**
	 * 返回当前数据库中的所有数据表
	 *
	 * @return array
	 */
	public function getTableList()
	{
		$tables = $this->execute('SHOW TABLES')->fetchCols('Tables_in_'.$this->_dsn['name']);

		return $tables;
	}

	/**
	 * 返回指定数据表的结构
	 *
	 * 字段属性说明:
	 * field[string]		字段名
	 * unsigned[bool]		是否唯一
	 * type[string]			字段类型
	 * length[int]			字段长度
	 * precision[int]		整数位数
	 * scale[int]			小数位数
	 * primary[bool]		主键
	 * extra[string]		扩展
	 * identity[bool]		是否为ID
	 * null[bool]			是否允许为空
	 * default[string]		默认值
	 *
	 * @param string $table
	 * @return array
	 */
	public function getDescribeTable($table)
	{
		static $fd = array();
		if (!isset($fd[$table])) {
			$list = $this->execute("DESCRIBE {$table}")->fetchAll();
			foreach ($list as $k => $item) {
				$d[$k]['FIELD'] = $item['Field'];
				if (preg_match('/unsigned/', $item['Type'])) {
					$d[$k]['UNSIGNED'] = true;
				}
				if (preg_match('/^(\w+)\((\d+)\)/', $item['Type'], $s)) {
					$d[$k]['TYPE'] = $s[1];
					$d[$k]['LENGTH'] = $s[2];
				} else if (preg_match('/^decimal\((\d+),(\d+)\)/', $item['Type'], $s)) {
					$d[$k]['TYPE'] = 'decimal';
					$d[$k]['PRECISION'] = $s[1];
					$d[$k]['SCALE'] = $s[2];
				} else {
					$d[$k]['TYPE'] = $item['Type'];
				}
				if ($item['Key'] == 'PRI') {
					$d[$k]['PRIMARY'] = true;
					if ($item['Extra'] == 'auto_increment') {
						$d[$k]['IDENTITY'] = true;
					}
				}
				$d[$k]['NULL'] = $item['Null'] == 'YES' ? 1 : 0;
				if ($item['Default']) { $d[$k]['DEFAULT'] = $item['Default']; }
			}
			$fd[$table] = $d;
		}
		return $fd[$table];
	}

	/**
	 * 返回查询影响条数
	 *
	 * @return string
	 */
	public function getAffectedRows($result)
	{
		//sqlsrv_num_rows
		return @sqlsrv_rows_affected($result);
	}

	/**
	 * 返回当前数据库版本
	 *
	 * @return string
	 */
	public function getVersion()
	{
		static $version;
		if (!$version) {
			$version = $this->execute("SELECT VERSION() AS version")->fetchCol('version');
			$version = number_format($version, 1);
		}

		return $version;
	}

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

		$this->_linkId = sqlsrv_connect($host, array('UID' => $user, 'PWD' => $pass, 'CharacterSet' => 'UTF-8'));

		if (!$this->_linkId) {
			require_once 'Suco/Db/Exception.php';
			throw new Suco_Db_Exception("无法连接数据库服务器. ", 1001);
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
		$this->execute("USE [{$dbName}]");
	}

	/**
	 * 执行一条sql查询
	 *
	 * @param string $sql
	 *
	 * @return Suco_Db_Result_Abstract
	 */
	public function execute($sql, $return = true)
	{
		$beginTime = $this->_getMicrotime();
		$result = sqlsrv_query($this->_linkId, $sql);

		$executeTime = $this->_getMicrotime() - $beginTime;
		$this->_querys[] = array(
			'runtime'	=> $executeTime,
			'query'		=> $sql,
			'result'	=> $this->getAffectedRows($result),
		);
		$this->_totalExecuteTime+= $executeTime;
		$this->_totalExecuteQuantity++;
		if (!$result) {
			$errors = sqlsrv_errors();
			require_once 'Suco/Db/Exception.php';
			throw new Suco_Db_Exception("{$sql} 执行失败. [".$errors[0]['message']."]", 1002);
		}

		require_once 'Suco/Db/Result/Mysql.php';
		return new Suco_Db_Result_Sqlsrv($result);
	}

	/**
	 * 关闭当前数据库连接
	 */
	public function close()
	{
		sqlsrv_close($this->_linkId);
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
	 * @param string $value
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function addSymbol($string)
	{
		if (false !== strpos($string, '(') || false !== strpos($string, 'AS')
			|| false !== strpos($string, 'as')
			|| false !== strpos($string, 'top')
			|| false !== strpos($string, '`')) {
			return $string;
		}
		$keywords = explode('.', $string);
		foreach ($keywords as $pos => $keyword) {
			if ('*' == $keyword) {
				continue;
			}
			$keywords[$pos] = '[' . trim($keyword) . ']';
		}

		return implode('.', $keywords);
	}

	/*
	public function addSymbol($string, $prefix = null)
	{
		if (false !== strpos($string, '(') || false !== strpos($string, 'AS')
			|| false !== strpos($string, 'as')
			|| false !== strpos($string, '`')
			|| $string !== '*') {
			return $string;
		}
		$p = explode('.', $string);
		switch (count($p)) {
			case 1:
				$s1 = $prefix ? "`{$prefix}`" : null;
				$s2 = $p[0] != '*' ? "`{$p[0]}`" : $p[0];
				return $s1 ? "{$s1}.{$s2}" : $s2;
				break;
			case 2:
				return "`{$p[0]}`.`{$p[1]}`";
		}
	}
	*/

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