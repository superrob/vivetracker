#!/usr/bin/php
<?php
// Used to move old entries from the main table to the archive table.
include("database.php");
echo "Moving entries from vive to vive_archive..\n\n";
$get = $db->query("SELECT * FROM `vive` WHERE `id` < 7995008963 AND found=1 AND origin != '' AND `origin` NOT LIKE '%Ricany-Jazlovice%'");

// Prepared statement for updating the Vive table
$db_updateVive_stmt = $db->prepare('update vive set origin="", destination="", description="" where id=?');

// Prepared statement for inserting into the archive table
$db_insertArchive_stmt = $db->prepare('insert into vive_archive (id, origin, destination, description, firstdate, scandate) values (?, ?, ?, ?, ?, ?)');

while ($dat = $get->fetch_array()) {	
	echo "Moving id " . $dat['id']."\n";
	$db_updateVive_stmt->bind_param("i", $dat['id']);
	$db_updateVive_stmt->execute();
	
	$db_insertArchive_stmt->bind_param("isssss", $dat['id'], $dat['origin'], $dat['destination'], $dat['description'], $dat['firstdate'], $dat['scandate']);
	$db_insertArchive_stmt->execute();
}
$db_insertArchive_stmt->close();
$db_insertVive_stmt->close();
$db->close();
?>