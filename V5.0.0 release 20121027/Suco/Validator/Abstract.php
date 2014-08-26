<?php
/**
 * Suco_Validator_Abstract 数据验证规则抽象
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Validator
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

abstract class Suco_Validator_Abstract
{
	protected $_messages = array();
	protected $_errors = null;

	protected $_required = false;

	public function setRequired($bool)
	{
		$this->_required = $bool;
		return $this;
	}

	public function setMessage($key, $value)
	{
		$this->_messages[$key] = $value;
		return $this;
	}

	public function setMessages($messages)
	{
		$this->_messages = $messages;
		return $this;
	}

	public function getMessage($key)
	{
		return isset($this->_messages[$key]) ? $this->_messages[$key] : 'undefind';
	}

	public function setError($key)
	{
		$this->_errors = $this->getMessage($key);
		return $this;
	}

	public function getError()
	{
		return $this->_errors;
	}
}