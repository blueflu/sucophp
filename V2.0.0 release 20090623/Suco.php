<?php
	session_start();
	set_magic_quotes_runtime(0);
	error_reporting(E_ALL & ~E_NOTICE);

	header("Content-type: text/html; charset=" . CHARSET);
	date_default_timezone_set(TIMEZONE);
	
	define('IN_SUCOPHP',	true);
	define('SYS_VER',		'1.03');
	define('PHP_VER',		intval(PHP_VERSION));

	define('CLS_PREFIX',	'SCN_');
	define('PHP_EXT',		'.php');
	
	define('MODE_EXT',		'.mod');
	define('CORE_EXT',		'.core');
	define('LIBS_EXT',		'.lib');
	define('LANG_EXT',		'.lang');
	define('FUNC_EXT',		'.helper');
	
	define('CORE_DIR',		SYS_PATH . 'Core' . DS);
	define('LIBS_DIR',		SYS_PATH . 'Librarys' . DS);
	define('FUNC_DIR',		SYS_PATH . 'Helper' . DS);
	define('DATABASE_DIR',	SYS_PATH . 'Database' . DS);
	
	require_once CORE_DIR . 'Loader.core' . PHP_EXT;

	//引入核心类库	
	SCN_Loader::import('Base.core', 		CORE_DIR);
	SCN_Loader::import('Exception.core',	CORE_DIR);
	SCN_Loader::import('Timer.core', 		CORE_DIR);
	SCN_Loader::import('Output.core',		CORE_DIR);
	SCN_Loader::import('Configure.core', 	CORE_DIR);
	SCN_Loader::import('Controller.core', 	CORE_DIR);
	SCN_Loader::import('Dispatcher.core', 	CORE_DIR);
	SCN_Loader::import('Uri.core', 			CORE_DIR);
	SCN_Loader::import('Acl.core',			CORE_DIR);

	class Suco
	{
		public $uriMode = SCN_Uri::URI_STANDARD;
		public $uriCtrlKeyName = 'mod';
		public $uriActKeyName = 'act';
		public $uriDefaultController = 'index';
		public $uriDefaultAction = 'default';
		public $uriDelimit1 = '/';
		public $uriDelimit2 = '-';
		public $uriDelimit3 = '_';
		public $uriHtmlExt = '.html';
		
		public $controllerPrefix = '';
		public $controllerSuffix = '';
		public $actionPrefix = 'do';
		public $actionSuffix = '';
		
		public function run()
		{
			global $kernel, $URI, $DPA;	

			//URI解析
			$URI = new SCN_Uri($this->uriMode);
			$URI->ctrlKeyName = $this->uriCtrlKeyName;
			$URI->actKeyName = $this->uriActKeyName;
			$URI->defaultCtrl = $this->uriDefaultController;
			$URI->defaultAct = $this->uriDefaultAction;
			$URI->delimit1 = $this->uriDelimit1;
			$URI->delimit2 = $this->uriDelimit2;
			$URI->delimit3 = $this->uriDelimit3;
			$URI->htmlExt = $this->uriHtmlExt;
			$URI->parseUri();
			
			//控制器调度
			$DPA = new SCN_Dispatcher();
			$DPA->controllerPath = CTRL_DIR;
			$DPA->controllerPrefix = $this->controllerPrefix;
			$DPA->controllerSuffix = $this->controllerSuffix;
			$DPA->actionPrefix = $this->actionPrefix;
			$DPA->actionSuffix = $this->actionSuffix;
			$DPA->controllerName = $URI->curCtrl;
			$DPA->actionName = $URI->curAct;
			$DPA->run();
		}
	}
?>