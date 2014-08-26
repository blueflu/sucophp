<?php

class Suco_Loader_Autoload
{
	public function enable()
	{
		spl_autoload_register(array(__CLASS__, '_autoload'));
	}
	
	public function disable()
	{
		spl_autoload_unregister(array(__CLASS__, '_autoload'));
	}
	
    protected static function _autoload($class)
    {
        try {
        	require_once 'Suco/Loader/Class.php';
            Suco_Loader_Class::loadClass($class);
            return true;
        } catch (Suco_Loader_Exception $e) {
            return false;
        }
    }
}