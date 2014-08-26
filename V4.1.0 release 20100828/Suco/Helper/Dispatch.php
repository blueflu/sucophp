<?php

class Suco_Helper_Dispatch
{
	public function dispatch($controller, $action = null, $module = null, $params = null)
	{
		try {
			$dispatcher = Suco_Application::getInstance()->getDispatcher();
			$dispatcher->dispatch($controller, $action, $module, $params);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}