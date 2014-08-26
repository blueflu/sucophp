<?php
Suco_Loader::loadClass('Suco_File');
class Suco_File_Image extends Suco_File
{
	public function thumb($file, $width = 0, $height = 0, $dstFile = NULL)
	{
		//没有指定目标文件时.自动生成文件名
		if (!$dstFile) {
			$path = pathinfo($file);
			$dstFile = "{$path['dirname']}/{$path['filename']}_{$width}x{$height}.{$path['extension']}";
			$dstFile = str_replace(Suco_Application::getInstance()->getRequest()->getHost(), '.', $dstFile); //拿掉http://siteurl/部分
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
	
	public function watermark($file, $source = './source.gif', $position = 'topRight')
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