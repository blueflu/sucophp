<?php

class SCN_Array
{
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

	function transpose($data, $balance = 0) {
		if (is_null($data) || empty($data) || !$data) { return; }
		foreach (@$data as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $k1 => $v1) {
					$arr[$k1][$k] = $v1;
				}
			}
		}
		
		foreach (@$arr as $k => $v) {
			foreach ($data as $k1 => $v1) {
				if (!is_array($v1)) {
					$arr[$k][$k1] = $v1;
				}
			}
		}
		
		if ($balance) {
			static $i = 0;
			foreach (@$arr as $k => $v) {
				if ($i == 0) {
					$i = count($v);
				}
				if ($i != count($v)) {
					unset($arr[$k]);
				}
			}
		}
		return $arr;
	}
}
?>