<?php

class Suco_Bootstrap
{
	public function __construct()
	{
		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (substr($method, 0, 4) == 'init') {
				$this->$method();
			}
		}
	}
}