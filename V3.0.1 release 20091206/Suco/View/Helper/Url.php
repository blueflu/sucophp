<?php

class Suco_View_Helper_Url
{
	public function url($url, $route = 'default')
	{
		return Suco_Controller_Front::getInstance()
									->getRouter()
									->reverse($url);
	}

}