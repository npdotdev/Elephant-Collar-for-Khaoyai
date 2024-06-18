<!DOCTYPE html>
<html>
  <head>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

    <link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/>
    <!--<script src="js/jquery.js"></script>-->
    <script src="js/jquery.datetimepicker.full.js"></script>
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

      /* Add some padding on document's body to prevent the content
      to go underneath the header and footer */
      body{
          padding-top: 60px;
      }
      .fixed-header, .fixed-footer{
          width: 100%;
          position: absolute;
          background: #333;
          padding: 10px 0;
          color: #fff;
      }
      .fixed-header{
          top: 0;
      }
      .fixed-footer{
          bottom: 0;
      }
      .container{
          width: 80%;
          margin: 0 auto; /* Center the DIV horizontally */
      }
      nav a{
          color: #fff;
          text-decoration: none;
          padding: 7px 25px;
          display: inline-block;
      }
    </style>
  </head>
  <body>
    <div class="fixed-header">
      <script>
            var curSelect_Device;

            /*jslint browser:true*/
            /*global jQuery, document*/

            jQuery(document).ready(function () {
                'use strict';

                jQuery('#date_start,#date_end').datetimepicker({
                  format:'Y-m-d H:i:00'
                });

            });
        </script>
        <div class="container">
            <div style="float:left;width:500px;" class="input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">เลือกช่วงเวลา</span>
              </div>
              <input type="text"  id="date_start" class="form-control" placeholder="ช่วงเวลาเริ่ม" aria-label="ช่วงเวลาเริ่ม" aria-describedby="basic-addon1">
              <input type="text" id="date_end" class="form-control" placeholder="ช่วงเวลาจบ" aria-label="ช่วงเวลาจบ" aria-describedby="basic-addon1">
            </div>
            <div style="float:right;" class="dropdown">
              <button class="btn btn-secondary dropdown-toggle" type="button" id="select_device_only" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                เลือกช้างแบบเจาะจง
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <?php
                require 'connect.php';
                $sql = "SELECT * FROM device";
                $qry = $con->query($sql);
                while($rows = $qry->fetch_assoc())
                {
                ?>
                <a class="dropdown-item" id="select_device" onClick="curSelect_Device='<?php echo $rows["device_id"];?>';document.getElementById('select_device_only').innerHTML='<?php echo $rows["device_name"];?>';" href="#"><?php echo $rows["device_name"];?></a>
                <?php
                }
                ?>
              </div>
              <button type="button" id="cancle_all" class="btn btn-danger">ยกเลิก</button>
            </div>
        </div>
    </div>

    <div id="map"></div>
    <script>
      var map;
      var marker2 = [];
      var marker_sim;
      var isSimulator;
      function call_first_point()
      {
        $.get("get_position.php",function(data,status){
          var newdata = data.split("?");
          for(var i = 0;i < newdata.length - 1;i++)
          {
            var realData = newdata[i].split(",");
            //alert(realData[0]);
            var isOut = "NULL";
            if (google.maps.geometry.spherical.computeDistanceBetween( new google.maps.LatLng(realData[2], realData[3]), mapCircle.getCenter()) <= mapCircle.getRadius()) {
                isOut = "อยู่ในพื้นที่";
            } else {
                isOut = "อยู่นอกพื้นที่";
            }

            marker2[i] = new google.maps.Marker({
          	   position: new google.maps.LatLng(realData[2], realData[3]),
          	   map: map,
          	   title: realData[1]+"\n"+isOut,
               icon: 'image/iconn.png',
               data: realData[0],
          	});

            google.maps.event.addListener(marker2[i], 'click', function () {
               //alert(this.data);
               var data_detail = this.title.split("\n");
               document.getElementById("option_detail").innerHTML = data_detail[0]+"<br>"+data_detail[1]+"<br>"+data_detail[2];
               document.getElementById("device_for_checkway").value = this.data;
               $("#modal").modal();

            });
          }
        });
      }
      function initMap() {
        marker_sim = new google.maps.Marker();
        var my_Latlng  = new google.maps.LatLng(14.380187553399567,101.3661064280501);
        map = new google.maps.Map(document.getElementById('map'), {
          center: my_Latlng,
          zoom: 11.3,
          mapTypeId:google.maps.MapTypeId.TERRAIN
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

        call_first_point();

        function animatedMove(marker, t, current, moveto) {
          var lat = current.lat();
          var lng = current.lng();

          var deltalat = (moveto.lat() - current.lat()) / 100;
          var deltalng = (moveto.lng() - current.lng()) / 100;

          var delay = 10 * t;
          for (var i = 0; i < 100; i++) {
            (function(ind) {
              setTimeout(
                function() {
                  var lat = marker.position.lat();
                  var lng = marker.position.lng();
                  lat += deltalat;
                  lng += deltalng;
                  latlng = new google.maps.LatLng(lat, lng);
                  marker.setPosition(latlng);
                }, delay * ind
              );
            })(i)
          }
        }

        setInterval(function(){
          for(var i = 0;i<marker2.length;i++)
          {
            curSelect = i;
            $.get("get_lastposition.php?device_id="+marker2[i].data+"&key="+i+"&date_start="+$("#date_start").val()+"&date_end="+$("#date_end").val(),function(data,status){
              var realData = data.split(" ");
              if(isSimulator)
              {
                marker2[realData[0]].setVisible(false);

              }
              else {
                if(curSelect_Device && curSelect_Device != marker2[realData[0]].data)
                {
                  marker2[realData[0]].setVisible(false);
                }
                else {
                  marker2[realData[0]].setVisible(true);
                }
              }
              animatedMove(marker2[realData[0]],.5,marker2[realData[0]].position,new google.maps.LatLng( realData[1], realData[2]));
              //(marker2[realData[0]]).setPosition(new google.maps.LatLng( realData[1], realData[2] ));
            });
            //alert(marker2[i].data);
          }
        }, 1500);


        /*google.maps.event.addListener(map, 'click', function(event) {
            //alert('Lat: ' + event.latLng.lat() + ' Lng: ' + event.latLng.lng());

            $.get("add_point.php?lat="+event.latLng.lat()+"&lng="+event.latLng.lng(), function(data, status){
              alert("Data: " + data + "\nStatus: " + status);
            });

          	var marker2 = new google.maps.Marker({
          	   position: new google.maps.LatLng(event.latLng.lat(), event.latLng.lng()),
          	   map: map,
          	   title: 'TEST'
          	});
        });*/
      }

      var pathCoordinates = Array();
      var objectPolyline;
      function drawPath(datado) {
    		objectPolyline = new google.maps.Polyline({
    			path : pathCoordinates,
    			geodesic : true,
    			strokeColor : '#Ee0000',
    			strokeOpacity : 0.7,
    			strokeWeight : 1,
    			map : map,
    			icons: [{
                icon: {path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW},
                offset: '100%',
                repeat: '20px'
            }],
          data : datado
    		});
    	}
      $(document).ready(function(){
        $("#cancle_all").click(function(){
          curSelect_Device = "";
          for(var i = 0;i<marker2.length;i++)
          {
            marker2[i].setMap(null);
          }
          if(marker_sim != null)
          {
            marker_sim.setMap(null);
          }
          marker2 = [];
          marker_sim = null;
          call_first_point();
          document.getElementById("select_device_only").innerHTML="เลือกช้างแบบเจาะจง";
          document.getElementById("date_start").value="";
          document.getElementById("date_end").value="";
          isSimulator = false;
        });
        $("#simulator").click(function(){
          isSimulator = true;
          $.get("checkway.php?Key="+$("#device_for_checkway").val()+"&date_start="+$("#date_start").val()+"&date_end="+$("#date_end").val(),function(data,status){

            marker_sim = new google.maps.Marker({
               position: new google.maps.LatLng(14.548622, 101.365763),
               map: map,
               title: "",
               icon: 'image/wait.gif'
            });
            marker_sim.setVisible(false);
            var newdata = data.split("?");

            /*for(var i = 0;i < newdata.length - 1;i++)
            {
              var realData = newdata[i].split(",");
              //marker_sim.setPosition(new google.maps.LatLng( realData[2], realData[3]));

              animatedMove(marker_sim,.5,marker_sim.position,new google.maps.LatLng( realData[2], realData[3]));

            }*/

            var index = 0;
            var isMove = false;
            var id = setInterval(function(){

              var realData = newdata[index].split(",");
              if(!isMove)
              {
                var lat = marker_sim.getPosition().lat();
                var lng = marker_sim.getPosition().lng();

                var deltalat = (new google.maps.LatLng(realData[2], realData[3]).lat() - marker_sim.getPosition().lat()) / 100;
                var deltalng = (new google.maps.LatLng(realData[2], realData[3]).lng() - marker_sim.getPosition().lng()) / 100;

                var delay = 10 * .5;
                for (var i = 0; i < 100; i++) {
                  (function(ind) {
                    setTimeout(
                      function() {
                        var lat = marker_sim.position.lat();
                        var lng = marker_sim.position.lng();
                        lat += deltalat;
                        lng += deltalng;
                        latlng = new google.maps.LatLng(lat, lng);
                        marker_sim.setPosition(latlng);
                      }, delay * ind
                    );
                  })(i)
                }


                //animatedMove(marker_sim,.5,marker_sim.position,new google.maps.LatLng( realData[2], realData[3]));
                isMove = true;
              }

              if(marker_sim.getPosition().lat().toFixed(6) == new google.maps.LatLng(realData[2], realData[3]).lat())
              {
                map.setCenter(new google.maps.LatLng(realData[2], realData[3]));
                marker_sim.setVisible(true);
                if(objectPolyline != null)
                {
                  //alert(objectPolyline.data);
                  objectPolyline.setMap(null);
                  objectPolyline = null;
                }
                if(parseFloat(realData[2]) > 0 && parseFloat(realData[3]) > 0)
                {
                  pathCoordinates.push({
            				lat : parseFloat(realData[2]),
          					lng : parseFloat(realData[3])
          				});
                }
                drawPath($("#device_for_checkway").val());
                index++;
                isMove = false;
              }

              if(index == newdata.length - 1)
              {
                clearInterval(id);
              }
              //clearInterval(id);
            }, 500,index,isMove);

          })
        });
        $("#CheckingWay").click(function(){
          $.get("checkway.php?Key="+$("#device_for_checkway").val()+"&date_start="+$("#date_start").val()+"&date_end="+$("#date_end").val(),function(data,status){

            pathCoordinates = [];
            if(objectPolyline != null)
            {
              //alert(objectPolyline.data);
              objectPolyline.setMap(null);
              objectPolyline = null;
            }
            else {
              var newdata = data.split("?");

              for(var i = 0;i < newdata.length - 1;i++)
              {
                var realData = newdata[i].split(",");

                var marker2 = new google.maps.Marker({
              	   position: new google.maps.LatLng(realData[2], realData[3]),
              	   map: map,
              	   title: realData[1],
                   icon: 'image/iconn.png'
              	});

                if(parseFloat(realData[2]) > 0 && parseFloat(realData[3]) > 0)
                {
                  pathCoordinates.push({
            				lat : parseFloat(realData[2]),
          					lng : parseFloat(realData[3])
          				});
                }
              }

            /*  pathCoordinates.push({
                lat : parseFloat(14.148502),
                lng : parseFloat(101.366008)
              });*/
              drawPath($("#device_for_checkway").val());
            }
          });
        });
      });
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXyGMj8rdeGL_Lw7dhN7G4xOB7Ll0xGN0&callback=initMap"
    async defer></script>

    <div class="modal" id="modal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">จัดการอุปกรณ์</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p id="option_detail"></p>
            <input type="hidden" id="device_for_checkway" value="null">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="simulator" class="btn btn-warning">Simulator</button>
            <button type="button" id="CheckingWay" class="btn btn-primary">ตรวจสอบเส้นทาง</button>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
