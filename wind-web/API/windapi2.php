<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    //header('Content-Type: application/json; charset=utf-8');
    header('Content-Type: text/plain; charset=utf-8');
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
    include ('./config.php');
    include ('./Sample.php');
    date_default_timezone_set('America/Bogota');

    global $_DATABASE;
    $_DATABASE = new PDO("mysql:host=" . $DB_Address . ";dbname=" . $DB_Name, $DB_UserName, $DB_Password);
    $_DATABASE->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $Response = array();
    // DataBase

    try {
        chmod($path, 0777);
        if (file_exists("/var/www/html/API/log.txt")) {
          $errorlog = fopen("/var/www/html/API/log.txt","a+");
          fwrite($errorlog, "connected-web/wasp\n");
          fclose($errorlog);
        }else {
          $errorlog = fopen("/var/www/html/API/log.txt","w+");
          fwrite($errorlog, "connected-web/wasp\n");
          fclose($errorlog);
        }

        switch ($_POST['type']) {
            case 'sample':
                Sample::saveToDataBase(
                    $_POST["sensor"], $_POST["lat"], $_POST["lon"], $_POST["spd"], $_POST["dir"], date('Y-m-d H:i:sP')
                );
                //$Response["status"] = 1;
                //$Response["message"] = "record saved";
                echo "record saved";
                break;
            case 'test':
                //$Response["status"] = 1;
                //$Response["message"] = "connected";
                echo "connected";
                break;
        }
    }catch (Exception $e) {
        //$Response["status"] = 0;
    	  //$Response["message"] = $e->getMessage();
        echo "error: ".$e->getMessage();
      	$errorlog = fopen("/var/www/html/API/log.txt","a");
      	fwrite($errorlog, $e->getMessage());
      	fclose($errorlog);
    }
    json_encode($Response);
    exit();
?>
