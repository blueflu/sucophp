<?php

class Suco_View_Helper_BaseUrl
{
	public function baseUrl()
	{
		return Suco_Controller_Front::getInstance()->getRequest()->getBasePath() . '/';
	}
}