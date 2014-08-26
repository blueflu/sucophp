<?php

class Suco_Helper_Keyword
{
	public function keyword($str, $keyword)
	{
		if (!empty($keyword)) {
			$keywords = explode(" ", $keyword);
			foreach ($keywords as $keyword) {
				$str = str_replace($keyword, "<font color=\"red\">{$keyword}</font>", $str);
			}
		}
		return $str;
	}
}