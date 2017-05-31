<?php
class Sample {
	protected $SampleId;
    protected $SensorId;
    protected $Lat;
    protected $Lon;
    protected $Speed;
    protected $Dir;
    protected $Time;
    protected $Temperature;

    // -------------------------------------------------
	// Getters
	// -------------------------------------------------
    public function getSampleId()      		{ return $this->SampleId; }
    public function getSensorId()      		{ return $this->SensorId; }
    public function getLat()      			{ return $this->Lat; }
    public function getLon()      			{ return $this->Lon; }
	public function getSpeed()      		{ return $this->Speed; }
	public function getDir()      			{ return $this->Dir; }
    public function getTime()      			{ return $this->Time; }
	public function getTemperature()   		{ return $this->Time; }

    function __construct( $db_row = null ){
		if ( $db_row != null)
			$this->setPropertiesFromDbRow( $db_row );
	}

	public static function WithId( $id ){
		$instance = new self();
		$instance->loadFromDataBase( 'id_muestra', $id );
		return $instance;
	}

    protected function setPropertiesFromDbRow( $db_row ){
		try {
			$this->SampleId = $db_row['id_muestra'];
			$this->SensorId = $db_row['id_sensor'];
			$this->Lat = $db_row['lat'];
			$this->Lon = $db_row['lon'];
			$this->Speed = $db_row['vel'];
			$this->Dir = $db_row['dir'];
			$this->Time = $db_row['hora'];
			$this->Temperature = $db_row['temp'];
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), 1);
		}
	}
    public static function saveToDataBase( $sensor,$lat,$lon,$speed,$dir,$time,$temp ){
		try{
			global $_DATABASE;
			$query = "INSERT INTO muestras ( id_sensor,lat,lon, vel,dir,hora, temp ) VALUES ( ?,?,?, ?,?,?, ? )";
			$stmt = $_DATABASE->prepare( $query );
 			$stmt->bindParam( 1, $sensor );
            $stmt->bindParam( 2, $lat );
 			$stmt->bindParam( 3, $lon );
 			$stmt->bindParam( 4, $speed );
 			$stmt->bindParam( 5, $dir );
 			$stmt->bindParam( 6, $time );
			$stmt->bindParam( 7, $temp );
			$stmt->execute();
		} catch(PDOException $e) {
			throw new Exception("Error Processing Request: ".$e->getMessage(), 1);
			return false;
		}
        return true;
	}
    protected function loadFromDataBase2( $value ){
		global $_DATABASE;
		$query = " SELECT * FROM muestras WHERE id_muestra = ? LIMIT 1";
		$stmt = $_DATABASE->prepare( $query );
		$stmt->bindParam( 1, $value );
		$stmt->execute();

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC , PDO::FETCH_ORI_NEXT))
			$this->setPropertiesFromDbRow( $row );
		return true;
	}

	public static function loadFromDataBase( $value ){
		global $_DATABASE;
		$query = " SELECT * FROM muestras WHERE id_muestra = ? LIMIT 1";
		$stmt = $_DATABASE->prepare( $query );
		$stmt->bindParam( 1, $value );
		$stmt->execute();

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC , PDO::FETCH_ORI_NEXT))
			//$this->setPropertiesFromDbRow( $row );
			return $row;
		return true;
	}

	public static function loadMostRecent( $value ){
		global $_DATABASE;
		$query = "SELECT * FROM muestras WHERE id_sensor = ? ORDER BY hora DESC LIMIT 1";
		$stmt = $_DATABASE->prepare( $query );
		$stmt->bindParam( 1, $value );
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC , PDO::FETCH_ORI_NEXT);
		return $row;
	}

	public static function loadAllFromDataBase( $value ){
		$Samples = array();
		global $_DATABASE;
		$query = "SELECT * FROM muestras WHERE id_sensor = ? ORDER BY hora DESC LIMIT 7";
		$stmt = $_DATABASE->prepare( $query );
		$stmt->bindParam( 1, $value );
		$stmt->execute();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC , PDO::FETCH_ORI_NEXT))
			$Samples[$row["id_muestra"]] = $row;
		return $Samples;
	}
}
?>
