<!DOCTYPE html>
<html>
  <head>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    <title>Tracking</title>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }

      #map {
        height: 100%;
        width: 100%;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <script>
      var map;
      function initMap() {
        var my_Latlng  = new google.maps.LatLng(14.380187553399567,101.3661064280501);
        map = new google.maps.Map(document.getElementById('map'), {
          center: my_Latlng,
          zoom: 11.6
        });

        mapCircle = new google.maps.Circle({ // สร้างตัว circle
          strokeColor: "#000000", // สีของเส้นสัมผัส หรือสีขอบโดยรอบ
          strokeOpacity: 0.8, // ความโปร่งใส ของสีขอบโดยรอบ กำหนดจาก 0.0  -  0.1
          strokeWeight: 1, // ความหนาของสีขอบโดยรอบ เป็นหน่วย pixel
          fillColor: "#00FF00", // สีของวงกลม circle
          fillOpacity: 0.35, // ความโปร่งใส กำหนดจาก 0.0  -  0.1
          map: map, // กำหนดว่า circle นี้ใช้กับแผนที่ชื่อ instance ว่า map
          center: my_Latlng, // ตำแหน่งศูนย์กลางของวลกลม ในที่นี้ใช้ตำแหน่งเดียวกับ ศูนย์กลางแผนที่
          radius: 15000 // รัศมีวงกลม circle ทีสร้าง หน่ายเป็น เมตร
        });

        $.get("ay_process.php",function(data,status){
          var newdata = data.split("?");
          for(var i = 0;i < newdata.length;i++)
          {
            var realData = newdata[i].split(",");
            //alert(realData[0]);

            var marker2 = new google.maps.Marker({
          	   position: new google.maps.LatLng(realData[0], realData[1]),
          	   map: map,
          	   title: 'Analysis data',
               icon: 'image/iconn.png'
          	});
          }
        });
      }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBynn3W-UXP6CAGKaIx7_LaItGLoRHty6E&callback=initMap"
    async defer></script>

  </body>
</html>
