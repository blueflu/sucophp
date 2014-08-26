<?php

class Suco_Helper_Date
{
	public function date($date, $display_format = 'long')
	{
		switch ($display_format) {
			case 'long':
				return date('Y/m/d H:i:s', $date);
			case 'short':
				return date('Y/m/d');
		}
	}

}