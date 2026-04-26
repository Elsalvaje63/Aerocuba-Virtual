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
<?php
	/* Connect to Database */
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
	// Execute SQL query
	$sql_map = "select * from vam_track where flight_id='".$vamflightid ."' order by id asc";
	
	if (!$result = $db_map->query($sql_map)) {
		die('There was an error running the query  [' . $db_map->error . ']');
	}
	unset($flights_coordinates);
	$flights_coordinates = array();
	$datos = array ();
	$index = 0;
	while ($row = $result->fetch_assoc()) {
		
		$flights_coordinates ["latitude"] = $row["latitude"];
		$flights_coordinates ["longitude"] = $row["longitude"];
		$datos[$index] = $flights_coordinates;
		$index++;
	}

	// Execute SQL query
	$sql_map = "select * from puntos_mapa order by id asc";
	
	if (!$result = $db_map->query($sql_map)) {
		die('There was an error running the query  [' . $db_map->error . ']');
	}
	unset($puntos_mapa);
	$puntos_mapa = array();
	$puntos = array ();
	$indice = 0;
	while ($row = $result->fetch_assoc()) {
		$puntos_mapa ["type"] = $row["type"];
		$puntos_mapa ["latitude"] = $row["latitude"];
		$puntos_mapa ["longitude"] = $row["longitude"];
		$puntos_mapa ["name"] = $row["name"];
		$puntos_mapa ["freq"] = $row["freq"];
		$puntos[$indice] = $puntos_mapa;
		$indice++;
	}

	
?>
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

<script type="text/javascript">
	
		var map;	
		var locations = <?php echo json_encode($datos); ?>;
		var numpoints=(locations.length);
		
		var puntos = <?php echo json_encode($puntos); ?>;
		var numero_puntos=(puntos.length);
		
		var datos_lat = <?php echo $datos[0]["latitude"]; ?>;
		var datos_lon = <?php echo $datos[0]["longitude"]; ?>;
		var dep = new L.LatLng(datos_lat, datos_lon);
		
		var map = L.map('map-container').setView([datos_lat, datos_lon], 5);
	    map.addControl(new L.Control.Fullscreen());
		L.tileLayer('https://api.tiles.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token={accessToken}', {
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		maxZoom: 18,
		accessToken: 'pk.eyJ1IjoicnNhbnRvc2YiLCJhIjoiY2tnZHZ1OW92MG42bTJ4bzc3azUyYmZ0YyJ9.Wj3UU1oRmxxkcFpZMBYGxw'
	    }).addTo(map);
	
	//loop
		var flightPlanCoordinates=[];
		var k=0;
		
		var coordinate;
		while (k<numpoints) {
			
			coo_lat = locations[k]['latitude'];
			coo_lon = locations[k]['longitude'];
			coordinate = new L.LatLng(coo_lat,coo_lon);
			flightPlanCoordinates.push(coordinate);
			k=k+1;
		};
		
		//icon
				var icon = L.icon({
					iconUrl: 'ic_location_on_arancio_24dp_1x.png',
					iconSize: [15, 24],
					iconAnchor: [7, 22]
				});
				
				var icono_waypoint = L.icon({
					iconUrl: 'way.png',
          			iconSize: [32, 32],
          			iconAnchor: [32, 32]
				});
				
				var icono_vor = L.icon({
					iconUrl: 'vor.png',
          			iconSize: [32, 32],
          			iconAnchor: [32, 32]
				});
		
		//polyline
				var polyline = L.polyline(flightPlanCoordinates, {
				color: 'blue',/*'#FFAF33',*/
				opacity: 1.0,
				weight: 1	
				}).addTo(map);
				k=k+2;
		
		//marker
				var marker_dep = new L.marker(dep, {
				icon: icon
				}).addTo(map);
				
				var marker_arr = new L.marker(coordinate, {
				icon: icon
				}).addTo(map);
				
					
	//loop2
		var conjunto_coordenadas=[];
		var k=0;
		
		var coordenada;
		while (k<numero_puntos) {
			
			coo_lat = puntos[k]['latitude'];
			coo_lon = puntos[k]['longitude'];
			name = puntos[k]['name'];
			freq = puntos[k]['freq'];
			type = puntos[k]['type'];
			
			coordenada = new L.LatLng(coo_lat,coo_lon);
			if (type=="W") {
				var marker_arr = new L.marker(coordenada, 
				{ icon: icono_waypoint },
				{ riseOnHover: true },
			    { pane: 'markerPane' },
			    { bubblingMouseEvents: true }
				).addTo(map).bindTooltip(name,{direction:'top'}); 
			} else {
				var marker_arr = new L.marker(coordenada, 
				{ icon: icono_vor },
				{ riseOnHover: true },
			    { pane: 'markerPane' },
			    { bubblingMouseEvents: true }
				).addTo(map).bindTooltip(name+' - '+freq,{direction:'top'});
			}	
			k=k+1;
		};
		

		
</script>
</body>
</html>
