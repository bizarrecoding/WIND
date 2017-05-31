<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
include ('./API/config.php');
include ('./API/Sample.php');
date_default_timezone_set('America/Bogota');

global $_DATABASE;
$_DATABASE = new PDO("mysql:host=" . $DB_Address . ";dbname=" . $DB_Name, $DB_UserName, $DB_Password);
$_DATABASE->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$samples = Sample::loadAllFromDataBase(1);
$ctSample = array_values($samples)["0"];

$query = "SELECT Id_sensor, lat, lon FROM `sensores` ";
$stmt = $_DATABASE->prepare( $query );
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC , PDO::FETCH_ORI_NEXT))
	$sensors[$row["Id_sensor"]] = $row;
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1">
		<title>Wind</title>

		<!-- Loading third party fonts -->
		<link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
		<link href="fonts/font-awesome.min.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<!-- Loading main css file -->
		<link rel="stylesheet" href="css/styles.css">
		<link rel="stylesheet" href="css/minimal-menu.css"  type="text/css">
		<!--script src='https://www.google.com/recaptcha/api.js'></script-->
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async='async'></script>
		<script>
			var OneSignal = window.OneSignal || [];
			OneSignal.push(["init", {
		    	appId: "d18140e5-8295-4839-899d-73f70285fb35",
			    autoRegister: false, /* Set to true to automatically prompt visitors */
		    	subdomainName: 'https://pfwind.onesignal.com',
			    httpPermissionRequest: {
				 	enable: true,
					useCustomModal: true,
					modalTitle: 'Gracias por subscribirte',
				   	modalMessage: "¡Listo! ya estas subscrito a las notificaciones. Te puedes salir en cualquier momento.",
				   	modalButtonText: 'Close'
				},
		      	notifyButton: {
					enable: true,
					theme: 'inverse',
					text: {
					   'tip.state.unsubscribed': 'recibe notificaciones',
					   'tip.state.subscribed': "You're subscribed to notifications",
					   'tip.state.blocked': "You've blocked notifications",
					   'message.prenotify': 'Has click para subscribirte a notificaciones',
					   'message.action.subscribed': "¡Gracias por subscribirse!",
					   'message.action.resubscribed': "You're subscribed to notifications",
					   'message.action.unsubscribed': "You won't receive notifications again",
					   'dialog.main.title': 'Administrar notificaciones',
					   'dialog.main.button.subscribe': 'SUBSCRIBIRSE',
					   'dialog.main.button.unsubscribe': 'Cancelar Subscribción',
					   'dialog.blocked.title': 'Desbloquear Notificaciones',
					   'dialog.blocked.message': "Follow these instructions to allow notifications:"
				   }
		      	},
				promptOptions: {
				   	/* These prompt options values configure both the HTTP prompt and the HTTP popup. */
				   	/* actionMessage limited to 90 characters */
				   	actionMessage: "¿Te gustaria recibir notificaciones de las mediciones del viento?",
				   	/* acceptButtonText limited to 15 characters */
				   	acceptButtonText: "ACEPTAR",
				   	/* cancelButtonText limited to 15 characters */
				   	cancelButtonText: "NO GRACIAS"
			   	},
				persistNotification: false
		    }]);
		</script>
	</head>
	<body>
		<header>
			<div class="wrapper">
				<a href="/" class="logo">
					<img src="images/logo.png" alt="">
					<div class="titlebox">
						<h1 class="title">WIND</h1>
						<span class="subtitle">Seguridad y alertas</span>
					</div>
				</a>
				<!-- Nav bar menu -->
				<nav class="main-navigation">
					<label class="minimal-menu-button" for="mobile-nav">Menu</label>
					<input class="minimal-menu-button" type="checkbox" id="mobile-nav" name="mobile-nav">
					<div class="minimal-menu">
						<div class="mblogo">
							<img src="images/logo.png" alt="">
							<div class="titlebox">
								<h1>WIND</h1>
								<span>Seguridad y alertas</span>
							</div>
						</div>
						<ul class="menu">
							<li class="menu-item current-menu-item"><a href="/">Inicio</a></li>
							<li class="menu-item"><a href="#">Ubicacion</a></li>
							<li class="menu-item"><a href="simulation.php">Simulación</a></li>
						</ul>
					</div>
				</nav>
				<div class="mobile-navigation"></div>
			</div>
		</header>
		<div class="site-content">
			<br>
			<div class="container">
				<div id="map">
				</div>
				<div id="sensor">
					<div class="header">
						<div class="day"><p> </p></div>
						<div class="date"><p> </p></div>
					</div>
					<!-- .forecast-header -->
					<div class="content">
						<div class="location"><p>Barranquilla, sensor #<span id="ctSensor"><?php echo $ctSample["id_sensor"] ?><span></p></div>
						<!--div class="measures">
							<p><img src="images/icons/icon-1.svg" alt=""> <span id="ctTemp">28</span><sup>o</sup>C</p>
						</div-->
						<div class="measures">
							<p><img src="images/icon-wind@2x.png" alt=""> <span id="ctVel"><?php echo $ctSample["vel"] ?></span> km/h</p>
						</div>
						<div class="measures">
							<p><img src="images/icon-compass@2x.png" alt=""> <span id="ctDir"><?php echo $ctSample["dir"] ?></span></p>
						</div>
					</div>
				</div>
				<div class="recent">
					<div class="col-md-6">
						<div class="col-md-12" id="sensor1">
							<h2>Sensor #1</h2>
							<div id="chartS1">
							</div>
							<br>
							<table class="table">
								<thead>
									<tr>
										<th>Velocidad</th>
										<th>Dirección</th>
										<th>Hora</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach (Sample::loadAllFromDataBase(1) as $id => $muestra): ?>
										<tr>
											<td><?php echo $muestra["vel"];?></td>
											<td><?php echo $muestra["dir"];?></td>
											<td><?php echo $muestra["hora"];?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="col-md-6">
						<div class="col-md-12" id="sensor2">
							<h2>Sensor #2</h2>
							<div id="chartS2">
							</div>
							<br>
							<table class="table">
								<thead>
									<tr>
										<th>Velocidad</th>
										<th>Dirección</th>
										<th>Hora</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach (Sample::loadAllFromDataBase(2) as $id => $muestra): ?>
										<tr>
											<td><?php echo $muestra["vel"];?></td>
											<td><?php echo $muestra["dir"];?></td>
											<td><?php echo $muestra["hora"];?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<script type="text/javascript">
					var map;
					function initMap() {
					  	map = new google.maps.Map(document.getElementById('map'), {
						    center: {lat: 10.99130, lng: -74.82096},
						    zoom: 13
					  	});
						var image ="http://ec2-54-91-250-124.compute-1.amazonaws.com/images/icon-marker@2x.png";
					  	<?php foreach ($sensors as $id => $LatLon): ?>
					  		var myLatLng = {lat: <?php echo $LatLon["lat"] ?>, lng: <?php echo $LatLon["lon"] ?>};
		   					var marker<?php echo $id ?> = new google.maps.Marker({
								position: myLatLng,
								map: map,
								title: 'Sensor #<?php echo $id ?>',
								icon: image
							});
							marker<?php echo $id ?>.addListener('click', function() {
								map.setZoom(14);
								map.setCenter(marker<?php echo $id ?>.getPosition());
								$.ajax({
									url: "http://ec2-54-91-250-124.compute-1.amazonaws.com/API/windapi.php",
									method: "GET",
									"Content-Type": 'application/json',
									data: {
										type: "frontinfo",
										sensor: <?php echo $id ?>
									},
									success: function(data) {
										data = JSON.parse(data);
										if (data["status"]=="1") {
											$("#ctSensor").text(data["sensor"]);
											$("#ctDir").text(data["dir"]);
											$("#ctVel").text(data["vel"]);
											$("#ctTemp").text(data["temp"]);
										}
									}
								});
							});
					  	<?php endforeach; ?>
					}
				</script>
				<script type="text/javascript">
					google.charts.load('current', {'packages':['corechart', 'line']});
					google.charts.setOnLoadCallback(drawChart);

					function drawChart() {
					  	var data1 = google.visualization.arrayToDataTable([
							['Hora', 'velocidad'],
							<?php
								$count = 0;
								$top71 = array_reverse(Sample::loadAllFromDataBase(1));

								foreach( $top71 as $id => $muestra){
									if ($count<6) {
										echo "['".substr($muestra["hora"],11,5)."', ".$muestra["vel"]."],";
									}else {
										echo "['".substr($muestra["hora"],11,5)."', ".$muestra["vel"]."]";
									}
									$count = $count+1;

								}
							?>
					  	]);

						var data2 = google.visualization.arrayToDataTable([
							['Hora', 'velocidad'],
							<?php
								$count = 0;

								$top72 = array_reverse(Sample::loadAllFromDataBase(2));
								foreach($top72 as $id => $muestra){
									if ($count<6) {
										echo "['".substr($muestra["hora"],11,5)."', ".$muestra["vel"]."],";
									}else {
										echo "['".substr($muestra["hora"],11,5)."', ".$muestra["vel"]."]";
									}
									$count = $count+1;
								}
							?>
						]);
						var options = {
					        hAxis: {
					          title: 'Hora'
					        },
					        vAxis: {
					          title: 'Velocidad'
					        },
					        series: {
					          1: {curveType: 'function'}
					        }
      					};
					  	var chart1 = new google.visualization.LineChart(document.getElementById('chartS1'));
					  	chart1.draw(data1, options);

						var chart2 = new google.visualization.LineChart(document.getElementById('chartS2'));
					  	chart2.draw(data2, options);
					}
				</script>
				<script type="text/javascript">
					var notification = window.Notification || window.mozNotification || window.webkitNotificat;
					if ('undefined' === typeof notification){
						alert('Tu navegador no soporta notificaciones');
					}else{
						notification.requestPermission(function(permission){});
					}
				</script>
				<br>
			</div>
		</div>
		<footer class="site-footer">
			<div class="container">
				<div class="row">
					<br>
					<!--div class="col-md-8">
						<form action="#" class="subscribe-form">
							<input type="text" placeholder="Enter your email to subscribe...">
							<input type="submit" value="Subscribe">
						</form>
					</div>
					<div class="col-md-3 col-md-offset-1">
						<div class="social-links">
							<a href="#"><i class="fa fa-facebook"></i></a>
							<a href="#"><i class="fa fa-twitter"></i></a>
							<a href="#"><i class="fa fa-google-plus"></i></a>
							<a href="#"><i class="fa fa-pinterest"></i></a>
						</div>
					</div-->
				</div>
			</div>
		</footer>
		<!-- .site-footer -->
		<script src="js/jquery-1.11.1.min.js"></script>
		<script type="text/javascript" src="js/app.js"></script>
		<script async defer
	      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCYJOj6LcaM4279-oXjUriuJf-o7FxHBiI&callback=initMap">
	  	</script>
	</body>
</html>
