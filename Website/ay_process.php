<?php
require 'connect.php';

$sql = "SELECT * FROM location";
$qry = $con->query($sql);

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
while($rows = $qry->fetch_assoc())
{
  //echo $rows["lat"].",";
  //echo $rows["lng"]."?";

  $chdataX[$count] = $rows["lat"];
  $chdataY[$count] = $rows["lng"];
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

  echo $chdataX[$i].",";
  echo $chdataY[$i]."?";
}
?>
