#include <WaspSensorAgr.cpp>
#include <WaspSensorAgr.h>
#include <WaspPWR.cpp>
#include <WaspPWR.h>
#include <WaspRTC.cpp>
#include <WaspRTC.h>
#define THRESHOLD 90.0

#include "WProgram.h"
void setup();
void loop();
void sleepX();
void takeMean();
void WaspSense();
float value_anemometer = 0;
float windMean = 0;
int count = 0;
float value_vane = 0;
float value_vane_max = 0;
char* lat;
char* lon;
int sensorID = 2;

//Sant location
char* FIXLAT =  "10.99130388";
char* FIXLON = "-74.82096638";
  
void setup(){
  RTC.ON();
  if(sensorID == 1){
    //Sant
    FIXLAT =  "10.99130388";
    FIXLON = "-74.82096638";
  }else{
    //H
    FIXLAT =  "11.00699916";
    FIXLON = "-74.80561138";  
  }
  
  USB.begin();
  USB.print("Battery Level: ");
  USB.print(PWR.getBatteryLevel(),DEC);
  USB.println("%");
  
  //turn agriculture sensor on and initialize anemometer with wind vane 
  SensorAgr.setBoardMode(SENS_ON);
  SensorAgr.setSensorMode(SENS_ON, SENS_AGR_ANEMOMETER);
  delay(100);
  SensorAgr.setAnemometerThreshold(THRESHOLD);
  SensorAgr.setSensorMode(SENS_ON, SENS_AGR_VANE);
  delay(100);
}

void loop(){
  RTC.setTime("17:05:21:01:00:00:00");
  USB.print("startTime = ");
  USB.println(RTC.getTime());
  delay(8000);
  windMean = 0;
  count = 0;
  sleepX();
  USB.print("count: ");  
  USB.println(count);
  USB.print("mean: ");
  char mean_str[9];
  Utils.float2String(windMean, mean_str, 2);
  USB.println(mean_str);
  WaspSense();
  USB.print("endTime = ");
  USB.println(RTC.getTime());
  USB.println( "#####################################" ); 
}

void sleepX(){
  int i = 0;
  while(i<9){
    int j = 0;
    while (j < 6){
      //PWR.sleep(WTD_8S, ALL_OFF);
      delay(8000);
      delay(2000);
      j = j+1;
    }
    takeMean();  
    i = i+1;
  }
}  
void takeMean(){
  //Getting values from sensors
  value_anemometer = SensorAgr.readValue(SENS_AGR_ANEMOMETER);
  windMean = windMean + value_anemometer;
  count = count + 1;
  USB.print(count);
  USB.print(": sample taken = ");
  USB.println(value_anemometer);
}

void WaspSense(){
  USB.begin();
  USB.println("Measurements:");
  delay(8000);
  //#############################   GPS     #############################
  //use alternate LatLon data if no GPS coverage
  lat = FIXLAT;
  lon = FIXLON;
  GPS.ON();
  int8_t tries = 5;
  while(tries < 5 ){
    //check GPS connectivity
    if(GPS.check()){
      tries = 10;
      GPS.getPosition();  
      lat = GPS.latitude;
      lon = GPS.longitude;
    }else{
      tries = tries + 1;
    }
    delay(1000);
  }
  USB.print("Latitude: ");
  USB.println(lat);
  USB.print("Longitude: ");
  USB.println(lon);        
  
  //#############################   ANEM    #############################
  
  //Getting values from sensors
  value_anemometer = SensorAgr.readValue(SENS_AGR_ANEMOMETER);
  value_vane = SensorAgr.readValue(SENS_AGR_VANE);
  windMean = (windMean + value_anemometer) / (count + 1);
  
  USB.print("wind speed: ");
  USB.println(value_anemometer);
  
  USB.print("wind mean: ");
  USB.println(windMean);
  
  USB.print("wind direction: ");
  
  char* dir;  
  //parse volt values from wind vane to numbers
  int vdir = SensorAgr.vane_direction;
  //parse number to cardinal direction string
  switch(vdir){ 
    case 0:
      dir = "N";
      break;
    case 1:
      dir = "NNE";
      break;
    case 2:
      dir = "NE";
      break;
    case 4:
      dir = "ENE";
      break;
    case 8:
      dir = "E";
      break;
    case 16:
      dir = "ESE";
      break;
    case 32:
      dir = "SE";
      break;
    case 64:
      dir = "SSE";
      break;
    case 128:
      dir = "S";
      break;
    case 256:
      dir = "SSW";
      break;
    case 512:
      dir = "SW";
      break;
    case 1024:
      dir = "WSW";
      break;
    case 2048:
      dir = "W";
      break;
    case 4096:
      dir = "WNW";
      break;
    case 8192:
      dir = "NW";
      break;
    case 16384:
      dir = "NNW";
      break;
  }
  USB.println(dir);
  
  //#############################   GPRS    #############################
  //turns on GPRS
  GPRS.ON();
  //check GPRS coverage
  while(!GPRS.check()){ 
    USB.println("GPRS off network");
  }
  // set default configurations
  GPRS.setInfoIncomingCall();
  GPRS.setInfoIncomingSMS();
  GPRS.setTextModeSMS();
  delay(1000);
  if(GPRS.configureGPRS()){
    USB.println("Configured OK");
  }
  delay(7000);
  int8_t i = 0;
  //create a socket to server adress on http port
  if(GPRS.createSocket("54.91.250.124","80",GPRS_CLIENT)){
    USB.print("Session Number: ");
    while( GPRS.socket_ID[i]!='\r' ){
      USB.print(GPRS.socket_ID[i]-'0',DEC);
      i++;
    }
    i=0;
    USB.println();
  }
  else USB.println("Error opening the socket");
  delay(8000);
  USB.println("sending data");
  // Get data from socket
  
  char raw_params[70];
  char command[250];
  char anem_str[9];
  Utils.float2String(windMean, anem_str, 2);
  USB.println(anem_str);  
  char sID = '1';
  if(sensorID!=1){
    sID='2';
  }
  //urlencoding sensor data to GET request payload
  sprintf(raw_params,"%c&lat=%s&lon=%s&spd=%s&dir=%s&bat=%d&temp=%s",sID,lat,lon,anem_str,dir,PWR.getBatteryLevel(),"31");
  //USB.println(raw_params);
  //USB.println();  
  //raw GET request building 
  USB.println("params: ");
  sprintf(command,"%s%s%s%", "GET http://ec2-54-91-250-124.compute-1.amazonaws.com/API/windapi.php?type=sample&sensor=",raw_params," HTTP/1.0\r\nHost: ec2-54-91-250-124.compute-1.amazonaws.com\r\nConnection: close\r\n");
  USB.println(command);  
  USB.println("preparing socket transfering");  
  if(GPRS.sendData(command,GPRS.socket_ID)){ 
    USB.println("Data sent");  
  } 
  delay(2000);
  int8_t n=0;
  while(!n){
    n=GPRS.readData(GPRS.socket_ID,"90");
  }
  //print response
  USB.println("Response[0-99]: ");
  USB.println(GPRS.data_URL);  
  // Close socket
  if(GPRS.closeSocket(GPRS.socket_ID)) USB.println("Socket closed");
  windMean = 0;
  count = 0;
}



int main(void)
{
	init();

	setup();
    
	for (;;)
		loop();
        
	return 0;
}

