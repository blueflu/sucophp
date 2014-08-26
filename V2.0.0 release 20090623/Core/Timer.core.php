<?php
/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * 计时器
 * 用于计算代码断的执行时间
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 * @example:
	SCN_Timer::setPoint('test1'); 		#设置一个时间点test1
	test1 code... 						#需要进行时间计算的代码片断
	echo SCN_Timer::getPoint('test1'); 	#返回test1运行时间
 */

class SCN_Timer
{

	/*
	 * 返回当前系统时间 
	 * @return  float
	 */
	 
	function getMicrotime() 
	{
		list($usec, $sec) = explode(' ', microtime()); 
		return ((float)$usec + (float)$sec); 
	} 

	/*
	 * 记录计时点 
	 * @return  array
	 */

	function &point()  {
		static $point  =  array();
		return $point;
	}
	
	/*
	 * 设置一个计时点 
	 * @params  string  $key
	 */
	
	function mark($key = 0) 
	{
		$point = &SCN_Timer::point();
		$point[$key] = SCN_Timer::getMicrotime();
	} 
	
	/*
	 * 计算执行时间
	 * @params  string  $key
	 * @params  int  $peric	小数位数
	 * @return  float
	 */

	function executeTime($start, $end = 0) 
	{
		$point = &SCN_Timer::point();
		return $end ? $point[$end] : SCN_Timer::getMicrotime() - $point[$start]; 
	}
	
}

?>