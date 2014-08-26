<?php
class SCN_Exception extends Exception
{	
	function dump()
	{
		if (!DEBUG && $this->getCode <= 1) {
			return;
		}
		
		$this->getCode();
		echo '<h3>'.$this->getMessage().'</h3>';
		echo '<pre>';
		echo $this->getTraceAsString();
	}
}
?>