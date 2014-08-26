<?php

class Suco_Validator
{
	protected $_validates;
	
	public function is($validate, $args = array())
	{
		$className = 'Suco_Validator_' . ucfirst($validate);
		$class = new ReflectionClass($className);
		if ($class->hasMethod('__construct')) {
			$args = !is_array($args) ? array($args) : $args;
			$object = $class->newInstanceArgs($args);
		} else {
			$object = $class->newInstance();
		}
		$this->_validates[] = $object;
		return $object;
	}
	
	public function getErrors()
	{
		$errors = array();
		foreach ($this->_validates as $validate) {
			$errors[] = $validate->getError();
		}
		return $errors;
	}
}