<?php

class Suco_View_Helper_Dispatch
{
	public function dispatch($controller, $action = null, $module = null, $params = null)
	{
		$dispatcher = Suco_Controller_Front::getInstance()->getDispatcher();
		$dispatcher->dispatch($controller, $action, $module, $params);
	}
}