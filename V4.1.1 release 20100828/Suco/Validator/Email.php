<?php

class Suco_Validator_Email extends Suco_Validator_Abstract
{
	public function isValid($data)
	{
		if (empty($data) && $this->_required) {
			$this->setError('invalid_empty');
			return false;
		}
		if (!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $data)) {
			$this->setError('invalid_format');
			return false;
		}
		return true;
	}
}