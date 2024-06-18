<?php
require 'connect.php';

function distance($lat1, $lon1, $lat2, $lon2, $unit) {
  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    return 0;
  }
  else {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
      return ($miles * 1.609344);
    } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
      return $miles;
    }
  }
}


$sql = "SELECT * FROM device";
$qry = $con->query($sql);
while($rows = $qry->fetch_assoc())
{
  $sqlLocation = "SELECT * FROM location WHERE device_id = '{$rows["device_id"]}' ORDER BY id DESC";
  $qryLocation = $con->query($sqlLocation);
  $rowsLocation = $qryLocation->fetch_assoc();
  $datetime1 = new DateTime();
  $datetime2 = new DateTime($rowsLocation["timesend"]);
  $interval = $datetime1->diff($datetime2);
  $elapsed = $interval->format('%y ปี %m เดือน %a วัน %h ชั่วโมง %i นาที %s วินาที ที่ผ่านมา');
  echo $rows["device_id"].",";
  echo "ชื่ออุปกรณ์ : ".$rows["device_name"]."\nรายละเอียดอุปกรณ์ : ".$rows["device_detail"]."\n"."อัพเดทล่าสุดเมื่อ : ".$elapsed.",";

  echo $rowsLocation["latitude"].",";
  echo $rowsLocation["longitude"]."?";
}
?>
