<?php
/**
 * Suco_Validator 数据验证类
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Validator
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

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