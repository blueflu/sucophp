<?php
/*
 * 数据库驱动的抽象类
 *
 * @version		3.0 2009/09/01 01:31
 * @author		blueflu (lqhuanle@163.com)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */

abstract class Suco_Db_Adapter_Abstract
{
	/*
	 * 数据库连接信息
	 *
	 * @var array
	 */
	protected $_dsn;
	
	/*
	 * 数据库连接句柄
	 *
	 * @var resource
	 */
	protected $_linkId;
	
	/*
	 * 字符集
	 *
	 * @var string
	 */
	protected $_charset;
	
	/*
	 * SQL总查询次数
	 *
	 * @var int
	 */
	protected $_totalExecuteQuantity = 0;
	
	protected $_totalExecuteTime = 0;
	
	protected $_querys = array();
	
	protected $_tbPrefix;
	/*
	 * 数据库连接
	 *
	 * @param array $dsn
	 * @param bool $persistent 是否进行持久连接
	 */
	abstract public function connect($host, $port, $user, $pass, $persistent = false);

	/*
	 * 打开指定数据库
	 *
	 * @param string $dnName
	 */
	abstract public function selectdb($dbName);
	
	/*
	 * 执行一条sql查询
	 *
	 * @param string $sql
	 *
	 * @return Suco_Db_Result_Abstract
	 */
	abstract public function execute($sql);
	
	/*
	 * 关闭当前数据库连接
	 */
	abstract public function close();

	/*
	 * 添加特殊符号
	 *
	 * @param string $value
	 * @param string $prefix
	 *
	 * @return string
	 */
	abstract public function addSymbol($string);
	
	/*
	 * 添加引号及转义
	 *
	 * return string
	 */
	public function addQuote($string)
	{
        if (is_int($string) || is_float($string)) {
            return $string;
        }
        return "'" . addcslashes(trim($string), "\000\n\r\\'\"\032") . "'";
	}

	/*
	 * 转换占位符
	 *
	 * @return string
	 */
	public function quoteInto($string, $params = array())
	{
		if (is_array($params)) {
			$i = 0;
			while (strpos($string, '?')) {
				$string = substr_replace($string, $this->addQuote($params[$i]), strpos($string, '?'), 1);
				$i++;
			}
			
			if (preg_match_all('#\:(\w+)#', $string, $m, PREG_SET_ORDER)) {
				foreach ($m as $c) {
					$string = str_replace($c[0], $this->addQuote($params[$c[1]]), $string);
				}
			}

		} else {
			$string = str_replace('?', $this->addQuote($params), $string);
		}
		return $string;
	}

	/*
	 * 创建一个数据表操作对象
	 *
	 * @return Suco_Db_Table
	 */
	public function table($tbName)
	{
		static $tbo;
		if (!isset($tbo[$tbName])) {
			require_once 'Suco/Db/Table.php';
			Suco_Db_Table::setAdapter($this);
			$tbo[$tbName] = new Suco_Db_Table($tbName);
		}
		return $tbo[$tbName];
	}

	/*
	 * 发起一个查询
	 *
	 * @param string|array $cols
	 * @param string $from
	 * @return Suco_Db_Select
	 */
	public function select()
	{
		$args = func_get_args();
		require_once 'Suco/Db/Select.php';
		$select = new Suco_Db_Select($this);
		if ($args) {
			$select->from($args[0], $args[1]);
		}
		return $select;
	}

	/*
	 * 检查是否数字类型数据
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function isNumeric($value)
	{
		return in_array($value, $this->_numericDataTypes);
	}

	/*
	 * 设置数据库前缀
	 *
	 * @param string $tbPrefix
	 */
	public function setTbPrefix($tbPrefix)
	{
		$this->_tbPrefix = $tbPrefix;	
	}
	
	/*
	 * 返回数据库前缀
	 *
	 * @param string $tbPrefix
	 */
	public function getTbPrefix()
	{
		return $this->_tbPrefix;
	}
	
	/*
	 * 返回SQL 调试器
	 * 
	 * return string
	 */
	public function dump()
	{
		$string = null;
		foreach ((array)$this->_querys as $item) {
			$string .= '<li>['.number_format($item['runtime'], 5).' ms]	'.$item['query'].' (<strong>'.$item['result'].'</strong>)</li>';
		}
		return '<div style="font-family:\'Courier New\'; font-size:12px; line-height:1.5em; padding:0px; margin:0px;">'
				.'<h2 style="font-weight:bold; font-size:24px;">[QUERYS '.strtoupper($this->_linkId).']</h2>'
				.'<ul style="padding:20px; list-style:inside disc;">'
				.$string
				.'</ul>'
				.'<p style="padding:4px; margin-left:20px;">'
				.'Total quantity:<strong>'.$this->_totalExecuteQuantity.'</strong>, '
				.'Total process time:<strong>'.number_format($this->_totalExecuteTime, 8) . '</strong> ms'
				.'</p>'
				.'</div>';
	}

	/*
	 * 返回当前时间
	 * 
	 * return string
	 */
	protected function _getMicrotime() 
	{
		list($usec, $sec) = explode(' ', microtime()); 
		return ((float)$usec + (float)$sec); 
	} 

	/*
	 * 析构函数
	 *
	 * 自动关闭数据库链接
	 * 
	 * return string
	 */
	public function __destruct()
	{
		if ($this->_linkId) {
			$this->close();	
		}
	}
}

?>