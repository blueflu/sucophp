<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * URI类
 * 用于转换URL模式 如 将 /news/list 映射成 ?mod=news&act=list
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */
 
class SCN_Uri
{
	const URI_STANDARD = 0;
	const URI_PATHINFO = 1;
	const URI_REWRITE = 2;
	
	public $uriMode = 0;
	public $curUrl  = NULL;
	public $curCtrl = NULL;
	public $curAct	= NULL;
	public $curParam = NULL;
	
	public $delimit1 = '/';
	public $delimit2 = '----';
	public $delimit3 = '_';
	public $htmlExt = '.html';
	
	public $ctrlKeyName = 'mod';
	public $actKeyName = 'act';
	public $defaultCtrl = 'index';
	public $defaultAct = 'default';
	
	public function __construct($mode = self::URI_STANDARD)
	{
		$this->uriMode = $mode;
		$this->curUrl = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);
		$this->parseUri();
	}
	
	public function parseUri()
	{
		if ($this->uriMode == self::URI_STANDARD) {
			if (false === strpos($this->curUrl, '?') && $this->curUrl) {
				header('Location:' . $this->p2s($this->curUrl));
			}
			$this->curCtrl = isset($_GET[$this->ctrlKeyName]) ? $_GET[$this->ctrlKeyName] : $this->defaultCtrl;
			$this->curAct = isset($_GET[$this->actKeyName]) ? $_GET[$this->actKeyName] : $this->defaultAct;
		} else {
			if (false !== strpos($this->curUrl, '?')) {
				header('Location:' . $this->s2p($this->curUrl));
			}

			$tmp = explode($this->delimit1, $this->curUrl);

			$this->curCtrl = isset($tmp[1]) ? $tmp[1] : $this->defaultCtrl;
			$this->curAct = isset($tmp[2]) ? $tmp[2] : $this->defaultAct;
			$this->curParam = isset($tmp[3]) ? $tmp[3] : '';
			if (strpos($this->curAct, $this->htmlExt) !== false) {
				$this->curParam = $this->curAct;
				$this->curAct = $this->defaultAct;
			}
			
			if ($this->curParam) {
				$params = explode($this->delimit2, str_replace($this->htmlExt, '', $this->curParam));
				foreach ($params as $param) {
					list($key, $val) = explode($this->delimit3, $param);
					$_GET[$key] = $val;
				}
				$_REQUEST = array_merge($_GET, $_REQUEST);
			}
			unset($tmp);
		}
	}
	
	public function s2p($href)
	{
		preg_match('/\?(.*)/i', $href, $s);
		if (!$s) return strpos($href, $_SERVER['SCRIPT_NAME']) === false ? $_SERVER['SCRIPT_NAME'] . $href : $href;

		$url = '';
		$arr = explode('&', $s[1]);
		foreach ($arr as $item) {
			list($key, $val) = explode('=', $item);
			if ($key == $this->ctrlKeyName || $key == $this->actKeyName) {
				$url .= $this->delimit1 . $val;
			} else {
				$params[] = $key . $this->delimit3 . $val;
			}
		}
		
		return $_SERVER['SCRIPT_NAME'] . $url . (isset($params) ? $this->delimit1 . implode($this->delimit2, $params) . $this->htmlExt : '');
	}
	
	public function p2s($href)
	{
		if (strpos($href, $this->delmit1) === false) { return $href; }
		$uri = explode($this->delimit1, $href);
		array_shift($uri);
		
		isset($uri[0]) && $url[] = $this->ctrlKeyName . '=' . $uri[0];
		isset($uri[1]) && $url[] = $this->actKeyName . '=' . (strpos($uri[1], $this->htmlExt) === false ? $uri[1] : '');

		$params = array_pop($uri);
		if (strpos($params, $this->htmlExt) !== false) {
			$params = str_replace($this->htmlExt, '', $params);
			$params = explode($this->delimit2, $params);
			foreach ($params as $param) {
				list($key, $val) = explode($this->delimit3, $param);
				$url[]= $key . '=' . $val;
			}
		}
		
		return $_SERVER['SCRIPT_NAME'] . '?' . implode('&', $url);
	}
	
	public function wrap($href)
	{
		switch ($this->uriMode)
		{
			case self::URI_STANDARD:
				return $this->p2s($href);
			case self::URI_PATHINFO:
				return $this->s2p($href);
			case self::URI_REWRITE:
				return $this->s2p($href);
		}
	}
	
	public function redirect($url)
	{
		echo "<script>window.location = '{$url}'</script>";	
		exit;
	}
	
	public function goRef()
	{
		$this->redirect($_SERVER['HTTP_REFERER']);	
	}
	
}
?>