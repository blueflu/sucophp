<?php
/**
 * Suco_Db_Table 表操作业务封装
 *
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2008, Suconet, Inc.
 * @license		http://www.suconet.com/license
 * @package		Db
 * -----------------------------------------------------------
 */

class Suco_Db_Table extends Suco_Db_Table_Abstract
{
	/**
	 * 设置默认数据库适配器
	 * @param object $adapter
	 */
	public static function setDefaultAdapter(Suco_Db_Adapter_Abstract $adapter)
	{
		self::$_defaultAdapter = $adapter;
	}

	/**
	 * 返回默认数据库适配器
	 * @return object
	 */
	public static function getDefaultAdapter()
	{
		return self::$_defaultAdapter;
	}

	/**
	 * 设置数据库适配器
	 * @param object $adapter
	 * @return void
	 */
	public function setAdapter(Suco_Db_Adapter_Abstract $adapter)
	{
		$this->_adapter = $adapter;
	}

	/**
	 * 返回数据库适配器
	 * @return $adapter
	 */
	public function getAdapter()
	{
		if (!$this->_adapter && !self::$_defaultAdapter) {
			require_once 'Suco/Db/Exception.php';
			throw new Suco_Db_Exception('没有指定数据库适配器');
		}
		return $this->_adapter ? $this->_adapter : self::getDefaultAdapter();
	}

	/**
	 * 添加一个触发器
	 * @return void 
	 */
	public function addTrigger($trigger = null)
	{
		if (!$trigger instanceof Suco_Db_Table_Trigger) {
			throw new Suco_Db_Exception('无效的触发器');
		}
		$this->_triggers[] = $trigger;
	}

	/**
	 * 返回所有触发器
	 * @return object
	 */
	public function getTriggers()
	{
		return (array)$this->_triggers;
	}
}