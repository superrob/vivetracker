<?php
// Yes i know.. using the MySQL extension in 2016 is a sin.. :c
$connection = mysql_connect("HOST",
                            "USERNAME",
                            "PASSWORD");
mysql_select_db("DATABASE", $connection);
?>