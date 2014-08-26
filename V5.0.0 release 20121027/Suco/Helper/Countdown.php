<?php
/**
 * Suco_Helper_Countdown 倒计时
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		Helper
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_Helper_Countdown implements Suco_Helper_Interface
{
	public static function callback($args)
	{
		return self::countdown($args[0], $args[1], $args[2]);
	}

	public static function countdown($endTime, $now = 0, $full = 0)
	{
		$string = null;
		if (!$now) {
			$now = time();
		}
		$s = $endTime - $now;
		if ($s >= 60) {
			$i = $s / 60;
			$s = $s % 60;
			if ($i >= 60) {
				$h = $i / 60;
				$i = $i % 60;
				if ($h >= 24) {
					$d = $h / 24;
					$h = $h % 24;
					if ($d >= 30) {
						$m = $d / 30;
						$d = $d % 30;
						if ($m >= 12) {
							$y = $m / 12;
							$m = $m % 12;
						}
					}
				}
			}
		}

		$return = array();
		if (isset($y) && $y > 0) {
			$return[] = intval($y) . ' year' . ($y > 1 ? 's' : null);
		}
		if (isset($m) && $m > 0) {
			$return[] = intval($m) . ' month' . ($m > 1 ? 's' : null);
		}
		if (isset($d) && $d > 0) {
			$return[] = intval($d) . ' day' . ($d > 1 ? 's' : null);
		}
		if (isset($h) && $h > 0) {
			$return[] = intval($h) . ' hour' . ($h > 1 ? 's' : null);
		}
		if (isset($i) && $i > 0) {
			$return[] = intval($i) . ' minute' . ($i > 1 ? 's' : null);
		}
		if (isset($s) && $s > 0) {
			$return[] = intval($s) . ' second' . ($s > 1 ? 's' : null);
		}

		if ($return) {
			if ($full) {
				return implode(' ', $return);
			} else {
				return $return[0] . ' ' . @$return[1];
			}
		} else {
			return 'expired';
		}
	}
}