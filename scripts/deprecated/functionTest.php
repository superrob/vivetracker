#!/usr/bin/php
<?
// Script used to check if the generateTrackingNumber function was valid against the old more complex method.
function generateTrackingNumber($seq) {
	return (floor($seq / 7) * 70 + (($seq % 7) * 11));
}
$series = floor(9400588396/70);
$num = $series * 7;
$seq = 0;
$inc = 0;
for ($i=0;$i<1000;$i++) {
		$cur = ($series+$inc)*70 +($seq*11);
		echo $cur . " - " . generateTrackingNumber($num) . "\n";
		if ($cur != generateTrackingNumber($num))
			die ("Noo not equal :c");$seq++;
		if ($seq >= 7) {
			$inc++; 
			$seq = 0;
		}		
		$num++;
	}
echo "Yes! Function is valid!";
?>