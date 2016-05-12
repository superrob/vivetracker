#!/usr/bin/php
<?php
// Old script, used to copy all "revive" targets to seperate table.
include("database.php");

$get = mysql_query("select id,revives from vive where found=0");
while ($dat = mysql_fetch_array($get)) {
	mysql_query("insert into revive (id, revives) values ('".$dat['id']."', '".$dat['revives']."')");
}
?>