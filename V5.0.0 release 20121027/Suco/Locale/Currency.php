<?php

class Suco_Locale_Currency
{
	protected $_currency;
	protected $_locale;

	public function __construct($currency = 'USD', $locale)
	{
		$this->_locale = $locale;
		$this->_currency = $currency;
	}

	public function exchange($form, $to)
	{

	}

	public function format($amount, $type = 'long')
	{
		switch($type) {
			case 'long':
				return $amount;
			case 'short':
				return $amount;
		}
	}

	public function __toString()
	{
		return $this->format($this->_amount);
	}
}