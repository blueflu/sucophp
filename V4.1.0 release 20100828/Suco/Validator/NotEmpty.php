<?php

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