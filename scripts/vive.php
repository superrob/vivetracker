#!/usr/bin/php
<?
include("database.php");

// Tracking number generation function. 
function generateTrackingNumber($seq) {
	return floor($seq / 7) * 70 + (($seq % 7) * 11);
}

// Find the current highest tracking number.
$get = mysql_query("select id from vive order by ID desc limit 1");
$startdat = mysql_fetch_array($get);

// Get the current sequence number from the current tracking number.
$seq = floor($startdat['id']/70)*7;

echo "Starting from $seq\n\n";
// Initialize curl.
$ch = curl_init();
// Continue to loop until we break the loop.
while (true) {
	// Init an empty array for the tracking numbers.
	$trackingNumbers = array();
	// Generate the tracking number string until 10 numbers have been reached.
	while (count($trackingNumbers) < 10) {
		// Generate a tracking number and increment the sequence number.
		$cur = generateTrackingNumber($seq++);	
		echo "Trying $cur\r";		
		
		// Check if the generated tracking number is already present in the database.
		if (mysql_num_rows(mysql_query("select id from vive where id='".$cur."'")) == 0)
			$trackingNumbers[] = $cur;
	}
	
	// Generate the DHL json feed URL.
	$url = "http://www.dhl.com/shipmentTracking?AWB=".implode(",", $trackingNumbers)."&countryCode=g0&languageCode=en&_=1460201053210";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);	
	$json = json_decode($output, true);
	
	// Are there any results?
	if (!isset($json["results"])) {
		// No results.. Lets check if we have gone "too far away" from the current highest tracking number found.
		$highestdat = mysql_fetch_array(mysql_query("select id from vive where found=1 order by ID desc limit 1"));
		if ($cur - $highestdat['id'] > 10000 ) {
			echo "No more new tracking numbers\n";
			break;
		}
	} else {
		// We have some results!
		foreach ($json['results'] as $current) {			
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
			// Lets remove the tracking number from the revive table (If present)
			mysql_query("delete from revive where id=".$current["id"]);
			// Lets insert the newly found tracking number into the Vive table.
			mysql_query("insert into vive (id, found, origin, destination, description, firstdate) values (".$current["id"].", 1, '".$origin."', '$des', '$descrip', FROM_UNIXTIME(".$unix."))");
		}	
	}
	if (isset($json["errors"])) {
		foreach ($json["errors"] as $current) {
			// Is the error any different from error 404?
			if ($current['code'] != 404)
				print_r($current);
			// Lets add this tracking number as not found in the Vive table.
			mysql_query("insert into vive (id, found) values (".$current["id"].", 0)");
			// Lets also add it to the revive tracker.
			mysql_query("insert into revive (id) values (".$current["id"].")");
		}	
	}
}
?>
