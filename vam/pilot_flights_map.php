<?php
	/**
	 * @Project: Virtual Airlines Manager (VAM)
	 * Convertido a Mapbox 
	 */
?>

<!DOCTYPE html>
<html>
<head>
<!--MapBox-->
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
	/* Connect to Database */
	$db_map = new mysqli($db_host , $db_username , $db_password , $db_database);
	$db_map->set_charset("utf8");
	if ($db_map->connect_errno > 0) {
		die('Unable to connect to database [' . $db_map->connect_error . ']');
	}
	$sql = "set SESSION SQL_MODE='NO_ENGINE_SUBSTITUTION'";
	if (!$result = $db_map->query($sql)) {
	  die('There was an error running the query  [' . $db_map->error . ']');
	}
	// Execute SQL query
	$sql_map = "select from_airport departure, to_airport arrival ,date from pireps where valid<>3 and valid<>2 and gvauser_id=$id
    UNION
    select  SUBSTRING(OriginAirport,1,4) departure, SUBSTRING(DestinationAirport,1,4) arrival , CreatedOn as date from pirepfsfk where gvauser_id=$id
    UNION
    SELECT origin_id as departure, destination_id as arrival, date from reports where pilot_id=$id
	UNION
    SELECT departure, arrival, flight_date as date from vampireps where gvauser_id=$id
    order by date desc limit 50";
	if (!$result = $db_map->query($sql_map)) {
		die('There was an error running the query  [' . $db_map->error . ']');
	}
	unset($flights);
	$flights = array();
	$index = 0;
	while ($row = $result->fetch_assoc()) {
		$flights [$index] = $row["departure"];
		$index++;
		$flights [$index] = $row["arrival"];
		$index++;
	}

	$flights_coordinates = array ();
	$index = -1;
	foreach($flights as $flight) {
		$sql_map = "select latitude_deg, longitude_deg ,ident , airports.name as airport_name  from airports where ident='$flight'";
		if (!$result = $db_map->query($sql_map)) {
			die('There was an error running the query  [' . $db_map->error . ']');
		}
		while ($row = $result->fetch_assoc()) {
			$index++;
			$flights_coordinates [$index] = array ($row["latitude_deg"],  $row["longitude_deg"] ,  $row["ident"],  $row["airport_name"] ) ;
		}
	}
?>
<div class="container">
	<div class="row">
		<div id="map-outer" class="col-md-11">
			<div id="map-container" class="col-md-12">
			</div>
		</div><!-- /map-outer -->
	</div> <!-- /row -->
</div><!-- /container -->
<style>
	body { background-color:#FFFFF }
	#map-outer {
		padding: 0px;
		border: 0px solid #CCC;
		margin-bottom: 0px;
		background-color:#FFFFF }
	#map-container { height: 500px }
	@media all and (max-width: 991px) {
		#map-outer  { height: 650px }
	}
</style>
</body>

<script language="javascript">

	var map;
	var locations = [<?php echo json_encode($flights_coordinates); ?>];
	var var_location = [[<?php echo $flights_coordinates[0][0]; ?>,<?php echo $flights_coordinates[0][1]; ?>]];
	var datos_lat = <?php echo $flights_coordinates[0][0]; ?>;
	var datos_lon = <?php echo $flights_coordinates[0][1]; ?>;
	
	
	var map = L.map('map-container').setView([datos_lat,datos_lon], 5);
    map.addControl(new L.Control.Fullscreen());
	
		L.tileLayer('https://api.tiles.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token={accessToken}', {
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		maxZoom: 18,
		accessToken: 'pk.eyJ1IjoicnNhbnRvc2YiLCJhIjoiY2tnZHZ1OW92MG42bTJ4bzc3azUyYmZ0YyJ9.Wj3UU1oRmxxkcFpZMBYGxw'
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
