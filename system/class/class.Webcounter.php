<?php
/*
	WebCounter
	by Scripthosting.net

	Licensed under the "GPL Version 3, 29 June 2007"
	http://www.gnu.org/licenses/gpl.html

	Support-Forum: http://board.scripthosting.net/viewforum.php?f=7
	Don't send emails asking for support!!
*/

class Webcounter extends Database
{	
	public function __construct()
	{
		parent::__construct();
		$this->addVisitor();
	}
	
	
	/**
	* Läd die Besucherstatistik in ein Array
	* @return String[]
	*/
	public function getVisitor()
	{		
		global $config,$usercount;
		require_once($config["system_path"]."/useronline.inc.php");
		
		$gestern = date("Y-m-d",strtotime("yesterday"));
		$heute = date("Y-m-d");

		// Statistik auslesen
		$row_gestern = $this->resultRow(sprintf("SELECT SUM(anzahl) as anzahl FROM %s WHERE datum = '%s';", $config["visitor_table"], $gestern));
		$row_heute = $this->resultRow(sprintf("SELECT SUM(anzahl) as anzahl FROM %s WHERE datum = '%s';", $config["visitor_table"], $heute));
		$row_gesamt = $this->resultRow(sprintf("SELECT SUM(anzahl) as anzahl FROM %s;", $config["visitor_table"]));
		
		$besucher = Array();
		// German
		$besucher["gestern"] = (int) $row_gestern["anzahl"];
		$besucher["heute"] = (int) $row_heute["anzahl"];
		$besucher["gesamt"] = (int) $row_gesamt["anzahl"];
		$besucher["online"] = (int) $usercount;
		// English
		$besucher["yesterday"] = (int) $row_gestern["anzahl"];
		$besucher["today"] = (int) $row_heute["anzahl"];
		$besucher["overall"] = (int) $row_gesamt["anzahl"];
		
		return $besucher;		
	}
	
	
	/**
	 * WebCounter erzeugen
	 * @param $file_type
	 * @return void
	 */
	private function createWebcounter($file_type="png")
	{		
		global $config, $usercount;
		require_once($config["basepath"] . "/system/useronline.inc.php");
		
		$data = $this->getVisitor();
		
		if ($file_type=="png") {
			$bild = imagecreatefrompng($config["basepath"] . "/templates/img/webcounter.png");
		}
		elseif ($file_type=="gif") {
			$bild = imagecreatefromgif ($config["basepath"] . "/templates/img/webcounter.gif");
		}
		elseif ($file_type=="jpg") {
			$bild = imagecreatefromjpeg($config["basepath"] . "/templates/img/webcounter.jpg");
		}
		$farbe = imagecolorallocate($bild, 0, 0, 0);
		
		// Statistik auf die Grafik zeichnen
		ImageTTFText($bild, 10, 0, 80, 20, $farbe, $config["basepath"]."/templates/ttf/arial.ttf", $data["gesamt"]);
		ImageTTFText($bild, 10, 0, 80, 36, $farbe, $config["basepath"]."/templates/ttf/arial.ttf", $data["gestern"]);
		ImageTTFText($bild, 10, 0, 80, 51, $farbe, $config["basepath"]."/templates/ttf/arial.ttf", $data["heute"]);
		ImageTTFText($bild, 10, 0, 80, 67, $farbe, $config["basepath"]."/templates/ttf/arial.ttf", (int) $usercount);
		
		if ($file_type == "png") {
			imagepng($bild,$config["basepath"]."/webcounter1.png");
		}
		elseif ($file_type == "gif") {
			imagegif ($bild,$config["basepath"]."/webcounter1.gif");
		}
		elseif ($file_type == "jpg") {
			imagejpeg($bild,$config["basepath"]."/webcounter1.jpg");
		}			
	}
	

	/**
	* Speichert Besucherdaten
	* @return void
	*/
	private function addVisitor()
	{		
		global $config;
		
		if ($this->ipCheck($_SERVER["REMOTE_ADDR"])) {		

			// Referer speichern
			$referer = $this->clean($_SERVER["HTTP_REFERER"]);
			$datum = date("Y-m-d");
			$stunde = date("H");
			
			// Referer Eintrag suchen
			$resultRow = $this->resultRow("SELECT count(referer) anzahl FROM {$config["referer_table"]} WHERE referer = '{$referer}'");
			// Referer eintragen oder erhöhen
			if ($resultRow["anzahl"] == 0) {
				$this->query("INSERT INTO {$config["referer_table"]} (referer,anzahl,erstbesuch,letztbesuch) VALUES ('{$referer}',1,'{$datum}','{$datum}')");
			} else {
				$this->query("UPDATE {$config["referer_table"]} SET anzahl=anzahl+1,letztbesuch='{$datum}' WHERE referer = '{$referer}';");
			}
			
			// Datumseintrag suchen
			$resultRow = $this->resultRow("SELECT count(anzahl) anzahl FROM {$config["visitor_table"]} WHERE datum = '{$datum}' AND stunde = '{$stunde}'");
			// Besuch hinzufügen
			if ($resultRow["anzahl"] == 0) {
				$this->query("INSERT INTO {$config["visitor_table"]} (datum,stunde,anzahl) VALUES ('{$datum}','{$stunde}',1)");
			} else {
				$this->query("UPDATE {$config["visitor_table"]} SET anzahl=anzahl+1 WHERE datum = '{$datum}' AND stunde = '{$stunde}'");
			}
			
			$this->createWebcounter();
		}
	}
	
	
	/**
	* Verwaltet die IP-Sperre
	* @param String $ip
	* @return boolean
	*/
	private function ipCheck($ip)
	{		
		global $config;
		
		$table = $config["reload_table"];
	    $view_wait = $config["reloadtime"];
	    $ip = $this->clean($ip);   
	
		$result = $this->result("SELECT * FROM {$table} WHERE ipaddress = '$ip'");
		$rows = $this->numRows($result);
	
		if ($rows) {
		   $val = $this->fetchAssoc($result);
		
			if ($val["visit"] < (time()-$view_wait)) {
				
				$sql = sprintf("DELETE FROM %s WHERE visit <= '%u';", $table, (time()-$view_wait));
				$query = $this->query($sql);
			
				$sql = sprintf("INSERT INTO %s (ipaddress,visit) VALUES ('%s','%u');", $table, $ip, time());
				$query = $this->query($sql);
				return true;
		    } else {
				return false;
			}
		} else {
		   $sql = sprintf("INSERT INTO %s (ipaddress,visit) VALUES ('%s','%u');", $table, $ip, time());
		   $query = $this->query($sql);
		   return true;
		}
	}
}
?>