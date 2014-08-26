<?php

class Suco_Exception extends Exception
{
	public function dump()
	{
		return '<div style="font-family:\'Courier New\'; font-size:12px;">'
				. '<h2 style="font-weight:bold; font-size:24px;">['.strtoupper(get_class($this)).']</h2>'
				. '<span style="font-size:18px; font-weight:bold; margin:20px;">'.$this->getMessage().'</span>'
				. '<ul><li>' . str_replace("\n", '</li><li>', $this->getTraceAsString()) . '</li></ul>'
				. '</div>';
	}
}