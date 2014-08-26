<?php
/*
 * 随机字符串
 */
function randStr($len=6,$format='ALL') { 
	switch($format) { 
		case 'LOWER':
			$chars='abcdefghijklmnopqrstuvwxyz0123456789'; break;
		case 'ALL':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~'; break;
		case 'CHAR':
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; break;
		case 'NUMBER':
			$chars='0123456789'; break;
		default :
			$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~'; 
		break;
	}
	mt_srand((double)microtime()*1000000*getmypid()); 
	$password="";
	while(strlen($password)<$len)
		$password.=substr($chars,(mt_rand()%strlen($chars)),1);
	return $password;
}
?>