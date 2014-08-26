<?php

class Suco_Format
{
	public function telphone($countrycode, $citycode, $number)
	{
		return sprintf('%s-%s-%s', $countrycode, $citycode, $number);
	}
	
	public function currency($amount)
	{
		return Suco_Application::getInstance()->getLocale()->currency($amount);
	}
}