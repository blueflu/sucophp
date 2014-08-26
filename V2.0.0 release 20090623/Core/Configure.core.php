<?php

/* ===================================================================
 * SucoPHP Framework
 *
 * Copyright (c) 2008, Suconet, Inc.
 * License http://www.suconet.com/license
 * -------------------------------------------------------------------
 *
 * 程序设置类
 *
 * @version v1.02
 * @created	blueflu (2009-01-17 22:39PM)
 */

class SCN_Configure
{
	
	/*
	 * 存储变量 
	 */
	
	protected static function &config()  {
		static $config = array();
		return $config;
	}
	
	/*
	 * 装载配置文件
	 * params string $fileName
	 */

	public function load($fileName, $path = '')
	{
		static $loaded = array();
		
		$key = md5($fileName);
		if (!isset($loaded[$key])) {
			if (!is_file($path . $fileName . PHP_EXT)) {
				//die('ERROR: Load configure: <strong>'. $path . $fileName . PHP_EXT . '</strong> fail');
				return false;
			}
			require_once $path . strtolower($fileName) . PHP_EXT;

			$config = &SCN_Configure::config();
			$config = array_merge($config, (array)$_CONFIG);
			$loaded[$key] = true;
		}
	}
	
	public function destroy($keyName)
	{
		$file = CFG_DIR . $keyName . '.conf' . PHP_EXT;
		unlink($file);
		unset($keyName);
	}
	
	public function save($keyName)
	{
		$config = &SCN_Configure::config();
		$tmp = explode('.', $keyName);
		$file = CFG_DIR . strtolower($tmp[0]) . '.conf' . PHP_EXT;

		$content = "<?php\r";
		if (is_array($config[$keyName])) {
			foreach ($config[$keyName] as $key => $item) {
				if (is_array($item)) {
					$item = self::formatArray($item);	
				}
				$content .= '$_CONFIG[\''.$keyName.'\'][\''.$key.'\'] = '.$item.";\r";
			}
		} else {
			$config[$keyName] = is_string($config[$keyName]) ? '\''.$config[$keyName].'\'' : $config[$keyName];
			$content .= '$_CONFIG[\''.$keyName.'\'] = '.$config[$keyName].";\r";
		}
		$content .= "?>";

		if (is_writable(dirname($file))) {
			$handle = fopen($file, 'w');
			flock($handle, LOCK_EX);
			fwrite($handle, $content);
			flock($handle, LOCK_UN);
			fclose($handle);
		}
	}
	
	private function formatArray($arr)
	{
		static $lv = 1;
		$content = "array(\r";
		foreach ($arr as $key => $item) {
			$content .= str_repeat('	', $lv);
			if (is_array($item)) {
				$lv++;
				$content .= "'{$key}' => ".self::formatArray($item).", \r";
			} else {
				$content .= "'{$key}' => '$item', \r";
			}
		}
		$content .= str_repeat('	', $lv-1) . ")";
		$lv = 1;
		return $content;
	}
	
	/*
	 * 设置一项配置
	 * params mixed $name
	 * params mixed $value
	 */

	public function set($name, $value = '') {
		$config = &SCN_Configure::config();
		if (is_array($name)) {
			$config = array_merge($config, $name);
			return;
		}
		
		$key = str_replace('.', '][', $name);
		eval('$config['.$key.'] = $value;');
	}
	
	/*
	 * 返回一项配置 
	 * @params string $name
	 * @return mixed
	 */
	
	public function &get($name = NULL) {
		self::load($name . '.conf', CFG_DIR);
		$config = &SCN_Configure::config();
		if ($name === NULL) {	return $config;	}

		$key = str_replace(".", "']['", $name);
		eval('$return = &$config[\''.$key.'\'];');
		return $return;
	}
	
	public function write($fileName, $arr)
	{
		$content = '<?php ' . chr(13);
		foreach( $arr as $k => $v) {
			$tmp = var_export($v, true);
			$content .= '$_CONFIG[\''.$k.'\'] = ' . $tmp . ';' . chr(13);
		}
		$content .= 'return $_CONFIG;' . chr(13) . '?>';
		
		$handle = fopen($fileName, 'w+');
		flock($handle, LOCK_EX);
		fwrite($handle, $content);
		flock($handle, LOCK_UN);
		fclose($handle);
	}
}

?>