<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
include ('./API/config.php');
include ('./API/Sample.php');
date_default_timezone_set('America/Bogota');
if(isset($_GET["cat"])){
    switch ($_GET["cat"]) {
        case 'sample':
            $ch = curl_init();
            $data = array(
                'sensor' => "2",
                'lat' => "10.99130388",
                'lon' => "-74.82096638",
                'spd' => $_GET["speed"],
                'dir' => 'WSW',
                'temp' => '32'
            );
            $params="type=sample";
            foreach ($data as $key => $value) {
                $params = $params."&".$key."=".$value;
            }

            curl_setopt($ch, CURLOPT_URL, 'http://ec2-54-91-250-124.compute-1.amazonaws.com/API/windapi.php?'.$params);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

            $content = curl_exec($ch);
            curl_close($ch);
            echo $params."\n";
            echo var_dump($content);

            break;
        case 'notify':
            SendNotifications($_GET["sensor"],$_GET["speed"],date('Y-m-d H:i:sP'));
            break;
        default:
            echo "please post sample/notify + speed";
            break;
    }
}

function SendNotifications($sensor, $speed, $date){
    $content = array(
        "es" => "Sensor#".$sensor.": ".$speed." km/h \n".$date,
        "en" => "Sensor#".$sensor.": ".$speed." km/h \n".$date
    );
    $Segment = "Sensor ".$sensor;
    $Segments = array($Segment,"web users");
    $fields = array(
        'app_id' => "d18140e5-8295-4839-899d-73f70285fb35",
        'included_segments' => $Segments,
        'data' => array("foo" => "bar"),
        'contents' => $content
    );
    $fields = json_encode($fields);
    $headers = array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic OWRiZTc2YWUtOTZlZi00ZTA2LTkwYzAtNTE1OGVmNDg3YTc1'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $resp = curl_exec($ch);
    echo "sending alert\n";
    echo var_dump($resp);
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1">
        <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
		<link href="fonts/font-awesome.min.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<!-- Loading main css file -->
		<link rel="stylesheet" href="css/styles.css">
		<link rel="stylesheet" href="css/minimal-menu.css"  type="text/css">
		<title>simulacion WIND</title>
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
                            <li class="menu-item"><a href="/">Inicio</a></li>
                            <li class="menu-item"><a href="#">Ubicacion</a></li>
							<li class="menu-item current-menu-item"><a href="#">Simulación</a></li>
						</ul>
					</div>
				</nav>
				<div class="mobile-navigation"></div>
			</div>
		</header>
		<div class="site-content">
			<br>
			<div class="container wrapper">
                <div class="row">
                    <h2>Control de simulación</h1>
                    <br>
                    <form>
                        <div class="form-group">
                            <label for="func">Función:</label>
                            <select class="form-control" name="func">
                                <option value="notify">Simular notificación</option>
                                <option value="sample">Simular muestra</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sensor">Sensor: </label>
                            <input type="number" class="form-control" name="sensor"value="">
                        </div>
                        <div class="form-group">
                            <label for="speed">Velocidad: </label>
                            <input type="number" class="form-control" name="speed" value="">
                        </div>
                        <div class="form-group">
                            <label for="dir">Direccion: </label>
                            <select class="form-control" name="dir">
                                <option value="N">N</option>
                                <option value="S">S</option>
                                <option value="E">E</option>
                                <option value="W">W</option>
                                <option value="NE">NE</option>
                                <option value="NW">NW</option>
                                <option value="SE">SE</option>
                                <option value="SW">SW</option>
                            </select>
                        </div>
                        <br>
                        <div class="form-group">
                            <input type="button" id="send" class="btn btn-default" name="submit" value="Enviar">
                        </div>
                    </form>
                    <script src="js/jquery-1.11.1.min.js"></script>
                    <script type="text/javascript">
                    $(document).ready(function (){
                        $("#send").click(function(ev) {
                            var params="";
                            var func = $('select[name=func]').val();
                            var sensor = $('input[name=sensor]').val();
                            var spd = $('input[name=speed]').val();
                            var dir = $('select[name=dir]').val();
                            if (func == "sample") {
                                params = "API/windapi.php?type=sample&sensor="+sensor+"&lat=0&lon=0&spd="+spd+"&dir="+dir+"&bat=75&temp=31";
                            }else if(func == "notify"){
                                params = "simulation.php?cat=notify&sensor="+sensor+"&speed="+spd
                            }
                            console.log(params);
                            $.ajax({
                                method: "GET",
                                url: "http://ec2-54-91-250-124.compute-1.amazonaws.com/"+params,
                                success: function (data, textStatus, jqXHR) {
                                    //console.log(data);
                                    alert("Función realizada");
                                }
                            });

                        });
                    });
                    </script>
                </div>
            </div>
        </div>
    </body>
</html>
