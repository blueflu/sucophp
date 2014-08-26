<?php

require_once 'Suco/Helper/Abstract.php';

class Suco_Helper_Lang extends Suco_Helper_Abstract
{
	public function lang($string)
	{
		$lang = Suco_Application::getInstance()->getLocale();
		return $lang->tranlate($string);
	}
}