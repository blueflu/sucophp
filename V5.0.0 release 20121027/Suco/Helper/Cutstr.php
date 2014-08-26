<?php
/**
 * Suco_Helper_Cutstr 截取字符串
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Cutstr implements Suco_Helper_Interface
{
	public static function callback($args)
	{
		return self::cutstr($args[0], $args[1], isset($args[2]) ? $args[2] : '...', isset($args[3]) ? $args[3] : 'utf-8', $args[4]);
	}

	public static function cutstr($string, $length, $dot = '...' , $charset = 'utf-8', $enforce = 0)
	{
		$string = str_replace(array('&nbsp;',"\r\n", '	'), array(' ','',''), $string);
		$string = trim(strip_tags($string));

		if(strlen($string) <= $length) {
			return $string;
		}
		$string = str_replace(array('&', '"', '<', '>'), array('&', '"', '<', '>'), $string);
		$strcut = '';
		if(strtolower($charset) == 'utf-8') {
			$n = $tn = $noc = 0;
			while($n < strlen($string)) {
				$t = ord($string[$n]);
				if ($enforce) $noc++;
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1; $n++; $noc++;
				} elseif(194 <= $t && $t <= 223) {
					$tn = 2; $n += 2; $noc += 2;
				} elseif(224 <= $t && $t < 239) {
					$tn = 3; $n += 3; $noc += 2;
				} elseif(240 <= $t && $t <= 247) {
					$tn = 4; $n += 4; $noc += 2;
				} elseif(248 <= $t && $t <= 251) {
					$tn = 5; $n += 5; $noc += 2;
				} elseif($t == 252 || $t == 253) {
					$tn = 6; $n += 6; $noc += 2;
				} else {
					$n++;
					$noc--;
				}
				if($noc >= $length) {
					break;
				}
			}
			if($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
		} else {
			for($i = 0; $i < $length - strlen($dot) - 1; $i++) {
				$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
			}
		}
		return $strcut.$dot;
	}
}