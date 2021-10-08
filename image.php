<?php
/*
	WebCounter
	by Scripthosting.net
	
	Licensed under the "GPL Version 3, 29 June 2007"
	http://www.gnu.org/licenses/gpl.html
	
	Support-Forum: http://board.scripthosting.net/viewforum.php?f=7
	Don't send emails asking for support!!
*/

// Load the configuration
include_once("system/config/config.inc.php");

// Initialize WebCounter
$webcounter = new Webcounter();

// Show the WebCounter image
header("Content-Type: image/png");
require_once("webcounter1.png");
?>