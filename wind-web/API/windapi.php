<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    //header('Content-Type: application/json; charset=utf-8');
    header('Content-Type: text/plain; charset=utf-8');
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
    include ('./config.php');
    include ('./Sample.php');
    include('./mailer/class.phpmailer.php');
    date_default_timezone_set('America/Bogota');

    global $_DATABASE;
    $_DATABASE = new PDO("mysql:host=" . $DB_Address . ";dbname=" . $DB_Name, $DB_UserName, $DB_Password);
    $_DATABASE->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $Response = array();
    // DataBase

    function sendBatteryAlert($email, $sensor){
        $MSG="Nivel bajo de bateria en el sensor #"+$sensor;
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->From = "admin@pf-wind.com";
        $mail->FromName = "PF WIND";
        $mail->IsHTML(true);
        $mail->WordWrap = 50;
        $mail->AddAddress($email,"PF WIND Admin");
        $mail->Subject = "Alerta de bateria";
        $mail->Body = $MSG;
        $mail->AltBody = strip_tags ( $MSG );
        $mail->ClearAddresses();
        $mail->ClearAttachments();
    }

    function SendNotifications($sensor, $speed, $date){
        $content = array(
		    "es" => "Sensor#".$sensor.": ".$speed." km/h \n".$date,
            "en" => "Sensor#".$sensor.": ".$speed." km/h \n".$date
		);
        $Segment = "sensor ".$sensor;
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

    try {
        switch ($_GET['type']) {
            case 'sample':
                $date = date('Y-m-d H:i:sP');
                Sample::saveToDataBase(
                    $_GET["sensor"], $_GET["lat"], $_GET["lon"], $_GET["spd"], $_GET["dir"], $date, $_GET["temp"]
                );
                if ($_GET["spd"]>=4) {
                    SendNotifications($_GET["sensor"],$_GET["spd"], $date);
                }

                if(isset($_GET["bat"])){
                    global $_DATABASE;
                    $batteryLVL = $_GET["bat"];
                    $query = " UPDATE sensores SET bateria = ? WHERE Id_sensor = ?";
                    $stmt = $_DATABASE->prepare( $query );
                    $stmt->bindParam( 1, $batteryLVL );
                    $stmt->bindParam( 2, $_GET["sensor"] );
                    $stmt->execute();
                    if($batteryLVL==15){
                        //send email notification
                        if($_GET["sensor"]==2){
                            sendBatteryAlert("herik02.11@gmail.com", "2");
                        }else{
                            sendBatteryAlert("sllanos@uninorte.edu.co", "1");
                        }
                    }
                }
                echo "record saved";
                break;
            case 'frontinfo':
                $info = Sample::loadMostRecent($_GET["sensor"]);
                $Response["status"]="1";
                $Response["temp"]=$info["temp"];
                $Response["vel"]=$info["vel"];
                $Response["dir"]=$info["dir"];
                $Response["sensor"]=$info["id_sensor"];
                echo json_encode($Response);
                break;
            case "sensorhistory":
                $Response["status"]="1";
                $Response["history"]=Sample::loadAllFromDataBase($_GET["sensor"]);
                echo json_encode($Response);
                break;
        }
    }catch (Exception $e) {
        if($_GET["type"]=="frontinfo"){
            $Response["status"] = 0;
        }else{
            echo "error: ".$e->getMessage();
        }
        $errorlog = fopen("/var/www/html/API/log.txt","a");
      	fwrite($errorlog, $e->getMessage());
      	fclose($errorlog);
    }
    // json_encode($Response);
    exit();
?>
