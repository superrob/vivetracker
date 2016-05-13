#!/usr/bin/php
<?
include("database.php");

// Tracking number generation function. 
function generateTrackingNumber($seq) {
	return floor($seq / 7) * 70 + (($seq % 7) * 11);
}

// Find the current highest tracking number.
$startdat = $db->query("select id from vive order by ID desc limit 1")->fetch_array();

// Get the current sequence number from the current tracking number.
$seq = floor($startdat['id']/70)*7;

echo "Starting from $seq\n\n";
// Initialize curl.
$ch = curl_init();

// Prepared statement for checking if tracking number already is present in database.
$db_check_stmt = $db->prepare('select id from vive where id=?');

// Prepared statement for inserting into the Vive table
$db_insertVive_stmt = $db->prepare('insert into vive (id, found, origin, destination, description, firstdate) values (?, 1, ?, ?, ?, ?)');

// Continue to loop until we break the loop.
while (true) {
	// Init an empty array for the tracking numbers.
	$trackingNumbers = array();
	// Generate the tracking number string until 10 numbers have been reached.
	while (count($trackingNumbers) < 10) {
		// Generate a tracking number and increment the sequence number.
		$cur = generateTrackingNumber($seq++);	
		echo "Trying $cur\r";		
		
		// Update the statement with current tracking number.
		$db_check_stmt->bind_param("i", $cur);
		$db_check_stmt->execute();
		$db_check_stmt->store_result();
		// Check if the generated tracking number is already present in the database.
		if ($db_check_stmt->num_rows == 0)
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
		$highestdat = $db->query("select id from vive where found=1 order by ID desc limit 1")->fetch_array();
		if ($cur - $highestdat['id'] > 10000 ) {
			echo "No more new tracking numbers\n";
			break;
		}
	} else {
		// We have some results!
		foreach ($json['results'] as $current) {			
			$origin = $current["origin"]["value"];
			$destination = $current["destination"]["value"];
			$description = $current["description"];
			$checks = $current["checkpoints"][0]["counter"]-1;
			$check = $current["checkpoints"][$checks];
			$unix = strtotime($check['date'])+3600;
			echo "Found ".$current["id"]."!\n";
			echo "Origin: " . $current["origin"]["value"]."\n";
			//echo "\nDestination: " . $current["destination"]["value"];
			//echo "\nDescription: " . $current["description"];
			//echo "\nCheckpoint: " . $check["description"] . " at: " . $check["date"];
			echo "Date: " . date("j m y") . "\n\n";
			// Lets insert the newly found tracking number into the Vive table.			
			$db_insertVive_stmt->bind_param("issss", $current['id'], $origin, $destination, $description, date("Y-m-d", $unix));
			$db_insertVive_stmt->execute();
		}	
	}
	if (isset($json["errors"])) {
		foreach ($json["errors"] as $current) {
			// Is the error any different from error 404?
			if ($current['code'] != 404)
				print_r($current);
			// Lets add this tracking number as not found in the Vive table.
			$db->query("insert into vive (id, found) values (".$current["id"].", 0)");
			// Lets also add it to the revive tracker.
			$db->query("insert into revive (id) values (".$current["id"].")");
		}	
	}
}
// Close the MySQL connection
$db_check_stmt->close();
$db_insertVive_stmt->close();
$db->close();
?>