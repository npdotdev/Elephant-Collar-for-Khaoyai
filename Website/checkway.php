<?php
require 'connect.php';

if(isset($_GET["Key"]))
{
  if(isset($_GET["date_start"]) && isset($_GET["date_end"]) && !empty($_GET["date_start"]) && !empty($_GET["date_end"]))
  {
    $sql = "SELECT * FROM location WHERE device_id = '{$_GET["Key"]}' AND timesend  BETWEEN  '{$_GET["date_start"]}' AND '{$_GET["date_end"]}'";
    $qry = $con->query($sql);
  }
  else {
    $sql = "SELECT * FROM location WHERE device_id = '{$_GET["Key"]}'";
    $qry = $con->query($sql);
  }
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

  $count = 0;
  $chdataX[$count] = 0;
  $chdataY[$count] = 0;
  $chSerial[$count] = 0;
  $chId[$count] = 0;
  while($rows = $qry->fetch_assoc())
  {
    $chdataX[$count] = $rows["latitude"];
    $chdataY[$count] = $rows["longitude"];
    $chSerial[$count] = $rows["device_id"];
    $chId[$count] = $rows["id"];
    $count++;
  }

  for($i = 0;$i<$count;$i++)
  {
    if(($chdataX[$i] == 0 && $chdataY[$i] == 0))
      continue;

    for($j=$i+1;$j<$count;$j++)
    {
      if(distance($chdataX[$i],$chdataY[$i],$chdataX[$j],$chdataY[$j],"K") < 0.5)
      {
        $chdataX[$j] = 0;
        $chdataY[$j] = 0;
      }
    }
  }

  for($i = 0;$i<$count;$i++)
  {
    if(($chdataX[$i] == 0 && $chdataY[$i] == 0))
      continue;

    $sql = "SELECT * FROM device WHERE device_id = '{$chSerial[$i]}'";
    $qry = $con->query($sql);
    $rows = $qry->fetch_assoc();
    $sqlLocation = "SELECT * FROM location WHERE device_id = '{$rows["device_id"]}' AND id = '{$chId[$i]}'";
    $qryLocation = $con->query($sqlLocation);
    $rowsLocation = $qryLocation->fetch_assoc();
    $datetime1 = new DateTime();
    $datetime2 = new DateTime($rowsLocation["timesend"]);
    $interval = $datetime1->diff($datetime2);
    $elapsed = $interval->format('%y ปี %m เดือน %a วัน %h ชั่วโมง %i นาที %s วินาที ที่ผ่านมา');
    echo $rows["device_id"].",";
    echo "ชื่ออุปกรณ์ : ".$rows["device_name"]."\nรายละเอียดอุปกรณ์ : ".$rows["device_detail"]."\n"."อัพเดทล่าสุดเมื่อ : ".$elapsed.",";

    echo $chdataX[$i].",";
    echo $chdataY[$i]."?";
  }
}
?>
