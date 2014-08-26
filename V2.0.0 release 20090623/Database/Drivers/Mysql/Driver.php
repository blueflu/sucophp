<?php
require 'Crud.php';
require 'Recordset.php';

if (!class_exists('SCN_Timer')) {
	require '../../../Core/Timer.php';
}

class SCN_Db_Exception extends SCN_Exception {}

class SCN_Db_Driver
{
	public $linkId;
	private $dsn = array();

	public function __construct($dsn)
	{
		$this->dsn = $dsn;
		$this->connect($dsn);
		$this->selectDb($dsn['name']);
	}
	
	public function connect($dsn)
	{
		$this->linkId = @mysql_connect($dsn['host'], $dsn['user'], $dsn['pass']);
		if (!$this->linkId) throw new SCN_Db_Exception('MYSQL ERROR:' . $this->error(), 1);
	}
	
	public function selectDb($dbName)
	{
		$this->execute("USE {$dbName}");
	}
	
	public function disconnect()
	{
		mysql_close($this->linkId);
	}
	
	public function crud($tbName, $tbAlias = '')
	{
		$this->$tbName = new SCN_Db_Crud($this->tbPrefix . $tbName, $tbAlias, $this);
		return $this->$tbName;
	}
	
	public function getVersion()
	{
		return $this->execute('SELECT VERSION() as Version')->fetchCol('Version');
	}
	
	public function setCharset($charset)
	{
		$this->execute('SET NAMES ' . $charset);
	}
	
	public function setTbPrefix($prefix)
	{
		$this->tbPrefix = $prefix;
	}
	
	public function execute($query)
	{
		SCN_Timer::mark('queryTime');
		$resource = @mysql_query($query);
		if (mysql_errno()) {
			throw new SCN_Db_Exception('MYSQL ERROR: ' . $query . '<br/>' .$this->error(), 1);
		}

		$this->result = &new SCN_Db_Recordset($resource);
		$this->querys[] = array(
			'query' => $query, 
			'result' => $this->affectedRows(), 
			'error' => $this->error(), 
			'runtime' => SCN_Timer::executeTime('queryTime')
		);
		return $this->result;
	}
	
	public function multiExec($query)
	{
		$this->splitSql($querys, $query);
		if (count($querys) == 1) {
			return $this->execute($querys[0]);
		}
		
		foreach ($querys as $query) {
			$this->execute(trim($query));
		}
		return false;
	}
	
	public function showTables()
	{
		$tables = $this->execute('SHOW TABLES');
		return $tables->fetchCols('Tables_in_'.$this->dsn['name']);
	}
	
	public function dumpTable($tbName)
	{
		$sql = "DROP TABLE IF EXISTS $tbName;\n";
		$tables = $this->execute('SHOW CREATE TABLE '.$tbName);
		$record = $tables->fetchRow();
		$sql .= $record['Create Table'] . ';';
		return $sql;
	}
	
	public function dumpRecord($tbName)
	{
		$tables = $this->execute('SELECT * FROM '.$tbName);
		$record = $tables->fetchAll();

		foreach ((array)$record as $row) {
			array_walk($row, array($this, 'addEscapeString'));
			$sql .= 'INSERT INTO '.$tbName.' VALUES ('.implode(',', $row).');' . "\r\n";
		}
		return $sql;
	}

	public function addEscapeString(&$string)
	{
		$string = ($string ? '\'' . mysql_escape_string($string) . '\'' : 'NULL');
		return;
	}
	
	public function affectedRows()
	{
		return mysql_affected_rows($this->linkId);
	}
	
	public function error()
	{
		return mysql_errno() . ' ' . mysql_error();
	}
	
	public function debug()
	{
		foreach ((array)$this->querys as $item) {
			$string .= '['.round($item['runtime'], 4).'ms]	'.$item['query'].' (<strong>'.$item['result'].'</strong>)'."\r\n";
			$totalTime += $item['runtime'];
			$totalRecord += $item['result'];
		}
		$string .= "\r\n".'<strong>Total record:'.$totalRecord.'	Total process time:'.round($totalTime, 6) . 'ms</strong>';
		return '<pre style="background:#333; color:#fff; padding:8px;"><p style="font-weight:bold; font-size:24px;">SQL DEBUG -----------------</p>'.$string.'</pre>';
	}

	public function splitSql(&$ret, $sql)
	{
		 $sql          = trim($sql);
		 $sql_len      = strlen($sql);
		 $char         = '';
		 $string_start = '';
		 $in_string    = false;
		 $time0        = time();
	
		 for($i = 0; $i < $sql_len; ++$i)
		 {
			  $char = $sql[ $i ];
	
			  if($in_string)
			  {
					while(true)
					{
						 $i	= strpos($sql, $string_start, $i);
	
						 if(!$i)
						 {
							  $ret[]  = $sql;
							  return TRUE;
						 }
	
						 else if($string_start == '`' || $sql[ $i-1 ]  != '\\')
						 {
							  $string_start      = '';
							  $in_string         = false;
							  break;
						 }
						 else
						 {
							  $j                     = 2;
							  $escaped_backslash     = false;
	
							  while ($i-$j > 0 && $sql[ $i-$j ]  == '\\')
							  {
									$escaped_backslash = !$escaped_backslash;
									$j++;
							  }
	
							  if($escaped_backslash)
							  {
									$string_start  = '';
									$in_string     = false;
									break;
							  }
							  else
							  {
									$i++;
							  }
						 }
					}
			  }
			  else if($char == ';')
			  {
					$ret[]       = substr($sql, 0, $i);
					$sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
					$sql_len    = strlen($sql);
					if($sql_len)
					{
						 $i      = -1;
					}
					else
					{
						 return TRUE;
					}
			  }
			  else if(($char == '"')  || ($char == '\'')  || ($char == '`'))
			  {
					$in_string    = TRUE;
					$string_start = $char;
			  }
			  else if($char == '#'
							|| ($char == ' ' && $i > 1 && $sql[ $i-2 ] . $sql[ $i-1 ]  == '--'))
			  {
	
					$start_of_comment = (($sql[ $i ]  == '#')  ? $i : $i-2);
	
					$end_of_comment   = (strpos(' ' . $sql, "\012", $i+2))
											  ? strpos(' ' . $sql, "\012", $i+2)
											  : strpos(' ' . $sql, "\015", $i+2);
					if(!$end_of_comment)
					{
	
						 if($start_of_comment > 0)
						 {
							  $ret[]     = trim(substr($sql, 0, $start_of_comment));
						 }
						 return TRUE;
					}
					else
					{
						 $sql          = substr($sql, 0, $start_of_comment) . ltrim(substr($sql, $end_of_comment));
						 $sql_len      = strlen($sql);
						 $i--;
					}
			  }
	
			  $time1     = time();
	
			  if($time1 >= $time0 + 30)
			  {
					$time0 = $time1;
					header('X-pmaPing: Pong');
			  }
		 }
	
		 if(!empty($sql) && trim($sql))
		 {
			  $ret[]  = $sql;
		 }
	
		 return true;
	}

}
?>