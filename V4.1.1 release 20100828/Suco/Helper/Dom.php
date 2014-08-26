<?php

require_once 'Suco/Helper/Abstract.php';

class Suco_Helper_Dom extends Suco_Helper_Abstract
{
	protected static $_elements = array();
	
	public function dom($name = 'default')
	{
		static $instance = array();
		if (!isset($instance[$name])) {
			$instance[$name] = new self();
		}
		return $instance[$name];
	}
	
	public function createElement($element, $args = array())
	{
		$class = new ReflectionClass('Suco_Helper_Dom_' . ucfirst($element));
		if ($class->hasMethod('__construct')) {
			$object = $class->newInstanceArgs($args);
		} else {
			$object = $class->newInstance();
		}
		self::$_elements[] = $object;
		return $object;
	}
}