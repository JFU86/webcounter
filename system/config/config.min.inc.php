<?php
/*
	WebCounter
	by Scripthosting.net

	Licensed under the "GPL Version 3, 29 June 2007"
	http://www.gnu.org/licenses/gpl.html

	Support-Forum: http://board.scripthosting.net/viewforum.php?f=7
	Don't send emails asking for support!!
*/

// PHP Reporting auf Default einstellen
error_reporting(E_ALL & ~E_NOTICE);

// Lege den Zeichensatz für die Ausgabe auf UTF-8 fest
header('Content-type: text/html; charset=UTF-8');

// Lege die Standard-Zeitzone fest
if( function_exists('date_default_timezone_set') )
	date_default_timezone_set("Europe/Berlin");

##########################################
$config = Array();
##########################################
// Pfade
$config["basepath"] = substr(__FILE__,0,-33);
$config["template_path"] = $config["basepath"]."/templates";
$config["include_path"] = $config["basepath"]."/templates/php";
$config["system_path"] = $config["basepath"] . "/system";
$config["class_path"] = $config["basepath"] . "/system/class";
##########################################

////////////////////////////////////////////////////////////////////////
// Teile PHP das Standardverzeichnis für alle Klassen und Interfaces mit
////////////////////////////////////////////////////////////////////////
spl_autoload_register(function ($class_name) {
	global $config;
	
    if (file_exists($config["system_path"] . "/class/class.". $class_name .".php")) {
		include_once($config["system_path"] . "/class/class.". $class_name .".php");
	}
	elseif (file_exists($config["system_path"] . "/class/interface.". $class_name .".php")) {
		include_once($config["system_path"] . "/class/interface.". $class_name .".php");
	}
	elseif (file_exists($config["system_path"] . "/class/". str_replace("\\","/",$class_name) .".class.php")) {
		include_once($config["system_path"] . "/class/". str_replace("\\","/",$class_name) .".class.php");
	}
	else {
		throw new Exception("Class or Interface {$class_name} not found!");
	}
});
?>