<?php

require_once 'Suco/Loader/Class.php';

class Suco_Loader extends Suco_Loader_Class
{
	public function setAutoload($enabled = true)
	{
		require_once 'Suco/Loader/Autoload.php';
        if ($enabled === true) {
            Suco_Loader_Autoload::enable();
        } else {
            Suco_Loader_Autoload::disable();
        }
	}
}
?>