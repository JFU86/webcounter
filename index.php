<?php
/*
	WebCounter
	by Scripthosting.net

	Licensed under the "GPL Version 3, 29 June 2007"
	http://www.gnu.org/licenses/gpl.html

	Support-Forum: http://board.scripthosting.net/viewforum.php?f=7
	Don't send emails asking for support!!
*/

// Run installer if config does not exist
if( !file_exists("system/config/config.inc.php") ){
	header("Location: install.php");
	exit;
}

// Always include config
require_once("system/config/config.inc.php");

// Initialize WebCounter
$webcounter = new Webcounter();

/* Get data for Example 1 (Table) */
$data = $webcounter->getVisitor();
?>
<!DOCTYPE html>
<html>
<head>
	<title>WebCounter Examples</title>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="templates/css/style.css" type="text/css" />
</head>

<body>

<h1>Welcome to WebCounter!</h1>

<div style="font-size:16px;">
	This is a demo page to show how WebCounter can be implemented.<br />
</div>

<br />
<div style="font-size:16px;">
	Dies ist eine Demo Seite, wie der WebCounter eingebunden werden kann.<br />
</div>
<br />
<br />

<!-- Example 1 (Table) Start -->
<div style="font-weight: bold; font-size:14px;">
	Example 1 (HTML Table):
</div>
<br />
<div style="width:150px; border:1px solid #000000;">
	<table style="width:100%;">
	<tr style="font-weight:bold; background-color:#E6E6E6;">
		<td colspan="2" style="text-align:center;">WebCounter</td>
	</tr>
	<tr>
		<td style="width:50%;">Gesamt:</td>
		<td><?=$data["overall"]?></td>
	</tr>
	<tr>
		<td>Heute:</td>
		<td><?=$data["today"]?></td>
	</tr>
	<tr>
		<td>Gestern:</td>
		<td><?=$data["yesterday"]?></td>
	</tr>
	<tr>
		<td>Online:</td>
		<td><?=$data["online"]?></td>
	</tr>
	<tr>
		<td colspan="2" class="foot" style="text-align:center;">by <a href="http://www.scripthosting.net">Scripthosting.net</a></td>
	</tr>
	</table>
</div>

<br />
<hr style="width:150px; margin:2px;" />
<br />

<!-- Example 2 (Image) Start -->
<div style="font-weight: bold; font-size:14px;">
	<b>Example 2 (PNG Image):</b><br />
	<br />
	<a href="http://www.scripthosting.net"><img src="image.php" alt="WebCounter" style="border:1px solid #000000;" /></a><br />
</div>

<br />
<br />

<div>
	Powered by <b>WebCounter 1.2.3</b><br />
	&copy;2009-2014 by <a href="http://www.scripthosting.net">Scripthosting.net</a>
</div>

</body>
</html>