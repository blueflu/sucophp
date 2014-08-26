<?php
function split_key($tplVar, $delimit, $key) { 
	$tmp = explode($delimit, $tplVar);
	return $tplVar[$key];
}
?>