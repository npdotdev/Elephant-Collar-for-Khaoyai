#include <Arduino.h>
#include <Stream.h>
#include <TinyGPS++.h>
#include <HardwareSerial.h>
// The TinyGPS++ object
TinyGPSPlus gps;

// The serial connection to the GPS device
HardwareSerial ss(2);

#define TX2_pin  17
#define RX2_pin  16

#define RXPin (13)
#define TXPin (14)

Stream *_Serial;
HardwareSerial myserial(1);

struct location{
  float lat;
  float lng;
  String datetime;
  int reason;
};

struct _RES{
  unsigned char status;
  String data;
  String temp;
};

int cmm_state = 0;
String cmm_reason = "";
int count = 0;
uint64_t chipid;
String real_chip;
location data;
unsigned long last_time;
bool ForcePass = false;
String  str2HexStr(String strin){
  int lenuse = strin.length();
  char charBuf[lenuse * 2 - 1];
  char strBuf[lenuse * 2 - 1];
  String strout = "";
  strin.toCharArray(charBuf, lenuse * 2) ;
  for (int i = 0; i < lenuse; i++)
  {
    sprintf(strBuf, "%02X", charBuf[i]);

    if (String(strBuf) != F("00") )
    {
      strout += strBuf;
    }
  }

  return strout;
}
String Wait_module_res(long tout, String str_wait){
  unsigned long pv_ok = millis();
  unsigned long current_ok = millis();
  String input;
  unsigned char flag_out = 1;
  unsigned char res = -1;
  _RES res_;
  res_.temp = "";
  res_.data = "";

  while (flag_out)
  {
    if (_Serial->available())
    {
      input = _Serial->readStringUntil('\n');
      res_.temp += input;
      if (input.indexOf(str_wait) != -1)
      {
        res = 1;
        cmm_state = 1;
        flag_out = 0;
      }
      else if (input.indexOf(F("ERROR")) != -1)
      {
        res = 0;
        cmm_state = 0;
        flag_out = 0;
      }
    }
    current_ok = millis();
    if (current_ok - pv_ok >= tout)
    {
      flag_out = 0;
      res = 0;
      pv_ok = current_ok;
    }
  }
  res_.status = res;
  res_.data = input;
  return (res_.temp);
}
void DeepSleep(int TIME_TO_SLEEP){
  uint64_t sleeptime = UINT64_C(TIME_TO_SLEEP * 1000000);
  esp_sleep_enable_timer_wakeup(sleeptime);
  esp_deep_sleep_start();
}
void Send_command(String cmd){
  String Sim_res = "";
  cmm_reason = "";
  int fail_cunt = 0;
   _Serial->println(cmd);
  do
  {
    cmm_state = -1;
    Sim_res = Wait_module_res(150, "OK");
    fail_cunt ++;
    if (fail_cunt > 5) break;
    Serial.print(".");
    delay(300);
  } while (cmm_state != 1);

  //Sim_res.replace("OK", "");
  cmm_reason = Sim_res;
  if(cmm_state != 1)
  {
    DeepSleep(1);
  }
  //Serial.println(Sim_res);
}
void setup(){
  Serial.begin(115200);
  chipid=ESP.getEfuseMac();
  last_time = millis() + 10000;
  ss.begin(9600, SERIAL_8N1, RXPin, TXPin, false);
}
void loop()
{
  Serial.println("Wait...");
  while (ss.available() > 0)
  {
    if (gps.encode(ss.read()))
    {
      if (gps.location.isValid() or ForcePass) 
      {
        /*while(gps.date.isUpdated() == false)
        {
          Serial.println("Wait for datetime data");
        }*/
        data.reason = 2;
        data.lat = gps.location.lat();
        data.lng = gps.location.lng();
        data.datetime = String(gps.date.year()) + "-" + String(gps.date.month()) + "-" + String(gps.date.day()) + " " + String(gps.time.hour() + 7) + ":" + String(gps.time.minute())+ ":" + String(gps.time.second());
        ss.end();
        char cp[12];
        sprintf(cp,"%04X%08X",(uint16_t)(chipid>>32),(uint32_t)chipid);
        real_chip = String(cp);
        Serial.println(real_chip);
        pinMode(26, OUTPUT);  //pwrkey
        pinMode(27, OUTPUT);  //reset
        //power on module  
        digitalWrite(26, LOW);
        delay(800);
        digitalWrite(26, HIGH);
        delay(800);
        
        myserial.begin(9600, SERIAL_8N1, RX2_pin, TX2_pin,false);
        _Serial = &myserial;
      
       //delay(5000);
      
        Serial.println("START.......");

        Send_command("AT+CPIN?");
        Serial.println("Command Status : " + String(cmm_state));
        Send_command("AT+CFUN=1");
        Serial.println("Command Status : " + String(cmm_state));
        Send_command("AT*MCGDEFCONT=\"IPV4V6\",\"\"");
        Serial.println("Command Status : " + String(cmm_state));
        //Send_command("AT+CSQ");
        //Serial.println("CSQ Command Status : " + String(cmm_reason));
        Send_command("AT+CGREG=1");
        Serial.println("Command Status : " + String(cmm_state));
        Send_command("AT+CGATT=1");
        Serial.println("Command Status : " + String(cmm_state));
        Send_command("AT+CGCONTRDP");
        Serial.println("Command Status : " + String(cmm_state));

        Send_command("AT+CSQ");
        Serial.println("Command Status : " + String(cmm_state));
        
        static String Payload = "{\"lat\":\"" + String(data.lat,6) + "\",\"lng\":\""+String(data.lng,6)+"\",\"time_in\":\""+data.datetime+"\",\"device_id\":\""+real_chip+"\"}";
        /*if(ForcePass)
        {
          Serial.println("Not found Signal GPS!!!");
          Payload = "{\"reason\":\"" + String("Not Found GPS") + "\",\"device_id\":\""+real_chip+"\",\"signal_nb\":\""+cmm_reason+"\"}";
          //DeepSeep(150);
        }*/
        Payload = str2HexStr(Payload);
      
        //Serial.println("Payload :" + Payload);
        //"AT+CHTTPCREATE=\"http://35.240.151.235/api/\""
        //"AT+CHTTPCREATE=\"http://35.240.149.24/khaoyai2020/api/\""
        Send_command("AT+CHTTPCREATE=\"http://35.240.149.24/khaoyai2020/api/\"");
        Serial.println("Command Status : " + String(cmm_state));
        if(cmm_reason.indexOf("OK") != -1)
        {
          Send_command("AT+CHTTPCON=0");
          Serial.println("1Command Status : " + String(cmm_state));
          Send_command("AT+CHTTPSEND=0,1,\"/collar_rec.php\",4163636570743a202a2f2a0d0a436f6e6e656374696f6e3a204b6565702d416c6976650d0a557365722d4167656e743a2053494d434f4d5f4d4f44554c450d0a,\"application/json\"," + Payload);
          Serial.println("2Command Status : " + String(cmm_state));
        
          Send_command("AT+CHTTPDISCON=0");
          Serial.println("Command Status : " + String(cmm_state));
          Send_command("AT+CHTTPDESTROY=0");
          Serial.println("Command Status : " + String(cmm_state));
        }
        myserial.end();
      
        digitalWrite(27, LOW);
        delay(800);
        digitalWrite(27, HIGH);
      
        DeepSleep(300);       
      }
      else
      {
        if(last_time <= millis())
        {
          ForcePass = true;
        }
      }
      break;
    }
  }
  if (millis() > 5000 && gps.charsProcessed() < 10)
  {
    ss.end();
    DeepSleep(300); 
  }
  //delay(1000);
}
