<?php 

require_once 'Suco/Loader.php';
require_once 'Suco/Controller/Front.php';
Suco_Loader::setAutoload(true);

class Suco_Application extends Suco_Controller_Front
{
	protected $_locale;
	
	public function getInstance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new self();
		}
		return $instance;
	}
	
	public function setLocale($locale = null)
	{
		if ($locale instanceof Suco_Locale) {
			$this->_locale = $locale;
		} else {
			require_once 'Suco/Locale.php';
			$this->_locale = new Suco_Locale();
		}
	}
	
	public function getLocale()
	{
		if (!$this->_locale) {
			$this->setLocale();
		}
		
		return $this->_locale;
	}
}