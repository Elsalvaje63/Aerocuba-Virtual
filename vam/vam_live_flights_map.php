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
<style>
body { margin:0; padding:0; }
#map { position:absolute; top:0; bottom:0; width:100%; }
</style>
</head>
<body>
<div class="container">
	<div class="row">
		<div id="map-outer" class="col-md-11">
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
		background-color:#FFFFF }
	#map-container { height: 500px }
	@media all and (max-width: 991px) {
		#map-outer  { height: 650px }
	}
</style>
<?php
	include('./db_login.php');
	$db_map = new mysqli($db_host , $db_username , $db_password , $db_database);
	$db_map->set_charset("utf8");
	if ($db_map->connect_errno > 0) {
		die('Unable to connect to database [' . $db_map->connect_error . ']');
	}
	$sql = "set SESSION SQL_MODE='NO_ENGINE_SUBSTITUTION'";
	if (!$result = $db_map->query($sql)) {
	  die('There was an error running the query  [' . $db_map->error . ']');
	}
	unset($flight);
	$flight = array();
	unset($geojson);
	$geojson = array();
	unset($index);
	$index=0;
	unset($index2);
	$index2=0;
	unset($flights_coordinates);
	$flights_coordinates = array();
	unset($geojson);
	$geojson = array();

	$sql_map = "SELECT DISTINCT flight_id,u.gvauser_id as gvauser_id,u.callsign as callsign,u.name as name,surname,departure,arrival,latitude,longitude,heading,a1.latitude_deg as dep_lat, a1.longitude_deg as dep_lon , a2.latitude_deg as arr_lat, a2.longitude_deg as arr_lon, lf.ias as velocidad, lf.altitude as altitud,lf.gs as groundspeed,lf.time_passed as tiempovuelo FROM vam_live_flights lf, gvausers u , airports a1, airports a2 where u.gvauser_id=lf.gvauser_id and lf.departure=a1.ident and lf.arrival=a2.ident";
	if (!$result = $db_map->query($sql_map)) {
		die('There was an error running the query  [' . $db_map->error . ']');
	}
	while ($row = $result->fetch_assoc()) {
		$flight[$index]["tooltip"]='<b>'.$row["callsign"].'</b> - '.$row["name"].' '.$row["surname"].'<br><b>Ruta:</b> '.$row["departure"].' - '.$row["arrival"].'<br><b>IAS:</b> '.$row["velocidad"].' kts - <b>GS:</b> '.$row["groundspeed"].' kts<br><b>Altitud:</b> '.$row["altitud"].' ft<br><b>Tiempo de vuelo:</b> '.($row["tiempovuelo"]/60).' min';
		$flight[$index]["latitude"]=$row["latitude"];
		$flight[$index]["longitude"]=$row["longitude"];
		$flight[$index]["heading"]=$row["heading"];
		$flight[$index]["flight_id"]=$row["flight_id"];
		$flight[$index]["departure"]=$row["departure"];
		$flight[$index]["arrival"]=$row["arrival"];
		$flight[$index]["dep_lon"]=$row["dep_lon"];
		$flight[$index]["dep_lat"]=$row["dep_lat"];
		$flight[$index]["arr_lon"]=$row["arr_lon"];
		$flight[$index]["arr_lat"]=$row["arr_lat"];
		
	$sql_map2 = "SELECT DISTINCT * from vam_live_acars where flight_id='".$row["flight_id"]."' order by id asc";
		if (!$result2 = $db_map->query($sql_map2)) {
			die('There was an error running the query  [' . $db_map->error . ']');
		}
			while ($row2 = $result2->fetch_assoc()) {
				$flights_coordinates[0] = $row2["longitude"];
				$flights_coordinates[1] = $row2["latitude"];
				$datos[$index][$index2] = json_encode($flights_coordinates,JSON_NUMERIC_CHECK);
				$index2 ++;
				}
		$index2=0  ;
		$index ++;
		$flightindex ++;
	}
?>

<script type="text/javascript">
	
var map;
var flight = <?php echo json_encode($flight); ?>;

var center_lat = <?php echo $flight[0]["latitude"]; ?>;
var center_lon = <?php echo $flight[0]["longitude"]; ?>;
		
var map = L.map('map-container').setView([center_lat, center_lon], 6);
map.addControl(new L.Control.Fullscreen());
	
		L.tileLayer('https://api.tiles.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token={accessToken}', {
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		maxZoom: 18,
		accessToken: 'pk.eyJ1IjoicnNhbnRvc2YiLCJhIjoiY2tnZHZ1OW92MG42bTJ4bzc3azUyYmZ0YyJ9.Wj3UU1oRmxxkcFpZMBYGxw'
	    }).addTo(map);


//Loop for each flight

var flights = <?php echo $index; ?>;
var datos = <?php echo json_encode($datos); ?>;

    for (x=0;x<flight.length;x++){
		var lat = flight[x]['latitude'];
		var lon = flight[x]['longitude'];
		var tooltip = flight[x]['tooltip'];
		var heading = flight[x]['heading'];
		var departure = flight[x]['departure'];
		var arrival = flight[x]['arrival'];
		var dep_lat = flight[x]['dep_lat'];
		var dep_lon = flight[x]['dep_lon'];
		var arr_lat = flight[x]['arr_lat'];
		var arr_lon = flight[x]['arr_lon'];
		
		var dep = new L.latLng(dep_lat, dep_lon);
		var arr = new L.latLng(arr_lat, arr_lon);

		var pos = new L.LatLng(lat, lon);
    
		var icon = L.icon({
		    iconUrl: 'ic_location_on_arancio_24dp_1x.png',
   			iconSize: [15, 24],
   			iconAnchor: [7, 22]
		});

	
		var icon_plane = new L.icon({
		iconUrl: './map_icons_white/'+heading+'.png',
		iconSize: [27, 27],
		tooltipAnchor: [0,-10]
		});

		var marker_flight = new L.marker(
			pos,
			{icon: icon_plane},
			{riseOnHover: true},
			{pane: 'markerPane'},
			{bubblingMouseEvents: true}
			).addTo(map).bindTooltip(tooltip,{direction:'top'});

		var marker_dep = new L.marker(dep, 
				{ icon: icon },
				{ riseOnHover: true },
			    { pane: 'markerPane' },
			    { bubblingMouseEvents: true }
				).addTo(map).bindTooltip(departure,{direction:'top'});

		var marker_arr = new L.marker(arr, 
				{ icon: icon },
				{ riseOnHover: true },
			    { pane: 'markerPane' },
			    { bubblingMouseEvents: true }
				).addTo(map).bindTooltip(arrival,{direction:'top'});

				var route = [dep, pos];
				var polyline = L.polyline(route, {
				color: 'blue',
				opacity: 1.0,
				weight: 1	
				}).addTo(map);

				var route = [pos, arr];
				var polyline = L.polyline(route, {
				color: 'blue',
				opacity: 1.0,
				weight: 1	
				}).addTo(map);

		
	}




var myStyle = {
    "color": "#ff7800",
    "weight": 3,
    "opacity": 0.65
};


</script>
</body>
</html>