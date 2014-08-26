<?php

class SCN_Tree
{
	var $tree;
		
	function dumpTree($arr)
	{
		$tree = array();
		foreach ((array)$arr as $key => $row) {
			$parentId = $row[1];
			if ($row[1]) {
				if (!isset($arr[$parentId])) { continue; }
				$parent =& $arr[$parentId];
				$parent['child'][] =& $arr[$key];
			} else {
				$tree[] =& $arr[$key];
			}
		}
		return $tree;
	}
	
}
?>