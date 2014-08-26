<?php

function validity($beginTime, $validity)
{
	$diff = $beginTime - $validity;
	return date('Y-m-d', $diff);
}

?>