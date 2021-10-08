<?php
/*
	WebCounter
	by Scripthosting.net
	
	Licensed under the "GPL Version 3, 29 June 2007"
	http://www.gnu.org/licenses/gpl.html
	
	Support-Forum: http://board.scripthosting.net/viewforum.php?f=7
	Don't send emails asking for support!!
*/

// Always include config
require_once("../system/config/config.inc.php");

// Open database connection
$dbCon = new Database();
?>
<!DOCTYPE html>
<html>
<head>
	<title>WebCounter WebStats</title>
	<meta charset="utf-8" />
	<style type="text/css">
	<!--
	body,table,div { font-family: Arial, Verdana, sans-serif; font-size:12px; overflow:hidden; }
	a { text-decoration: none; color:#000000; }
	.foot { font-size: 10px; }
	-->
	</style>
</head>

<body>
	<h1>WebCounter WebStats</h1>
	
	<div>
		<b>Top 20 referer (overall)</b><br />		
		<?php
		$result = $dbCon->result("SELECT referer,anzahl FROM {$config["referer_table"]} LIMIT 20");
		
		while( $row = $dbCon->fetchAssoc($result) ){
			if($row["referer"] == ""){
				$row["referer"] = "- Unbekannt -";
			}
			else{
				$row["referer"] = "<a href='http://anonym.to/?{$row["referer"]}' target='_blank'>{$row["referer"]}</a>";
			}
			echo $row["referer"]." ({$row["anzahl"]})<br />" . "\r\n";
		}
		?>
	</div>
	
	<br />
	<br />
	
	<div>
		<b>Referer (today)</b><br />
		<?php
		$heute = date("Y-m-d");
		$result = $dbCon->result("SELECT referer FROM {$config["referer_table"]} WHERE erstbesuch LIKE '{$heute}%' OR letztbesuch LIKE '{$heute}%'");
		
		while( $row = $dbCon->fetchAssoc($result) ){
			if($row["referer"] == ""){
				$row["referer"] = "- Unbekannt -";
			}
			else{
				$row["referer"] = "<a href='http://anonym.to/?{$row["referer"]}' target='_blank'>{$row["referer"]}</a>";
			}
			echo $row["referer"]."<br />" . "\r\n";
		}
		?>
	</div>
	
	<br />
	<br />
	
	<div>
		Powered by <b>WebCounter 1.2.2</b><br />
		&copy;2009-2014 by <a href="http://www.scripthosting.net">Scripthosting.net</a>
	</div>
</body>
</html>