#!/usr/bin/php
<?
include("database.php");

$get = mysql_query("select id from revive where revives < 45");
$total = mysql_num_rows($get);
$more = ($total == 0) ? false : true;

$last = 9999999999;
$cnt = 0;
$found = 0;
echo "Going through all not found entries!\n\n";
$ch = curl_init();
while ($more) {
	$get = mysql_query("select id from revive where revives < 45 and id<$last order by id desc limit 10");
	$nums = array();
	$more = (mysql_num_rows($get) == 0) ? false : true;
	while ($row = mysql_fetch_array($get)) {
		$cnt++;
		$nums[] = $row['id'];
		echo "Trying ".$row['id']." - Total found: $found - Processed $cnt of $total (".round($cnt/$total*100,2)."%)\r";
		$last = $row['id'];
	}
	$nums = implode(",", $nums);
	
	$url = "http://www.dhl.com/shipmentTracking?AWB=".$nums."&countryCode=g0&languageCode=en&_=1460201053210";
	curl_setopt($ch, CURLOPT_URL, $url);	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	$string = curl_exec($ch);
	$json = json_decode($string, true);
	if (!isset($json["results"])) {
		// All not found.
	} else {
		for ($j=0;$j<count($json["results"]);$j++) {		
			$current = $json["results"][$j];
			$total--;				
			$found++;
			$origin = mysql_real_escape_string($current["origin"]["value"]);
			$des = mysql_real_escape_string($current["destination"]["value"]);
			$descrip = mysql_real_escape_string($current["description"]);
			$checks = $current["checkpoints"][0]["counter"]-1;
			$check = $current["checkpoints"][$checks];
			$unix = strtotime($check['date'])+3600;
			echo "Found previously not found ".$current["id"]."!\n";
			echo "Origin: " . $current["origin"]["value"]."\n\n";
			//echo "\nDestination: " . $current["destination"]["value"];
			//echo "\nDescription: " . $current["description"];
			//echo "\nCheckpoint: " . $check["description"] . " at: " . $check["date"];
			//echo "\nUnix time: " . $unix . "\n\n";
			// Found!
			mysql_query("delete from revive where id=".$current["id"]);
			mysql_query("delete from vive where id=".$current["id"]);
			mysql_query("insert into vive (id, found, origin, destination, description, firstdate) values (".$current["id"].", 1, '".$origin."', '$des', '$descrip', FROM_UNIXTIME(".$unix."))");
		}	
	}
	if (isset($json["errors"])) {
		// Some still not found.
		//print_r($json['errors']);
		for ($j=0;$j<count($json["errors"]);$j++) {
			$current = $json["errors"][$j];			
			//mysql_query("delete from vive where id=".$current["id"]);
			mysql_query("update revive set revives=revives+1 where id=".$current['id']);
		}	
	}
}
?>
