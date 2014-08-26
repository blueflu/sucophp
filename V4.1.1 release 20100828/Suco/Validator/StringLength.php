<?php

class Suco_Validator_StringLength extends Suco_Validator_Abstract
{
	protected $_min;
	protected $_max;
	
	public function __construct($min, $max)
	{
		if ($min > $max) {
			throw new Suco_Exception('设置错误');
		}
		$this->_min = $min;
		$this->_max = $max;	
	}
	
	public function isValid($data)
	{
		if (empty($data) && $this->_required) {
			$this->setError('invalid_empty');
			return false;
		}
		if (strlen($data) < $this->_min) {
			$this->setError('invalid_too_short');
			return false;
		}
		if (strlen($data) > $this->_max) {
			$this->setError('invalid_too_long');
			return false;
		}
		return true;
	}
}