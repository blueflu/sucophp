<?php
/**
 * Suco_File_Image 图片文件操作
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		File
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

require_once 'Suco/File.php';
class Suco_File_Image extends Suco_File
{
	/**
	 * 生成缩略图
	 *
	 * @param string $file 文件路径
	 * @param int $width
	 * @param int $height
	 * @param string $dstFile 目标文件路径
	 * @return string 返回缩略图路径
	 */
	public function thumb($file, $width = 0, $height = 0, $dstFile = NULL)
	{
		//没有指定目标文件时.自动生成文件名
		if (!$dstFile) {
			$path = pathinfo($file);
			$dstFile = "{$path['dirname']}/{$path['filename']}_{$width}x{$height}.{$path['extension']}";
			$dstFile = str_replace(Suco_Application::instance()->getRequest()->getHost(), '.', $dstFile); //拿掉http://website/部分
		}

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
				$sm = imagecreatefromgif($file);
				imagecopyresampled($im, $sm, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
				imagegif($im, $dstFile);
				break;
			case 2:
				$sm = imagecreatefromjpeg($file);
				imagecopyresampled($im, $sm, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
				imagejpeg($im, $dstFile);
				break;
			case 3:
				$sm = imagecreatefrompng($file);
				imagecopyresampled($im, $sm, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
				imagepng($im, $dstFile);
				break;
		}

		return str_replace('./', '', $dstFile);
	}

	/**
	 * 加水印
	 *
	 * @param string $file 文件路径
	 * @param string $source 要叠加的水印图片路径
	 * @param string $position 水印位置 topLeft|topCenter|topRight|bottomLeft|bottomCenter|bottomRight
	 * @return void
	 */
	public function watermark($file, $source, $position = 'topRight')
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