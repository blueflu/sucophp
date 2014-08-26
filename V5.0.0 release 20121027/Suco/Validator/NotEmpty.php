<?php
/**
 * Suco_Validator_NotEmpty 非空验证
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Validator
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Validator_NotEmpty extends Suco_Validator_Abstract
{
	public function isValid($data)
	{
		if (empty($data)) {
			$this->setError('invalid_empty');
			return false;
		}
		return true;
	}
}