<?php

require_once 'Suco/Helper/Dom/Element.php';

class Suco_Helper_Dom_Input extends Suco_Helper_Dom_Element
{
	public function __toString()
	{
		$string = null;
		$attributes = null;
		foreach ($this->_attributes as $name => $value) {
			$attributes .= $name . '="' . $value . '" ';
		}
		$string = '<input ' . $attributes . '/>';
		return $string;
	}
}