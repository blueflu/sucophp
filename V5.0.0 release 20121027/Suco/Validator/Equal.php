<?php
/**
 * Suco_Validator_Equal 等值验证
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Validator
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Validator_Equal extends Suco_Validator_Abstract
{
	protected $_compare;

	public function __construct($compare)
	{
		$this->_compare = $compare;
	}

	public function isValid($data)
	{
		if (empty($data) && $this->_required) {
			$this->setError('invalid_empty');
			return false;
		}
		if ($this->_compare != $data) {
			$this->setError('invalid_not_equal');
			return false;
		}
		return true;
	}
}