<?php
/*
	WebCounter
	by Scripthosting.net

	Licensed under the "GPL Version 3, 29 June 2007"
	http://www.gnu.org/licenses/gpl.html

	Support-Forum: http://board.scripthosting.net/viewforum.php?f=7
	Don't send emails asking for support!!
*/

// Einbinden der Konfigurationsdatei
if( file_exists("system/config/config.inc.php") ){
	include_once("system/config/config.inc.php");
}
else{
	include_once("system/config/config.min.inc.php");
}

// Zusammensetzen und Laden des Installationstemplates
$template = new Template();
if( !isset($_REQUEST["noheader"]) ){
	$template->getTemplate("overall_header");
	$template->getTemplate("gfxheader");
}

$template->getTemplate("install");
$template->getTemplate("copyright");
$template->getTemplate("overall_footer");

?>