<?php

class SCN_Html
{
	function SCN_Html()
	{
	
	}
	
	function array2tree($arr, $foreignKey = 'pid', $primeKey = 'id')
	{
		$tree = array();
		foreach ($arr as $key => $row) {
			$parentId = $row[$foreignKey];
			if ($row[$foreignKey]) {
				if (!isset($arr[$parentId])) { continue; }
				$parent =& $arr[$parentId];
				$parent['child'][] =& $arr[$key];
			} else {
				$tree[] =& $arr[$key];
			}
		}
		return $tree;
	}
	
	function buildTreeSelect($arr)
	{
		foreach ($arr as $key => $row) {
			$html .= '<option>'.$row['categoryName'].'</option>';
		}
		return $html;
	}
}
?>