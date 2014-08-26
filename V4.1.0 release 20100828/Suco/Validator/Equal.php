<?php

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