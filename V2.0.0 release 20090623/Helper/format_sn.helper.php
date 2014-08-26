<?php
	function format_sn($sn, $length = 4, $prefix = 0)
	{
		$prefix && $str = $prefix;
		return $str . str_repeat('0', $length - strlen($sn)) . $sn;
	}
?>