<?php

class Suco_Controller_Router_Route_Abstract
{
	protected $_pattern;
	protected $_reverse;
	protected $_mapping = array();
	protected $_defaults = array();
	
	public function __construct($pattern = null, $options = null)
	{
		$this->_pattern = $pattern;

		if (isset($options[Suco_Controller_Router_Route::ROUTE_DEFAULTS])) {
			$this->_defaults = $options[Suco_Controller_Router_Route::ROUTE_DEFAULTS];
		}
		if (isset($options[Suco_Controller_Router_Route::ROUTE_MAPPING])) {
			$this->_mapping = $options[Suco_Controller_Router_Route::ROUTE_MAPPING];
		}
		if (isset($options[Suco_Controller_Router_Route::ROUTE_REVERSE])) {
			$this->_reverse = $options[Suco_Controller_Router_Route::ROUTE_REVERSE];
		}
	}
	
	public function encode($url)
	{
		//$url = str_replace(' ', '-', trim($url));
		return rawurlencode(iconv('utf-8', 'gb2312', $url));
	}
	
	public function decode($url)
	{
		//$url = str_replace('_', '-', trim($url));
		return rawurldecode(iconv('gb2312', 'utf-8', $url));
	}

}