<?php
/*
	WebCounter
	by Scripthosting.net

	Licensed under the "GPL Version 3, 29 June 2007"
	http://www.gnu.org/licenses/gpl.html

	Support-Forum: http://board.scripthosting.net/viewforum.php?f=7
	Don't send emails asking for support!!
*/

if( class_exists('SQLite3',false) && $config["databaseType"] == "Sqlite" ){
	class Database extends Sqlite {
		public function __construct(){
			global $config;
			parent::__construct($config["system_path"] . "/sqlite/webcounter.db");
		}
	}
}
elseif( class_exists('Mysqli',false) && ( $config["databaseType"] == "Mysql" || !isset($config["databaseType"]) ) ){
	class Database extends Mysql {
		public function __construct(){
			parent::__construct();
		}
	}
}
else{
	die("SQLite3 and MySQL are not proper installed or misconfigured.");
}
?>