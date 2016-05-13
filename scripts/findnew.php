#!/usr/bin/php
<?
include("database.php");

// Tracking number generation function. 
function generateTrackingNumber($seq) {
	return floor($seq / 7) * 70 + (($seq % 7) * 11);
}

// The desired date (DAY MONTH YEAR)
$desireddate = "12 05 16";

// Find the current highest tracking number.
$startdat = $db->query("select id from vive order by ID desc limit 1")->fetch_array();

// Get the current sequence number from the current tracking number.
$seq = floor($startdat['id']/70)*7;

echo "Starting from $seq\n\n";
// Initialize curl.
$ch = curl_init();

// Continue looping until we find the desired date.
while (true) {
	// Init an empty array for the tracking numbers.
	$trackingNumbers = array();
	// Generate the tracking number string until 10 numbers have been reached.
	while (count($trackingNumbers) < 10) {
		// Jump 100 tracking numbers.
		$seq += 100;
		// Generate a tracking number and increment the sequence number.
		$cur = generateTrackingNumber($seq);	
		echo "Trying $cur\r";		
		$trackingNumbers[] = $cur;
	}
	
	// Generate the DHL json feed URL.
	$url = "http://www.dhl.com/shipmentTracking?AWB=".implode(",", $trackingNumbers)."&countryCode=g0&languageCode=en&_=1460201053210";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);	
	$json = json_decode($output, true);
	
	if (isset($json['results'])) {
		foreach($json['results'] as $current) {			
			$origin = $current["origin"]["value"];
			$destination = $current["destination"]["value"];
			$description = $current["description"];
			$checks = $current["checkpoints"][0]["counter"]-1;
			$check = $current["checkpoints"][$checks];
			$unix = strtotime($check['date'])+3600;
			echo $current['id'] . "\n". $unix."\n".date("j m y", $unix)."\n\n";
			if (date("j m y", $unix) != $desireddate)
				continue;
			echo "Found ".$current["id"]."!\n";
			echo "Origin: " . $current["origin"]["value"]."\n";
			echo "\nDestination: " . $current["destination"]["value"];
			echo "\nDescription: " . $current["description"];
			echo "\nCheckpoint: " . $check["description"] . " at: " . $check["date"];
			echo "\nUnix time: " . $unix . "\n\n";
			break;
		}	
	}
	
	if (isset($json["errors"])) {
		// None found :c
	}
}
?>
