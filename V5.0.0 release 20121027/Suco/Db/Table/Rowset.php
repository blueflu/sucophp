<?php
/**
 * Suco_Db_Table_Rowset 数据行集对象
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */
require_once 'Suco/Object.php';

class Suco_Db_Table_Rowset extends Suco_Object
{
	protected $_table;
	protected $_select;

	/**
	 * 构造函数
	 *
	 * @param array $data
	 * @param object $table
	 * @parma object $select
	 *
	 * @return void
	 */
	public function __construct($data, $table = null, $select = null)
	{
		if (!$table instanceof Suco_Db_Table_Abstract) {
			//throw new Suco_Exception('指定的不是Suco_Db_Table对象');
		}

		$this->setTable($table);
		$this->setSelect($select);
		$this->_data = $data;
	}

	/**
	 * 设置表对象
	 *
	 * @param object $table
	 *
	 * @return void
	 */
	public function setTable($table)
	{
		$this->_table = $table;
	}

	/**
	 * 返回表对象
	 *
	 * @return object
	 */
	public function getTable()
	{
		return $this->_table;
	}

	/**
	 * 设置 Select 对象
	 */
	public function setSelect($select)
	{
		$this->_select = $select;
	}

	/**
	 * 返回 Select 对象
	 */
	public function getSelect()
	{
		return $this->_select;
	}

	/**
	 * 魔术方法
	 *
	 * @param string $method
	 * @param array $params
	 *
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
	 * 设置一项数据
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;
	}

	/**
	 * 返回一项数据
	 *
	 * @param string $key
	 *
	 * @return object
	 */
	public function get($key)
	{
		return new Suco_Db_Table_Row($this->_data[$key], $this->getTable());
	}

	/**
	 * 返回当前数据
	 *
	 * @return object
	 */
    public function current()
    {
        return new Suco_Db_Table_Row(current($this->_data), $this->getTable());
    }

	/**
	 * getTotal 方法别名
	 *
	 * @return int
	 */
    public function total()
    {
		return $this->getTotal();
    }

	/**
	 * 统计记录集数量
	 *
	 * @return int
	 */
	public function getTotal()
	{
		if ($this->getSelect() && $this->getSelect()->getPart(Suco_Db_Select::PAGINATOR)) { //已分页的SQL用COUNT(*)统计
			return $this->getSelect()->getTotal();
		}
		return count($this->_data);
	}
}