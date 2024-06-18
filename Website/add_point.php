<?php
require 'connect.php';

$sql = "INSERT INTO road_point VALUES(NULL,'{$_GET["lat"]}','{$_GET["lng"]}')";
echo $sql;
$qry = $con->query($sql);


?>
