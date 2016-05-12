#!/usr/bin/php
<?
// Old old script, used to generate the tables for Reddit before the website launched. Very manual.
include("database.php")

$countries = array();
$data = mysql_query("SELECT * FROM  `vive` WHERE firstdate='2016-04-27' AND `origin` LIKE  '%Ricany-Jazlovice%'");
$total = mysql_num_rows($data);
while ($dat = mysql_fetch_array($data)) {
	$ex = explode(" - ", $dat['destination']);
	$last = count($ex)-1;
	$countries[$ex[$last]]++;
}
arsort($countries);
foreach($countries as $key => $am) {
	echo "$key | $am\n";
}
print_r($countries);
echo "Total: $total\n";
?>
