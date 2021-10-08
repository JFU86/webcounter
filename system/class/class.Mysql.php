<?php
/*
	Mysql Connection Class
	from Database Framework
	by Scripthosting.net
	
	Licensed under the GPLv3 
	http://www.gnu.org/copyleft/gpl.html
*/

class Mysql
{	
	private $isConnected = false;
	private $host = ""; 
	private	$username = ""; 
	private	$userpass = ""; 
	private	$database = "";
	private	$port = 3306;
	private	$socket = ""; 
	private $returnerror = true; 
	private $autocommit = true;
	private $dbCon = null;	
	private $queryCounter = 0;
	private $queryArray = array();
	private $resultArray = array();
	private $errors = array();
	private $config = array();
	private $logPath = null;	
	
	/**
	 * Create a new MySQL connection object.
	 * @param string $host
	 * @param string $username
	 * @param string $userpass
	 * @param string $database
	 * @param int $port [3306]
	 * @param string $socket
	 * @param boolean $autocommit [true]
	 * @throws SQLException
	 * @return void
	 */
	public function __construct($host = "", $username = "", $userpass = "", $database = "", $port = 3306, $socket = "")
	{		
		$this->config = $GLOBALS["config"];
		
		if ($host != "") {
			$this->host = $host;
			$this->username = $username;
			$this->userpass = $userpass;
			$this->database = $database;
			$this->port = $port;
			$this->socket = $socket;
		} else {
			if (isset($this->config["db_host"])) {		
				$this->host = $this->config["db_host"];
				$this->username = $this->config["db_username"];
				$this->userpass = $this->config["db_userpass"];
				$this->database = $this->config["db_database"];
				$this->port = $this->config["db_port"];
				$this->socket = $this->config["db_socket"];
			} else {
				throw new SQLException("Could not find database settings.");
			}
		}
	}	
	
	/**
	 * Connect to the MySQL Database
	 * @return mysqli
	 */
	private function connect()
	{		
		if ($this->host == "" || $this->database == "") {
			$this->__construct();
		}
		
		if ($this->isConnected == true && $this->dbCon != null) {
			return $this->dbCon;
		} else {
			$mysqli = @new mysqli($this->host, $this->username, $this->userpass, $this->database, $this->port, $this->socket);
			
			if ($mysqli->connect_error != "" && $this->returnerror == true) {
				echo "<div style=\"font-family:Arial,Verdana,sans-serif;font-size:20px;\">\r\n\t<b>Die gewünschte Webseite ist momentan nicht erreichbar. Bitte versuchen Sie es später erneut</b> (Error -1). \r\n</div>";
				exit;
			} else {
				if ($mysqli->connect_error == "") {
					@$mysqli->query("SET NAMES 'UTF8'");
					
					if (!$this->autocommit) {
						$this->setAutoCommitState(false);
					}
				}
				$this->dbCon = $mysqli;
			}
			$this->isConnected = ($mysqli != null && $mysqli->error == "" && $mysqli->connect_error == "") ? true : false;
	
			return $mysqli;
		}
	}
	
	/**
	 * Create a new sql statement.
	 * @param string $sql
	 * @return mysqli_stmt
	 */
	private function preparedStatement($sql)
	{
		$mysqli = $this->connect();
		return $mysqli->prepare($sql);
	}
	
	/**
	 * Run an sql command and return the mysql connection object.
	 * @param string $sql
	 * 	sql command
	 * @param boolean $error
	 * 	return a message on error
	 * @return mysqli
	 */
	public function query($sql, $error = true)
	{
		$mysqli = $this->connect();		
		if (empty($sql)) return $mysqli;
		$stmt = $this->preparedStatement($sql);
		
		if ($error) {			
			if (($query = $stmt->execute()) == false) {
				$err = $mysqli->error;
				if ($this->autocommit == false) {
						$this->dbCon->rollback();
				}
				$this->logError("MySQL threw an error: {$err} in SQL-Statement: {$sql}");
				exit(
					"<p><b>MySQL threw an error:</b> <span style=\"color:#FF0000;\">{$err}</span><br />".
					"SQL statement: <span style=\"color:#000080;\">{$sql}</span></p>"
				);
			}
		} else {
			if (($query = $stmt->execute()) == null) {
				$this->errors[] = $mysqli->error;
			}
		}
		
		$this->queryCounter += 1;
		$this->queryArray[] = $sql;

		$stmt->close();
		return $mysqli;
	}	
	
	/**
	 * Run an sql select statement and return a resultset
	 * @param string $sql
	 * @param boolean $error
	 * 	return a message on error
	 * @return mysqli_result
	 */
	public function result($sql, $error = true)
	{
		$mysqli = $this->connect();		
		if (empty($sql)) return null;
 
		if ($error) {
			if (($result = $mysqli->query($sql)) == false) {
				$err = $mysqli->error;
				$this->logError("MySQL threw an error: {$err} in SQL-Statement: {$sql}");
				exit(
					"<p><b>MySQL threw an error:</b> <span style=\"color:#FF0000;\">{$err}</span><br />".
					"SQL statement: <span style=\"color:#000080;\">{$sql}</span></p>"
				);
			}
		} else {
			if (($result = $mysqli->query($sql)) == null) {
				$this->errors[] = $mysqli->error;
			}
		}
		
		$this->queryCounter += 1;
		$this->queryArray[] = $sql;
		$this->resultArray[] = $result;
		
		return $result;
	}
	
	/**
	 * Gibt ein einzelnes $result als Array zurück
	 * @param string $sql
	 * @return array
	 */
	public function resultRow($sql)
	{
		$result = $this->result($sql);
		if ($this->numRows($result) != 0) {
			return (array) $this->fetchAssoc($result);
		} else {
			return array();
		}
	}	
	
	/**
	 * Prepare an sql string.
	 * @param string $string
	 * @param boolean $decode
	 * @param boolean $htmlOutput
	 * @return string
	 */
	public function clean($string, $decode = false, $htmlOutput = false)
	{
		$string = trim($string);
		
		// Is the string base64 encoded?
		if ($decode) 
			$string = (function_exists('get_magic_quotes_gpc') && (int) get_magic_quotes_gpc() == 1) 
				? addslashes(trim(base64_decode($string))) 
				: trim(base64_decode($string));

		// Is the old 'magic_quotes' functionality active?
		if (!function_exists('get_magic_quotes_gpc') || (
				function_exists('get_magic_quotes_gpc') && (int) get_magic_quotes_gpc() == 0
			)
		) $string = addslashes($string);

		// Do we need an html secure output?
		if ($htmlOutput) {
			$xml = new XML(); 
			$string = $xml->htmlOutput($string); 
		}

		return $string;
	}	
	
	/**
	 * Database Test:
	 * Check if a connection to the database can be established
	 * and if the persmissions for create and drop are available.
	 * @return boolean
	 */
	public function connectionTest()
	{	
		$this->returnerror = false;
		$this->setAutoCommitState(true);
				
		$query = @$this->query("CREATE TABLE IF NOT EXISTS `{$this->database}`.`test_db` (`test_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (`test_id`)) ENGINE = InnoDB;", false);
		$query = @$this->query("INSERT INTO `{$this->database}`.`test_db` (test_id) VALUES ( 1 );", false);		
		$result = @$this->result("SELECT test_id FROM `{$this->database}`.`test_db` WHERE test_id = 1", false);	

		if ($this->numRows($result) > 0) {			
			$query = @$this->query("DROP TABLE `{$this->database}`.`test_db`", false);
			return true;
		}		
		return false;
	}
	
	/**
	 * Return a new or the existing database connection.
	 * @return mysqli
	 */
	public function getConnection()
	{
		return $this->connect();
	}	
	
	/**
	 * Return the MySQL-Server version.
	 * @return string
	 */
	public function getVersion()
	{
		$resultRow = $this->resultRow("SELECT VERSION() version");
		return $resultRow["version"];
	}	
	
	/**
	 * Return the number of rows in the result.
	 * @param mysqli_result $result
	 * @return int
	 */
	public function numRows(mysqli_result $result)
	{
		return (int) $result->num_rows;
	}	
	
	/**
	 * Return an array of the result.
	 * @param mysqli_result $result
	 * @return array
	 */
	public function fetchAssoc(mysqli_result $result)
	{
		return $result->fetch_assoc();
	}	
	
	/**
	 * Return the last inserted identity.
	 * @param mysqli $query
	 * @return int
	 */
	public function insertID(mysqli $query)
	{
		return (int) $query->insert_id;
	}	
	
	/**
	 * Return the number of affected rows by a query.
	 * @param mysqli $query
	 * @return int
	 */
	public function affectedRows(mysqli $query)
	{
		return (int) $query->affected_rows;
	}	
	
	/**
	 * Return the current database datetime string.
	 * @return string
	 */
	public function getSQLDateTime()
	{
		$resultRow = $this->resultRow("SELECT NOW() as datum");
		return $resultRow["datum"];
	}	
	
	/**
	 * Return the name of the sql random() function.
	 * @return string
	 */	
	public function getRandomFunctionName()
	{
		return "RAND";
	}	
	
	/**
	 * Return the current autocommit state.
	 * @return boolean
	 */
	private function getAutoCommitState()
	{
		$resultRow = $this->resultRow("select @@autocommit state");
		$this->autocommit = (boolean) $resultRow["state"];
		return $this->autocommit;
	}	
	
	/**
	 * Set or change the current autocommit state.
	 * @param boolean $bool
	 * @return void
	 */
	public function setAutoCommitState($bool)
	{		
		if ($this->dbCon == null) {
			$this->connect();
		}
		
		if ($this->dbCon != null) {		
			if ($bool) {
				@$this->dbCon->autocommit(true);
				$this->autocommit = true;
			} elseif (!$bool) {
				$this->dbCon->autocommit(false);
				$this->autocommit = false;
			}
		} else {
			throw new SQLException("Unable to change autocommit state while not connected!");
		}
	}	
	
	/**
	 * Return an array of errors in this connection.
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}	
	
	/**
	 * Try to log an error to a file.
	 * @param string $error
	 * @return void
	 */
	private function logError($error)
	{		
		$logPath = ($this->logPath != null) ? realpath( $this->logPath ) : realpath(dirname(__FILE__));
		
		if (is_writable($logPath)) {
			$datum = date("c");
			$open = @fopen($logPath . "/class.Mysql.log","a");
			$write = @fwrite($open, "[{$datum}]: {$error}\r\n\r\n");
			$close = @fclose($open);
		}
	}	
	
	/**
	 * Set the log path.
	 * @param $logPath
	 * @return void
	 */
	public function setLogPath($logPath)
	{
		$this->logPath = $this->clean($logPath);
	}
	
	/**
	 * Run a full backup of the database (WDF Version 1.1).
	 * @param string $mode
	 * 		Set the format (default "sql").
	 * 		"sql": plain (low memory usage, highest hard disk usage),
	 * 		"wdf": Encrypted and compressed in wdf format (high memory usage, lowest disk space usage),
	 * 		"gzip": GZIP-compression (medium memory and hard disk usage).
	 * @return string fileName
	 */
	public function backupDatabase($mode = "sql")
	{		
		@ini_set("memory_limit","-1");
		@ini_set("max_execution_time", 300);
		
		$sql = "";
		
		// Backup path.
		$path = $this->config["system_path"] . "/backup";
		$tmpFileName = $this->database."_".time() . ".sql";
		$tmpFile = $path . "/" . $tmpFileName;

		// SQL-Header
		$sql .= "-- @CHARSET UTF-8" . "\r\n";
		$sql .= "-- Database: `{$this->database}`" . "\r\n";
		$sql .= "-- Date: ". date("c")."\r\n";
		$sql .= "-- Server Version: ". $this->getVersion() ."\r\n\r\n";
		$sql .= "-- PHP Version: ". phpversion() ."\r\n\r\n";
		$sql .= "SET FOREIGN_KEY_CHECKS=0;" . "\r\n/*SPLIT*/\r\n";
		$sql .= "SET UNIQUE_CHECKS=0;" . "\r\n/*SPLIT*/\r\n";
		$sql .= "SET NAMES 'UTF8';" . "\r\n/*SPLIT*/\r\n";
		$sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';" . "\r\n/*SPLIT*/\r\n";
		
		// Write to temporary output file.
		file_put_contents($tmpFile, $sql, FILE_APPEND | LOCK_EX);
		$sql = "";
		
		// Read all database tables.
		$query = $this->query("SET SQL_QUOTE_SHOW_CREATE=1;");	// Einfache Anführungszeichen abschalten
		$query = $this->query("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';");
		$result = $this->result("SELECT TABLE_NAME,TABLE_COMMENT FROM information_schema.tables WHERE TABLE_SCHEMA = '{$this->database}' AND TABLE_TYPE = 'BASE TABLE'");
		
		while ($row = $this->fetchAssoc($result)) {
		
			$sql = "";
			$tableName = $row["TABLE_NAME"];
			
			// Read Storage-Engine
			$resEngine = $this->resultRow("SHOW TABLE STATUS WHERE Name = '{$tableName}'");
			$storageEngine = $resEngine["Engine"];
		
			######## CREATE TABLE ############################################################################
			// Read all CREATE scripts for this table.
			$fields = array();
			$uks = array(); // UNIQUE keys
			$nfd = array(); // NULL fields
			$ifd = array(); // Integer fields
			
			$resCreate = $this->resultRow("SHOW CREATE TABLE `{$tableName}`");
			$sql .= "DROP TABLE IF EXISTS `{$tableName}`" . ";\r\n/*SPLIT*/\r\n";
			$sql .= $resCreate["Create Table"] . ";\r\n/*SPLIT*/\r\n";
			
			// Read all fields of the current table.
			$resCols = $this->result("SHOW COLUMNS FROM {$tableName}");
			while ($rowCols = $this->fetchAssoc($resCols)) {
				$fields[] = "`". $rowCols["Field"] ."`";
				if ($rowCols["Null"] == "YES") $nfd[] = $rowCols["Field"];	// NULL
				if ($rowCols["Key"] == "UNI") $uks[] = $rowCols["Field"];	// UNIQUE
				if ((strpos($rowCols["Type"], "int") !== false 
					|| strpos($rowCols["Type"], "bit") !== false) 
					&& strpos($rowCols["Type"],"enum") === false) $ifd[] = $rowCols["Field"];	// Integer
			}			
			######## INSERT INTO #############################################################################
			// Start a transaction, if we have InnoDB
			if ($storageEngine == "InnoDB") {
				$sql .= "START TRANSACTION;\r\n/*SPLIT*/\r\n";
			}
			file_put_contents($tmpFile, $sql, FILE_APPEND | LOCK_EX);
			$sql = "";
			
			// Read all data from table.
			$resCount = $this->resultRow("SELECT count(*) AS count FROM `{$tableName}`");
			$itemCount = $resCount["count"];
			$itemPageSize = 1000; // Rows per page.
			
			$k = 0;
			while ($k < $itemCount) {
				$res = $this->result("SELECT * FROM `{$tableName}` LIMIT {$k},{$itemPageSize}");				
				while ($data = $this->fetchAssoc($res)) {
					$i = 0;
					$sql .= "INSERT INTO `{$tableName}` (". implode(",",$fields) .") VALUES (";
				
					foreach ($data as $key => $value) {
						if ($i > 0) $sql .= ",";
						$sql .= (addslashes(trim($value)) == "" && in_array($key,$nfd) ) ? "NULL" :
						( in_array($key,$ifd) ? trim($value) : "'". addslashes(trim($value)) ."'" );
						$i++;
					}
					// Write to temporary output file.
					file_put_contents($tmpFile, $sql, FILE_APPEND | LOCK_EX);
					$sql = ");\r\n/*SPLIT*/\r\n";
				}
				$k += $itemPageSize;
			}		
			######## CREATE/INSERT END #######################################################################
			// Commit, if possible
			if ($storageEngine == "InnoDB") {
				$sql .= "COMMIT;\r\n/*SPLIT*/\r\n";
			}			
			file_put_contents($tmpFile, $sql, FILE_APPEND | LOCK_EX);
			$sql = "";
		}		
		######## CREATE VIEWS ################################################################################
		// Read all views.
		$result = $this->result(
			"SELECT TABLE_NAME FROM information_schema.views WHERE TABLE_SCHEMA = '{$this->database}'"
		);
		while ($row = $this->fetchAssoc($result)) {			
			$resultRow = $this->resultRow("show create view {$row["TABLE_NAME"]}");
			$sql .= "DROP VIEW IF EXISTS `{$row["TABLE_NAME"]}`" . ";\r\n/*SPLIT*/\r\n";
			$data = explode("VIEW", $resultRow["Create View"], 2);
			$sql .= "CREATE VIEW ". trim($data[1]) . ";\r\n/*SPLIT*/\r\n";
		}
		file_put_contents($tmpFile, $sql, FILE_APPEND | LOCK_EX);
		$sql = "";
		######## CREATE VIEWS ENDE ###########################################################################
		// Backup mode:
		if ($mode == "gzip" && function_exists('shell_exec') && (int) ini_get('safe_mode') == 0 
			&& !in_array('shell_exec', explode(",", ini_get('disable_functions')))
		) {
			$exec = shell_exec("gzip -f -9 {$path}/{$tmpFileName}");
			$fileName = $tmpFileName;
		} elseif ($mode == "sql") {
			$fileName = $tmpFileName;
		}
		return $fileName;
	}	
	
	/**
	 * Restore a database from backup.
	 * @param string $fileName 
	 * 		Filename of the backup file:
	 * 		Supported formats are ".sql" and ".sql.gz".
	 * @return void
	 */
	public function restoreDatabase($fileName)
	{		
		@ini_set("memory_limit","-1");
		@ini_set("max_execution_time", 300);
		
		// Backup path.
		$path = $this->config["system_path"] . "/backup";
		
		// Read the sql file to a string.
		$input = file_get_contents($path . "/" . $fileName);
		$unlink = true;
		
		// Get file type:
		if (substr($fileName,-7) == ".sql.gz") {
			$open = gzopen($path . "/" . $fileName, "r");
			$content = gzread($open, filesize($path . "/" . $fileName));
			$close = gzclose($open);
			file_put_contents($path . "/" . $fileName . ".sql", $content);
		} elseif (substr($fileName, -4) == ".sql") {
			$fileName = substr($fileName, 0, -4);
			$unlink = false;
		} else {
			throw new Exception("Unsupported file format: restore aborted.");
			return;
		}

		unset($input);

		if (function_exists('shell_exec') && (int) ini_get('safe_mode') == 0 
		&& !in_array('shell_exec', explode(",", ini_get('disable_functions'))) && PHP_OS != "WINNT") {
			$exec_command = "mysql -h {$this->host} -P {$this->port} -u {$this->username} ";
			if ($this->userpass != null) $exec_command .= "-p{$this->userpass} ";
			if ($this->socket != null) $exec_command .= "-S {$this->socket} ";
			$exec_command .= "{$this->database} < {$path}/{$fileName}.sql";
			$exec = shell_exec(trim($exec_command));
		} else {
			$sql = explode("/*SPLIT*/", file_get_contents($path . "/" . $fileName . ".sql"));

			foreach ($sql as $value) {
				if (trim($value) != "")
					$this->query(trim($value));
			}
		}

		if ($unlink) @unlink($path . "/" . $fileName . ".sql");
	}
	
	public function __destruct()
	{			
		if ($this->isConnected) {			
			if ($this->autocommit == false) {
				if ($this->dbCon->commit() == false) {
					throw new SQLException("Could not commit transaction: ". print_r($this->queryArray, true));
				}
			}
			
			if ($this->config["debug"] == true) {
				$class = ($this->caller == null) ? get_class($this) : $this->caller;
				$_SESSION["db"]["queryCount"] += $this->queryCounter;
				
				$i = 0;
				while ($_SESSION["db"]["queryExec"][$class][$i] != null) $i++;
				
				$_SESSION["db"]["queryExec"][$class][$i] = $this->queryArray;				
				$_SESSION["db"]["isConnected"] = true;
			}
			
			foreach ($this->resultArray as $value) {
				if ($value != null && $value !== false && get_class($value) == "mysqli_result") {
					@$value->close();
				}
			}

			$this->dbCon->close();
		}
	}
}

if (!class_exists('SQLException', false)) {
	class SQLException extends Exception 
	{
		public function __construct($message, $code = 0) 
		{
	        parent::__construct($message, $code);
	    }
	    public function __toString() 
	    {
	        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	    }
	}
}

if (!class_exists('SQLQueryException', false)) {
	class SQLQueryException extends Exception 
	{
		public function __construct($message, $code = 0) 
		{
	        parent::__construct($message, $code);
	    }
	    public function __toString() 
	    {
	        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	    }
	}	
}

if (!class_exists('SQLResultException', false)) {
	class SQLResultException extends Exception 
	{
		public function __construct($message, $code = 0) 
		{
	        parent::__construct($message, $code);
	    }
	    public function __toString() 
	    {
	        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	    }	
	}
}
?>