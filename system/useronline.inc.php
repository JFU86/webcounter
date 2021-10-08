<?php
/*
	WebCounter
	by Scripthosting.net

	Licensed under the "GPL Version 3, 29 June 2007"
	http://www.gnu.org/licenses/gpl.html

	Support-Forum: http://board.scripthosting.net/viewforum.php?f=7
	Don't send emails asking for support!!
*/

//_____________________________________________________________________________
//
//	useronline.php
//
//	Copyright: © 2003 by Hauriswiss
//	Web Site : http://www.hauriswiss.ch
//	Email    : web@hauriswiss.ch
//
//
//      Set useronline.txt to chmod 777
//_____________________________________________________________________________



//__ Online Time in Seconds ___________________________________________________
$onlinetime="30";


//__ Path to useronline.txt ___________________________________________________
$uo_datafile = $config["basepath"]."/system/useronline.txt";



//__ Dont Change ______________________________________________________________
session_start();
$ot=$onlinetime;
$time = time();
$uo_datei = fopen($uo_datafile, "a+");
$writetext = "";
$usercount = 1;
$onlinetime = $time - $onlinetime;
$sessid = session_id();
while($erg = fgets($uo_datei, 1000))
{
   $inhalt = explode("|", $erg);
   $inhalt[1] = str_replace("\n", "", $inhalt[1]);
   if($inhalt[0] == $sessid || $inhalt[1] < $onlinetime || $inhalt[0]=="")
   continue;
   $writetext .= "$inhalt[0]|$inhalt[1]\n";
   $usercount++;
}
fclose($uo_datei);
$writetext .= "$sessid|$time\n";
$uo_datei = fopen($uo_datafile, "w");
fputs($uo_datei, $writetext);
fclose($uo_datei);

$usr="Besucher";

?>