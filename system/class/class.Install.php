<?php
/*
	WebCounter
	by Scripthosting.net
	
	Licensed under the "GPL Version 3, 29 June 2007"
	http://www.gnu.org/licenses/gpl.html
	
	Support-Forum: http://board.scripthosting.net/viewforum.php?f=7
	Don't send emails asking for support!!
*/

class Install
{	
	/**
	 * Schreibt die Configdatei
	 */
	public function writeConfig()
	{	
		global $config;
	
		$vars = array(
			"{databaseType}"	=>	$_REQUEST["database"],
			"{timeformat}"		=>	$_REQUEST["timeformat"],
			"{dbhost}"			=>	$_REQUEST["db_hostname"],
			"{dbport}"			=>	$_REQUEST["db_port"],
			"{dbuser}"			=>	$_REQUEST["db_username"],
			"{dbpass}"			=>	$_REQUEST["db_userpass"],
			"{dbname}"			=>	$_REQUEST["db_database"],
			"{dbsocket}"		=>	$_REQUEST["db_socket"],
		);	
		
		$tpl = new Template();
		$newData = $tpl->output($config["system_path"] . "/temp/config.txt", $vars);
		$writeConfigFile = file_put_contents($config["system_path"] . "/config/config.inc.php", $newData);
	}
		
	
	/**
	 * Installiert WebCounter auf der Datenbank
	 * @return void
	 */
	public function databaseInstall()
	{	
		global $config;
	
		// SQLite Database
		if (in_array($_REQUEST["database"], array("Sqlite"))) {
			// SQLite Datenbankdatei anlegen
			if (!file_exists($config["system_path"] . "/sqlite/webcounter.db")) {
				$put = file_put_contents($config["system_path"] . "/sqlite/webcounter.db", "");
				$chmod = chmod($config["system_path"] . "/sqlite/webcounter.db", 0777);
			}
			$sql = file_get_contents($config["system_path"] . "/temp/sqlite.sql");
		}
		// MySQL Database
		elseif (in_array($_REQUEST["database"], array("Mysql"))) {			
			$dbCon = new Mysql();
			if (!$dbCon->connectionTest())
				die("Connection to MySQL Database failed! Please go back and check the database configuration!");
			
			$sql = file_get_contents($config["system_path"] . "/temp/mysql.sql");
		}
		// No Database
		else {
			die("Unknown or incompatible database selected! Please go back and select a valid database!");
		}
		
		// Delete temp files
		@unlink($config["system_path"] . "/temp/sqlite.sql");
		@unlink($config["system_path"] . "/temp/sqlite-update.sql");
		@unlink($config["system_path"] . "/temp/mysql.sql");
		@unlink($config["system_path"] . "/temp/mysql-update.sql");
		@unlink($config["system_path"] . "/temp/config.txt");

		$dbCon = new Database();
		$part = explode("/* SPLIT */", trim($sql));
		foreach ($part as $value) {
			if ($value != "") {
				$query = $dbCon->query(trim($value));
			}
		}
	}
}
?>