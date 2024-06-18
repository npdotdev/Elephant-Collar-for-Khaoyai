<?php

$con = new mysqli("localhost","root","khaoyai","khaoyai");
if($con->connect_error)
{
  die("Error Connect");
}
date_default_timezone_set("Asia/Bangkok");
$con->set_charset("utf8");
?>
