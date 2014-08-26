<?php

interface Suco_View_Interface
{
	public function assign($index, $value = null);
	public function render($file);
	
	public function setRequest(Suco_Controller_Request_Interface $request);
	public function getRequest();
	public function setResponse(Suco_Controller_Response_Interface $response);
	public function getResponse();
}