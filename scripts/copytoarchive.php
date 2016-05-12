#!/usr/bin/php
<?php
// Used to move old entries from the main table to the archive table.
include("database.php");

$get = mysql_query("SELECT * FROM `vive` WHERE `id` < 7995008963 AND found=1 AND origin != '' AND `origin` NOT LIKE '%Ricany-Jazlovice%'");
while ($dat = mysql_fetch_array($get)) {	
	mysql_query("delete from vive where id=".$dat['id']);
	mysql_query("insert into vive (id, found, firstdate, scandate) values ('".$dat['id']."', 1, '".$dat['firstdate']."', '".$dat['scandate']."')");
	mysql_query("insert into vive_archive (id, origin, destination, description, firstdate, scandate) values ('".$dat['id']."', '".$dat['origin']."', '".$dat['destination']."', '".$dat['description']."', '".$dat['firstdate']."', '".$dat['scandate']."')");
}
?>