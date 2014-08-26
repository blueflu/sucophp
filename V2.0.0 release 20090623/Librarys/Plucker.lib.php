<?php
class SCN_Plucker
{

	function login($url, $data, $cookieFile, &$login)
	{
		if ($login) { return; }

		$ch1 = curl_init();
		curl_setopt($ch1, CURLOPT_URL, $url);
		curl_setopt($ch1, CURLOPT_HEADER, false);
		
		curl_setopt($ch1, CURLOPT_POST, 1);
		curl_setopt($ch1, CURLOPT_POSTFIELDS, $data);//顺便测试了下post提交数据
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch1, CURLOPT_NOBODY, 0);
		curl_setopt($ch1, CURLOPT_COOKIEJAR, $cookieFile); 
		curl_exec($ch1);
		curl_close($ch1);

		$login = 1;
	}

	function connect($url, $cookieFile = false)
	{
		$url = str_replace('&amp;', '&', $url);
		if (function_exists('curl_init')) {
		
			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt ($ch, CURLOPT_COOKIEFILE, $cookieFile); 
			
			
			$this->content = curl_exec($ch);
			curl_close($ch);
		} else {
			$this->content = @file_get_contents($url);
		}

		if (!$this->content) {
			show_error('链接失败 ' . $url, 2);
		}
	}

	function parseList(&$list, $tags = '{all}' ,$baseUrl = '')
	{
		$tags = $this->fiterRegEx($tags);
		$pattern = '/href=[\"\']('.$tags.')[\"\'].*>(.*)<\/a>/Ui';
		preg_match_all ($pattern, $this->content, $out);

		if (!$out[1]) { //找不到时尝试空引号
			$pattern = '/href=('.$tags.')/Ui';
			preg_match_all ($pattern, $this->content, $out);
		}
		foreach ($out[1] as $url) { //过滤掉重复的数据,并加前缀
			$url = (@strpos($url, $baseUrl) === false ? $baseUrl . $url : $url);
			if (!@in_array($url, $list)) {
				$list[] = $url;
			}
		}
		return $out[2];
	}
	
	function parseContent($tags, $replace = array())
	{
		if (!isset($this->caches[$tags])) {
			$pattern = $this->fiterRegEx($tags);

			preg_match('/'.$pattern.'/isU', $this->content, $out);
			$this->caches[$tags] = $out[1]; //缓存抓取内容
			return str_replace($replace[0], $replace[1], $out[1]);
		} else {
			return str_replace($replace[0], $replace[1], $this->caches[$tags]);
		}

	}

	function parseMultiContent($tags, $replace = array())
	{
		$pattern = $this->fiterRegEx($tags);
		preg_match_all('/'.$pattern.'/Us', $this->content, $out);
		return $out[1];
	}
	
	function fiterRegEx($string, $mode = 1)
	{
		$RegEx = array(
			0 => array('.', '(', ')', "'", '"', '?', '*', '&', '/', '{all}', '{data}', '{block}'),
			1 => array('\.', '\(', '\)', "\'", '\"', '\?', '\*', '\&', '\/', '.*', '(.*)', '(.*)'),
		);

		$string = $mode ? str_replace($RegEx[0], $RegEx[1], $string)
						: str_replace($RegEx[1], $RegEx[0], $string);
		
		return $string;
	}
	
}
?>