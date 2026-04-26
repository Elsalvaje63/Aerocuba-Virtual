<?php
	/**
	 * @Project: Virtual Airlines Manager (VAM)
	 * @Author: Alejandro Garcia
	 * @Web http://virtualairlinesmanager.net
	 * Copyright (c) 2013 - 2016 Alejandro Garcia
	 * VAM is licenced under the following license:
	 *   Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0)
	 *   View license.txt in the root, or visit http://creativecommons.org/licenses/by-nc-sa/4.0/
	 */
?>
<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8' />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
  integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
  crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
  integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
  crossorigin=""></script>
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
</head>
<body>
<?php
	$hubID = $_GET['hub_id'];
	include('db_login.php');
	$lat_centro='';
	$long_centro='';
	$db = new mysqli($db_host , $db_username , $db_password , $db_database);
	$db->set_charset("utf8");
	if ($db->connect_errno > 0) {
		die('Unable to connect to database [' . $db->connect_error . ']');
	}
	$sql = "set SESSION SQL_MODE='NO_ENGINE_SUBSTITUTION'";
	if (!$result = $db->query($sql)) {
	  die('There was an error running the query  [' . $db->error . ']');
	}
	$sql = "SELECT * FROM routes INNER JOIN airports ON airports.ident = routes.arrival  WHERE hub_id = $hubID";
	if (!$result = $db->query($sql)) {
		die('There was an error running the query  [' . $db->error . ']');
	}
	$index=-1;
	while ($row = $result->fetch_assoc()) {
		$index++;
		$flights_coordinates [$index] = array ($row["latitude_deg"],  $row["longitude_deg"] ,  $row["ident"],  $row["name"] ) ;
	}
	$sql = "SELECT * FROM  hubs h  INNER JOIN airports a on a.ident=h.hub WHERE h.hub_id = $hubID ";
	if (!$result = $db->query($sql)) {
		die('There was an error running the query  [' . $db->error . ']');
	}
	while ($row = $result->fetch_assoc()) {
		$lat_centro = $row["latitude_deg"];
		$long_centro = $row["longitude_deg"];
	}
?>
<div class="container">
	<div class="row">
		<div id="map-outer" class="col-md-12">
			<div id="map-container" class="col-md-12"></div>
		</div><!-- /map-outer -->
	</div> <!-- /row -->
</div><!-- /container -->
<style>
	body { background-color:#FFFFF }
	#map-outer {
		padding: 0px;
		border: 0px solid #CCC;
		margin-bottom: 0px;
		background-color:#FFFFF;
		width:100%;
		height:480px}
	#map-container { height: 100%}
</style>
</body>
<script language="javascript">

	var map;
	var locations = [<?php echo json_encode($flights_coordinates); ?>];
	var var_location = [[<?php echo $flights_coordinates[0][0]; ?>,<?php echo $flights_coordinates[0][1]; ?>]];
	
	<!-- var map = L.map('map-container').setView([40, -1], 5); -->
	<!-- Mejorado para que se centre -->
	
	var center_lat = <?php echo $lat_centro; ?>;
	var center_lon = <?php echo $long_centro; ?>;
	var map = L.map('map-container').setView([center_lat, center_lon], 5);
	map.addControl(new L.Control.Fullscreen());
	L.tileLayer('https://api.tiles.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token={accessToken}', {
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		maxZoom: 18,
		accessToken: 'pk.eyJ1IjoicnNhbnRvc2YiLCJhIjoiY2tnZHZ1OW92MG42bTJ4bzc3azUyYmZ0YyJ9.Wj3UU1oRmxxkcFpZMBYGxw',
		center: [40.3716749,-3.7911308]
	}).addTo(map);

	var markers = <?php echo json_encode($flights_coordinates); ?>;

	//loop
	var k=0;
	while (k<100) {

			var dep_lat = markers[k][0];
			var dep_lon = markers[k][1];
			var dep_ident = markers[k][2];
			var dep_name = markers[k][3];
			var arr_lat = markers[k+1][0];
			var arr_lon = markers[k+1][1];
			var arr_ident = markers[k+1][2];
			var arr_name = markers[k+1][3];
			var dep = new L.latLng(dep_lat, dep_lon);
			var arr = new L.latLng(arr_lat, arr_lon);
			
			//icon
				var icon = L.icon({
				iconUrl: 'ic_location_on_arancio_24dp_1x.png',
          			iconSize: [15, 24],
          			iconAnchor: [7, 22]
				});
			
			//marker
				var marker_dep = new L.marker(dep, 
				{ icon: icon },
				{ riseOnHover: true },
			    { pane: 'markerPane' },
			    { bubblingMouseEvents: true }
				).addTo(map).bindTooltip(dep_ident + '-' + dep_name,{direction:'top'});
				
				var marker_arr = new L.marker(arr, 
				{ icon: icon },
				{ riseOnHover: true },
			    { pane: 'markerPane' },
			    { bubblingMouseEvents: true }
				).addTo(map).bindTooltip(arr_ident + '-' + arr_name,{direction:'top'});

		
			//polyline
				var route = [dep, arr];
				var polyline = L.polyline(route, {
				color: 'blue',
				opacity: 1.0,
				weight: 1	
				}).addTo(map);
			k=k+2;
				
		}

</script>
</html>
