<?php

class Suco_View_Helper_Dispatch
{
	public function dispatch($action, $controller = null, $module = null, $params = null)
	{
		$dispatcher = Suco_Controller_Front::getInstance()->getDispatcher();
		$dispatcher->setAction($action);
		if ($controller) {
			$dispatcher->setController($controller);
		}
		if ($module) {
			$dispatcher->setModule($module);
		}
		
		$dispatcher->dispatch();
	}
}