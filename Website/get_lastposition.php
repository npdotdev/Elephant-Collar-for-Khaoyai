<?php
require 'connect.php';

if(isset($_GET["date_start"]) && isset($_GET["date_end"]) && !empty($_GET["date_start"]) && !empty($_GET["date_end"]))
{

  $sql = "SELECT * FROM location WHERE device_id = '{$_GET["device_id"]}' AND timesend  BETWEEN  '{$_GET["date_start"]}' AND '{$_GET["date_end"]}' ORDER BY id DESC";
  $qry = $con->query($sql);
  $rows = $qry->fetch_assoc();

}
else {
  $sql = "SELECT * FROM location WHERE device_id = '{$_GET["device_id"]}' ORDER BY id DESC";
  $qry = $con->query($sql);
  $rows = $qry->fetch_assoc();
}

echo $_GET["key"]." ";
echo $rows["latitude"]." ";
echo $rows["longitude"];

?>
