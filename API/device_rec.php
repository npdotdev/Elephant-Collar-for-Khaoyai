<?php
require '../connect.php';

//INSERT INTO `location` (`id`, `latitude`, `longitude`, `timesend`, `device_id`) VALUES (NULL, '14', '14', CURRENT_TIMESTAMP, '38e6ba3a7d80');
$_POST = json_decode(file_get_contents('php://input'), true);

$sql = "INSERT INTO location VALUES(NULL,'{$_POST["lat"]}','{$_POST["lng"]}',CURRENT_TIMESTAMP,'{$_POST["device_id"]}')";
$qry = $con->query($sql);
if($qry)
{
  echo "Device rec success!";
}
?>
