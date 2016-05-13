#!/usr/bin/php
<?
include("database.php");

// The amount of times each tracking number can max be retried until we give up on it.
$retry_amount = 45;

// Get the total amount of tracking numbers with under 45 retries in the revive table.
$total = $db->query("select id from revive where revives < $retry_amount")->num_rows;
// Are there anymore tracking numbers to retry?
$more = ($total == 0) ? false : true;

// Variable containing the last ID we processed. (We are moving from high to low)
$last = 9999999999;
// Counter to show current progress
$cnt = 0;
// The amount of previously not found tracking numbers discovered.
$found = 0;
echo "Going through all $total not found entries.\n\n";

// Initialze curl
$ch = curl_init();

// Prepared statement to select the next 10 tracking numbers.
$db_select_stmt = $db->prepare('select id from revive where revives < '.$retry_amount.' and id<? order by id desc limit 10');
$db_select_stmt->bind_result($id);

// Prepared statement for inserting into the Vive table
$db_insertVive_stmt = $db->prepare('insert into vive (id, found, origin, destination, description, firstdate) values (?, 1, ?, ?, ?, ?)');

// Prepared statement for incrementing the retry counter
$db_retryInc_stmt = $db->prepare('update revive set revives=revives+1 where id=?');

// Continue looping until no more tracking numbers available.
while ($more) {	
	// Init an empty array for the tracking numbers.
	$trackingNumbers = array();
	
	$db_select_stmt->bind_param("i", $last);
	$db_select_stmt->execute();
	$db_select_stmt->store_result();
	
	// Are there anymore tracking numbers to retry?
	$more = ($db_select_stmt->num_rows == 0) ? false : true;
	// Break the loop if no more tracking numbers should be retried.
	if (!$more)
		break;
	// Loop through the tracking numbers.
	while ($db_select_stmt->fetch()) {
		$cnt++;
		$trackingNumbers[] = $id;		
		$last = $id;
		
		echo "Trying $id - Total found: $found - Processed $cnt of $total (".round($cnt/$total*100,2)."%)\r";
	}
	
	// Generate the DHL json feed URL.
	$url = "http://www.dhl.com/shipmentTracking?AWB=".implode(",", $trackingNumbers)."&countryCode=g0&languageCode=en&_=1460201053210";
	curl_setopt($ch, CURLOPT_URL, $url);	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	$output = curl_exec($ch);	
	$json = json_decode($output, true);
	
	if (isset($json["results"])) {
		foreach($json['results'] as $current) {	
			$total--;				
			$found++;
			$origin = $current["origin"]["value"];
			$destination = $current["destination"]["value"];
			$description = $current["description"];
			$checks = $current["checkpoints"][0]["counter"]-1;
			$check = $current["checkpoints"][$checks];
			$unix = strtotime($check['date'])+3600;
			echo "Found previously not found ".$current["id"]."!\n";
			echo "Origin: " . $current["origin"]["value"]."\n\n";
			//echo "\nDestination: " . $current["destination"]["value"];
			//echo "\nDescription: " . $current["description"];
			//echo "\nCheckpoint: " . $check["description"] . " at: " . $check["date"];
			//echo "\nUnix time: " . $unix . "\n\n";
			
			// Delete any old entries from the revive and vive table.
			$db->query("delete from revive where id=".$current["id"]);
			$db->query("delete from vive where id=".$current["id"]);
			// Lets insert the newly found tracking number into the Vive table.		
			$db_insertVive_stmt->bind_param("issss", $current['id'], $origin, $destination, $description, date("Y-m-d", $unix));
			$db_insertVive_stmt->execute();
		}	
	}
	if (isset($json["errors"])) {
		// Some still not found.
		for ($j=0;$j<count($json["errors"]);$j++) {
			$current = $json["errors"][$j];			
			if ($current['code'] != 404)
				print_r($current);
			
			// Increment the retry counter.
			$db_retryInc_stmt->bind_param("i", $current['id']);
			$db_retryInc_stmt->execute();
		}	
	}
}

$db_select_stmt->close();
$db_insertVive_stmt->close();
$db_retryInc_stmt->close();
$db-close();
?>
