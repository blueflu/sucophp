<?php 

require_once 'Suco/Config/Abstract.php';

class Suco_Config extends Suco_Config_Abstract
{
	
	public function factory($file)
	{
		static $loaded = array();
		
		if (!isset($loaded[$file])) {
			$path = pathinfo($file);
			$adapter = ucfirst($path['extension']);
			$class = "Suco_Config_{$adapter}";
			
			require_once "Suco/Config/{$adapter}.php";
			$loaded[$file] = new $class($file);
		}
		return $loaded[$file];
	}
	
	public function __construct(array $config = null)
	{
		foreach ($config as $key => $val) {
			$this->set($key, $val);
		}
	}
} 