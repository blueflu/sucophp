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
}