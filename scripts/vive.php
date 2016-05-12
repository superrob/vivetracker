#!/usr/bin/php
<?
include("database.php");

$get = mysql_query("select id from vive order by ID desc limit 1");
$startdat = mysql_fetch_array($get);

$cnt = 0;
$series = floor($startdat['id']/70);
//7364211256
//$start = floor($startdat['id']/70);
//$series = floor($startdat['id']/70);
$seq = 0;
$inc = 0;
$none = 0;
echo "Starting from $series..\n\n";
$ch = curl_init();
while ($cnt < 50000) {
	$cnt++;
	$nums = "";	
	for ($i=0;$i<10;$i++) {
		$cur = ($series+$inc)*70 +($seq*11);	
		echo "Trying $cur\r";		
		//if ($cur > 7362761615)
		//	$cnt = 55555555;
		if (mysql_num_rows(mysql_query("select id from vive where id='".$cur."'")) == 0) {
			$nums .= $cur .",";
		} else {
			$i--;
		}
		$seq++;
		if ($seq >= 7) {
			$inc++; 
			$seq = 0;
		}
	}
	$nums = rtrim($nums);
	
	$url = "http://www.dhl.com/shipmentTracking?AWB=".$nums."&countryCode=g0&languageCode=en&_=1460201053210";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$string = curl_exec($ch);
	//echo $string;
	//die();
	//$string = file_get_contents($url);
	$json = json_decode($string, true);
	if (!isset($json["results"])) {
		$none++;
		$get = mysql_query("select id from vive where found=1 order by ID desc limit 1");
		$highestdat = mysql_fetch_array($get);
		if ($cur - $highestdat['id'] > 5000 ) {		
			die("No more!");
		}
	} else {
		$none = 0;
		for ($j=0;$j<count($json["results"]);$j++) {
			$current = $json["results"][$j];
			
			$origin = mysql_real_escape_string($current["origin"]["value"]);
			$des = mysql_real_escape_string($current["destination"]["value"]);
			$descrip = mysql_real_escape_string($current["description"]);
			$checks = $current["checkpoints"][0]["counter"]-1;
			$check = $current["checkpoints"][$checks];
			$unix = strtotime($check['date'])+3600;
			echo "Found ".$current["id"]."!\n";
			echo "Origin: " . $current["origin"]["value"]."\n";
			//echo "\nDestination: " . $current["destination"]["value"];
			//echo "\nDescription: " . $current["description"];
			//echo "\nCheckpoint: " . $check["description"] . " at: " . $check["date"];
			echo "Date: " . date("j m y") . "\n\n";
			// Found!
			mysql_query("delete from revive where id=".$current["id"]);
			mysql_query("insert into vive (id, found, origin, destination, description, firstdate) values (".$current["id"].", 1, '".$origin."', '$des', '$descrip', FROM_UNIXTIME(".$unix."))");
		}	
	}
	if (isset($json["errors"])) {
		//print_r($json["errors"]);
		for ($j=0;$j<count($json["errors"]);$j++) {
			$current = $json["errors"][$j];			
			//mysql_query("delete from vive where id=".$current["id"]);
			mysql_query("insert into vive (id, found) values (".$current["id"].", 0)");
			mysql_query("insert into revive (id) values (".$current["id"].")");
		}	
	}
}
?>
