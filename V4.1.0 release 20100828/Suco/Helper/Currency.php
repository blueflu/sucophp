<?php

require_once 'Suco/Helper/Abstract.php';

class Suco_Helper_Currency extends Suco_Helper_Abstract
{
	public function currency($amount)
	{
		return Suco_Application::getInstance()->getLocale()->currency($amount);
	}
}