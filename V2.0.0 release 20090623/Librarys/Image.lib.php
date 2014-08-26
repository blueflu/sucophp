<?php
/*
 * SCN_Image #2008/6/12 16:20:06#
 * 图片操作类
 *
 * @version				1.5
 * @author				blueflu (lqhuanle@163.com)
 * @copyright			Copyright (c) 2008, Suconet, Inc.
 * @license				http://www.suconet.com/license
 * -----------------------------------------------------------
 * @example:
	
	SCN_Image::watermark($file);
	echo '<img src="'.$file.'" />';
 */

if (!defined('DS')) define(DS, DIRECTORY_SEPARATOR);

SCN_Loader::import('File' . LIBS_EXT, LIBS_DIR);

class SCN_Image
{
	/**
	 * 返回一个单件实例
	 * @return  object
	 */
	function &getInstance() {
		static $instance = array();
		if (!isset($instance[0]) || !$instance[0]) {
			$instance[0] =& new SCN_Image();
		}
		return $instance[0];
	}
	
	/**
	 * 缩放图片
	 * @return  object
	 */
	function zoom($file, $dstFile = NULL, $width = 0, $height = 0)
	{
		$info = getimagesize($file);
		$dw = $sw = $info[0];
		$dh = $sh = $info[1];

		if ($width < $sw && $width) {
			$dw = $width;
			$dh = $sh * ($width / $sw);
		}
		if ($height < $dh && $height) {
			$dh = $height;
			$dw = $sw * ($height / $sh);
		}
		
		$im = imagecreatetruecolor($dw, $dh);

		switch ($info[2]) {
			case 1:
				$dstFile = $dstFile ? $dstFile : str_replace('.gif', '_s.gif', $file);
				$sm = imagecreatefromgif($file);
				imagecopyresampled($im, $sm, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
				imagegif($im, $dstFile);
				break;
			case 2:
				$dstFile = $dstFile ? $dstFile : str_replace('.jpg', '_s.jpg', $file);
				$sm = imagecreatefromjpeg($file);
				imagecopyresampled($im, $sm, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
				imagejpeg($im, $dstFile);
				break;
			case 3:
				$dstFile = $dstFile ? $dstFile : str_replace('.png', '_s.png', $file);
				$sm = imagecreatefrompng($file);
				imagecopyresampled($im, $sm, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
				imagepng($im, $dstFile);
				break;
		}
		
		return $dstFile;
	}

	/**
	 * 图片验证码
	 * @return  object
	 */
	function verify($length = 5, $width = 46, $height = 20, $interference = 1)
	{
		//生成验证码图片 
		header("Content-type: image/PNG");  
		srand((double)microtime()*1000000); 
		$im = imagecreate(46,20); 
		$black = imagecolorallocate($im, 0,0,0); 
		$white = imagecolorallocate($im, 255,255,255); 
		$gray = imagecolorallocate($im, 200,200,200); 
		imagefill($im,68,30,$gray); 
		while(($authnum=rand()%10000)<1000);
		
		//将四位整数验证码绘入图片 
		imagestring($im, 5, 5, 3, $authnum, $white); 
		
		if ($interference) {
			for($i=0;$i<800;$i++)   //加入干扰象素 
			{ 
				$randcolor = imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));
				imagesetpixel($im, rand()%70 , rand()%30 , $randcolor); 
			}
		}
		
		ImagePNG($im); 
		ImageDestroy($im);
		$_SESSION['auth'] = $authnum;
		exit();
	}

	/**
	 * 水印
	 * @return  object
	 */
	function watermark($file, $source = './source.gif', $position = 'topRight')
	{
		$im = imagecreatefromjpeg($file);
		$sim = imagecreatefromgif($source);
		$info = getimagesize($source);
		$w = $info[0];	$h = $info[1];
		
		$info = getimagesize($file);
		$border = 10;
		switch($position)
		{
			case 'topLeft':
				$sw = $border;
				$sh = $border;
				break;
			case 'topCenter':
				$sw = $info[0]/2 - $w/2;
				$sh = $border;
				break;
			case 'topRight':
				$sw = $info[0] - $w - $border;
				$sh = $border;
				break;
			case 'bottomLeft':
				$sw = $border;
				$sh = $info[1] - $h - $border;
				break;
			case 'bottomCenter':
				$sw = $info[0]/2 - $w/2;
				$sh = $info[1] - $h - $border;
				break;
			case 'bottomRight':
				$sw = $info[0] - $w - $border;
				$sh = $info[1] - $h - $border;
				break;
		}
		
		imagecopymerge($im,$sim,$sw,$sh,0,0,$w,$h,60);
		imagejpeg($im, $file);
	}
	

}

?>