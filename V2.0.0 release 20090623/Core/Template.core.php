<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * 模板引擎
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */
 
class SCN_Template
{
	//模板变量
	var $tplVars = array();
	//模板目录
	var $tplPath = 'templates/';
	//编译目录
	var $comPath = 'templates_c/';
	//缓存目录
	var $cachePath = 'caches';
	//定界符
	var $delimit = array('{', '}', '<\!--\#', '-->');
	//是否开启缓存
	var $isCache = 1;
	
	function SCN_Template()
	{
		$const = get_defined_constants();
		$data['app'] = array(
			'now'		=> time(),
			'get'		=> &$_GET,
			'post'		=> &$_POST,
			'session'	=> &$_SESSION[APP_KEY],
			'request'	=> &$_REQUEST,
			'server'	=> &$_SERVER,
			'const'		=> &$const
		);
		
		global $URI;
		$data['CUR_CTRL'] = $URI->curCtrl;
		$data['CUR_ACT'] = $URI->curAct;
		$data['CUR_URL'] = $_SERVER['REQUEST_URI'];
		$this->assign($data);
	}
	
	function assign($var, $val = NULL)
	{
		if (is_string($var)) {
			$this->tplVars[$var] = $val;
		} elseif (is_array($var)) {
			$this->tplVars = array_merge($var, $this->tplVars);
		}
	}
	
	function compileVars(&$s)
	{
		$s = '$this->tplVars[\''.str_replace('.', '\'][\'', $s).'\']';
	}
	
	function compileFunc(&$s)
	{
		$p = explode(':', $s[2]);
		$func = array_shift($p);
		switch (trim($func))
		{
			case 'format_date':
				$p[0] = @implode(':', $p);
				$syntax = 'date('.$p[0].', '.$s[1].')';
				break;
			case 'default':
				$syntax = $s[1] . ' ? ' .$s[1]. ' : ' . $p[0];
				break;
			case 'isset':
				$syntax = 'isset(' . $s[1] . ') ? ' .$s[1]. ' : ' . $p[0];
				break;
			case 'bool':
				$syntax = $s[1] . ' ? ' .$p[0]. ' : ' . $p[1];
				break;
			case 'str_repeat':
				$syntax = $func . '('.$p[0].', '.$s[1].')';
				break;
			case 'explode':
				$syntax = $func . '('.$p[0].', '.$s[1].')';
				break;
			case 'implode':
				$syntax = $func . '('.$p[0].', '.$s[1].')';
				break;
			default:
				$this->loadFunc[] = $func;
				$syntax = $func . '('.$s[1]. ($p ? ', '.implode(',', $p) : '').')';
				break;
		}
		$this->tplContent = str_replace($s[0], $syntax . $s[3], $this->tplContent);
	}
	
	function compileSyntax(&$s)
	{
		switch (trim($s[2])) {
			case 'echo':
				break;
			case 'include':
				$s[3] = str_replace('file=', '', $s[3]);
				$syntax = $s[1] . '$this->require_tpl('.trim($s[3]).')' . $s[4];
				$this->tplContent = str_replace($s[0], $syntax, $this->tplContent);
				break;
			case 'if':
				$syntax = $s[1] . 'if('.trim($s[3]).') {' . $s[4];
				$this->tplContent = str_replace($s[0], $syntax, $this->tplContent);
				break;
			case 'else':
				$syntax = $s[1] . '} else {' . $s[4];
				$this->tplContent = str_replace($s[0], $syntax, $this->tplContent);
				break;
			case 'elseif':
				$syntax = $s[1] . '} elseif('.trim($s[3]).') {' . $s[4];
				$this->tplContent = str_replace($s[0], $syntax, $this->tplContent);
				break;
			case 'foreach':
				$syntax = $s[1] . '$loop = 0; foreach( (array)'.trim($s[3]).') { $loop++; ' . $s[4];
				$this->tplContent = str_replace($s[0], $syntax, $this->tplContent);
				break;
			case 'for':
				$syntax = $s[1] . 'for( (array)'.trim($s[3]).') { ' . $s[4];
				$this->tplContent = str_replace($s[0], $syntax, $this->tplContent);
				break;
			case 'assign':
				$this->tplContent = str_replace($s[2] . ' ', '', $this->tplContent);
				break;
		}
	
		preg_match_all('#\$([a-zA-Z0-9_\.]+)#', $s[3], $s1);
		$this->compVars[0] = array_merge((array)$this->compVars[0], $s1[0]);
		$this->compVars[1] = array_merge((array)$this->compVars[1], $s1[1]);
	}
	
	function compile($content)
	{
		$this->compVars = array();
		$this->tplContent = &$content;
		//过滤PHP
		$content = preg_replace('#<\?.*\?>#sU', '', $content);
		$content = preg_replace('#{(\$.*)}#U', '<!--#echo $1-->', $content);
		
		//编译方法
		$pattern = '#(\$\S+)\|(.*)(-->)#U';
		preg_match_all($pattern, $content, $s, PREG_SET_ORDER);
		array_walk($s, array($this, 'compileFunc'));
		
		//编译语句
		$pattern = '#(<\!--\#)(\w+?)(.*)(-->)#sU';
		preg_match_all($pattern, $content, $s, PREG_SET_ORDER);
		array_walk($s, array($this, 'compileSyntax'));
		
		//编译变量
		@array_multisort($this->compVars[0], SORT_DESC, SORT_STRING, $this->compVars[1], SORT_DESC, SORT_STRING);
		@array_walk($this->compVars[1], array($this, 'compileVars'));
		$this->tplContent = str_replace($this->compVars[0], $this->compVars[1], $this->tplContent);
		
		$tplPath = APP_ROOT . str_replace(array($_SERVER['DOCUMENT_ROOT'], DS), array('', '/'), $this->tplPath);
		$content = preg_replace('/src=\"\.\/(.*?)\"/', 'src="'.$tplPath.'\1"', $content);
		$content = preg_replace('/href=\"\.\/(.*?)\"/', 'href="'.$tplPath.'\1"', $content);
		$content = preg_replace('/background=\"\.\/(.*?)\"/', 'background="'.$tplPath.'\1"', $content);

		$content = preg_replace('/url\(\.\/(.*?)\)/', 'url('.$tplPath.'\1)', $content);
		$content = preg_replace('#<\!--\#\/.*-->#U', '<?php } ?>', $content);
		$content = preg_replace('#<\!--\#(.*)-->#U', '<?php $1 ?>', $content);
		$content = preg_replace('#\[php\]#', '<?php', $content);
		$content = preg_replace('#\[\/php\]#', '?>', $content);
		
		$this->loadFunc = @array_unique($this->loadFunc);
		foreach ((array)$this->loadFunc as $file) {
			if (file_exists(FUNC_DIR . $file . FUNC_EXT . PHP_EXT)) {
				$loadStr .= '<?php SCN_Loader::helper(\'' . $file . '\')?>' . "\r";
			}
		}
		
		return $loadStr . $content;
	}
	
	function require_tpl($tplFile, $addPath = 1)
	{
		$this->tplFile = $addPath ? $this->tplPath . $tplFile : $tplFile;
		if (strpos($tplFile, '/') !== false) {
			list($path, $file) = explode('/', $tplFile);
			$this->comPath .= $path . '/';
			$tplFile = $file;
		}
		$this->comFile = $this->comPath . $tplFile . '.php';
		if (file_exists($this->tplFile)) {
			//检测模板是否修改
			$s1 = @filemtime($this->tplFile);
			$s2 = @filemtime($this->comFile);
			if (!file_exists($this->comFile) || $s2 <= $s1) {
				$this->tplContent = file_get_contents($this->tplFile);
				$this->tplContent = $this->compile($this->tplContent);
				if (!is_dir($this->comPath)) @mkdir($this->comPath);
				//写文件
				SCN_Loader::import('File.lib',	LIBS_DIR);
				SCN_File::write($this->comFile, $this->tplContent);
			}
			require $this->comFile;
		} else {
			die('ERROR: Not found template ' . $this->tplFile);
		}
	}
	
	function output($tplFile, $data = 0, $addPath = 1)
	{
		global $kernel;

		$this->assign('LANG', $kernel->lang);
		$data && $this->assign($data);

		$this->require_tpl($tplFile, $addPath);
	}
	
	function display($tplFile, $data = 0, $stop = 0)
	{
		global $kernel;

		$this->assign('LANG', $kernel->lang);
		$data && $this->assign($data);
		$this->require_tpl($tplFile);
		
		$stop && die();
	}
	
	function display_wrap($wrap, $body)
	{
		$data['body'] = $this->output($body);
		$this->display($wrap, $data);
	}
	
	function debug()
	{
		unset($this->tplVars['app']);
		$str .= '<table width="100%" bgcolor="#cccccc" cellspacing="1">';
		foreach ($this->tplVars as $key => $var) {
			$str .= '<tr bgcolor="#ffffff" height="26"><th width="160">' . $key . '</th>';
			$str .= '<td><pre>';
			$str .= is_array($var) ? implode(',', $var) : $var;
			$str .= '</pre></td></tr>';
		}
		$str .= '</table>';
		return $str;
	}

}
?>