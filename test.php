<?php

$db = mysql_connect("localhost", "root", "123");
// Make sure to include your chosen username and password during MySQL installation

if (!$db) die('Could not connect' . mysql_error());
echo 'Connected successfully';