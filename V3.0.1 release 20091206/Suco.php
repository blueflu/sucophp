<?php 

require_once 'Suco/Loader.php';
Suco_Loader::setAutoload(true);

class Suco
{
	public function getInstance($config = null)
	{
		static $instance;
		if (!isset($instance)) {
			$instance = Suco_Controller_Front::getInstance($config);
		}
		return $instance;
	}
	
	public static function model($name)
	{
		static $model;
		if (!isset($model[$name])) { 
			$model[$name] = new $name();
		}
		return $model[$name];
	}
	
	public static function plugin($name)
	{
		static $plugin;
		if (!isset($plugin[$name])) { 
			//$plugin[$name] = new $name();
		}
		return $plugin[$name];		
	}
}