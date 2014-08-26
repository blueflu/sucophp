<?php

require_once 'Suco/Helper/Abstract.php';

class Suco_Helper_Form
{
	public function form()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}
	
	public function createElement($name, $element = array())
	{
		if ($element instanceof Suco_Helper_Form_Element) {
			$this->_element[$name] = $element;
		} else {
			$this->_element[$name] = new Suco_Helper_Form_Element($element);
		}
		return $this->_element[$name];
	}
	
	public function __toString()
	{
		$string = null;
		foreach ($this->_element as $name => $_element) {
			$string .= $_element->__toString();
		}
		return $string;
	}
}