
#include <Arduino.h>

// =====================================================
// SAGIPFARMER - CELLULAR ONLY
// Board: LILYGO TTGO T-SIM A7670E R2
// Soil moisture: RAW ADC READING ONLY (0-4095)
// Water level: Distance in centimeters
// =====================================================

// MODEM PINS
#define MODEM_RX       27
#define MODEM_TX       26
#define MODEM_PWRKEY    4
#define MODEM_POWERON  12

HardwareSerial modem(1);

// SENSOR PINS
#define TRIG_PIN       5
#define ECHO_PIN       18
#define MOISTURE_PIN   34

// API SETTINGS
const char API_URL[] =
  "https://sagipfarmer.isuc-ccsict.com/api/readings.php";
const char APN[] = "internet.globe.com.ph";

// PLOT IDS
const int WATER_PLOT_ID = 1;
const int SOIL_PLOT_ID  = 2;

// UPLOAD INTERVAL
const unsigned long UPLOAD_INTERVAL = 10000;
unsigned long lastUpload = 0;

bool modemReady = false;
bool cellularReady = false;

// =====================================================
// READ MODEM RESPONSE
// =====================================================
String readModem(unsigned long timeout = 3000) {
  String response = "";
  unsigned long start = millis();

  while (millis() - start < timeout) {
    while (modem.available()) {
      response += (char)modem.read();
    }
    delay(5);
  }

  return response;
}

// =====================================================
// SEND AT COMMAND
// =====================================================
String sendAT(const String &command,
              unsigned long timeout = 3000) {
  while (modem.available()) {
    modem.read();
  }

  modem.println(command);
  return readModem(timeout);
}

// =====================================================
// START MODEM
// =====================================================
bool startModem() {
  pinMode(MODEM_POWERON, OUTPUT);
  pinMode(MODEM_PWRKEY, OUTPUT);

  digitalWrite(MODEM_POWERON, HIGH);
  digitalWrite(MODEM_PWRKEY, LOW);

  modem.begin(115200, SERIAL_8N1, MODEM_RX, MODEM_TX);
  delay(1000);

  digitalWrite(MODEM_PWRKEY, HIGH);
  delay(500);
  digitalWrite(MODEM_PWRKEY, LOW);

  delay(8000);

  for (int i = 0; i < 10; i++) {
    if (sendAT("AT", 1500).indexOf("OK") >= 0) {
      sendAT("ATE0", 2000);
      return true;
    }
    delay(1000);
  }

  return false;
}

// =====================================================
// CONNECT CELLULAR DATA
// =====================================================
bool connectCellular() {
  if (sendAT("AT+CPIN?", 5000).indexOf("READY") < 0) {
    Serial.println("ERROR: SIM not ready.");
    return false;
  }

  bool registered = false;

  for (int i = 0; i < 20; i++) {
    String response = sendAT("AT+CEREG?", 2000);

    if (response.indexOf("+CEREG: 0,1") >= 0 ||
        response.indexOf("+CEREG: 0,5") >= 0 ||
        response.indexOf("+CEREG: 1,1") >= 0 ||
        response.indexOf("+CEREG: 1,5") >= 0) {
      registered = true;
      break;
    }

    delay(500);
  }

  if (!registered) {
    Serial.println("ERROR: Network registration failed.");
    return false;
  }

  sendAT(
    "AT+CGDCONT=1,\"IP\",\"" + String(APN) + "\"",
    3000
  );

  String attach = sendAT("AT+CGATT=1", 10000);

  if (attach.indexOf("ERROR") >= 0) {
    Serial.println("ERROR: Cellular attach failed.");
    return false;
  }

  String activate = sendAT("AT+CGACT=1,1", 15000);

  if (activate.indexOf("ERROR") >= 0) {
    Serial.println("ERROR: Data activation failed.");
    return false;
  }

  String ip = sendAT("AT+CGPADDR=1", 5000);

  if (ip.indexOf("+CGPADDR: 1,") < 0 ||
      ip.indexOf("+CGPADDR: 1,0.0.0.0") >= 0) {
    Serial.println("ERROR: No cellular IP address.");
    return false;
  }

  String net = sendAT("AT+NETOPEN", 10000);

  if (net.indexOf("+NETOPEN: 0") < 0 &&
      net.indexOf("Network is already opened") < 0 &&
      net.indexOf("already opened") < 0) {

    String check = sendAT("AT+CGPADDR=1", 3000);

    if (check.indexOf("+CGPADDR: 1,") < 0 ||
        check.indexOf("+CGPADDR: 1,0.0.0.0") >= 0) {
      Serial.println("ERROR: Network service unavailable.");
      return false;
    }
  }

  return true;
}

// =====================================================
// READ WATER DISTANCE (CM)
// =====================================================
float readWaterDistance() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(3);

  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  unsigned long duration = pulseIn(ECHO_PIN, HIGH, 30000);

  if (duration == 0) {
    return -1.0;
  }

  float distance = duration * 0.0343f / 2.0f;

  if (distance < 2.0f || distance > 400.0f) {
    return -1.0;
  }

  return distance;
}

// =====================================================
// READ SOIL MOISTURE RAW ADC
// ESP32 ADC RANGE: 0-4095
// NO PERCENTAGE CONVERSION
// =====================================================
int readSoilADC() {
  long total = 0;

  // Average 10 raw ADC samples to reduce noise.
  for (int i = 0; i < 10; i++) {
    total += analogRead(MOISTURE_PIN);
    delay(10);
  }

  return total / 10;
}

// =====================================================
// HTTPS POST SENSOR DATA
// =====================================================
bool sendSensorData(float waterDistance, int soilADC) {
  sendAT("AT+HTTPTERM", 2000);
  delay(300);

  // Initialize HTTP service
  String response = sendAT("AT+HTTPINIT", 5000);

  if (response.indexOf("OK") < 0) {
    Serial.println("UPLOAD ERROR: HTTPINIT failed.");
    return false;
  }

  // Configure SSL SNI
  response = sendAT(
    "AT+CSSLCFG=\"enableSNI\",0,1",
    3000
  );

  if (response.indexOf("OK") < 0) {
    Serial.println("UPLOAD ERROR: SSL configuration rejected.");
    sendAT("AT+HTTPTERM", 2000);
    return false;
  }

  // Select SSL context 0
  response = sendAT(
    "AT+HTTPPARA=\"SSLCFG\",0",
    3000
  );

  if (response.indexOf("OK") < 0) {
    Serial.println("UPLOAD ERROR: SSL context rejected.");
    sendAT("AT+HTTPTERM", 2000);
    return false;
  }

  // Set HTTPS URL
  response = sendAT(
    "AT+HTTPPARA=\"URL\",\"" + String(API_URL) + "\"",
    5000
  );

  if (response.indexOf("OK") < 0) {
    Serial.println("UPLOAD ERROR: URL rejected.");
    sendAT("AT+HTTPTERM", 2000);
    return false;
  }

  // Set JSON content type
  response = sendAT(
    "AT+HTTPPARA=\"CONTENT\",\"application/json\"",
    3000
  );

  if (response.indexOf("OK") < 0) {
    Serial.println("UPLOAD ERROR: Content type rejected.");
    sendAT("AT+HTTPTERM", 2000);
    return false;
  }

  // ===================================================
  // JSON PAYLOAD: RAW ADC VALUE, NOT PERCENTAGE
  // ===================================================
  String payload =
    "{\"readings\":["
    "{\"plot_id\":" + String(SOIL_PLOT_ID) +
    ",\"type\":\"soil_moisture\",\"value\":" +
    String(soilADC) + "},"
    "{\"plot_id\":" + String(WATER_PLOT_ID) +
    ",\"type\":\"water_level\",\"value\":" +
    String(waterDistance, 2) +
    "}]}";

  Serial.println("JSON payload:");
  Serial.println(payload);

  // Request data upload mode
  response = sendAT(
    "AT+HTTPDATA=" + String(payload.length()) + ",10000",
    5000
  );

  if (response.indexOf("DOWNLOAD") < 0) {
    Serial.println("UPLOAD ERROR: HTTPDATA rejected.");
    sendAT("AT+HTTPTERM", 2000);
    return false;
  }

  // Send JSON payload
  modem.print(payload);
  response = readModem(5000);

  if (response.indexOf("OK") < 0) {
    Serial.println("UPLOAD ERROR: JSON transfer failed.");
    sendAT("AT+HTTPTERM", 2000);
    return false;
  }

  // Start POST
  response = sendAT("AT+HTTPACTION=1", 35000);

  int actionIndex = response.indexOf("+HTTPACTION:");

  if (actionIndex < 0) {
    Serial.println("UPLOAD ERROR: No HTTPACTION result.");
    sendAT("AT+HTTPTERM", 2000);
    return false;
  }

  String result = response.substring(actionIndex);
  int comma1 = result.indexOf(',');
  int comma2 = result.indexOf(',', comma1 + 1);

  if (comma1 < 0 || comma2 < 0) {
    Serial.println("UPLOAD ERROR: Invalid HTTP result.");
    sendAT("AT+HTTPTERM", 2000);
    return false;
  }

  int statusCode =
    result.substring(comma1 + 1, comma2).toInt();

  Serial.print("HTTP status: ");
  Serial.println(statusCode);

  String apiResponse = "";

  if (statusCode >= 200 && statusCode < 300) {
    apiResponse = sendAT("AT+HTTPREAD", 7000);

    Serial.println("API response:");
    Serial.println(apiResponse);
  }

  sendAT("AT+HTTPTERM", 2000);

  if (statusCode != 200) {
    Serial.println("UPLOAD ERROR: HTTP status not 200.");
    return false;
  }

  // Check common API rejection responses.
  if (apiResponse.indexOf("Processed 0") >= 0 ||
      apiResponse.indexOf("\"success\":false") >= 0 ||
      apiResponse.indexOf("\"success\": false") >= 0 ||
      apiResponse.indexOf("\"status\":\"error\"") >= 0 ||
      apiResponse.indexOf("\"status\": \"error\"") >= 0) {
    Serial.println("UPLOAD ERROR: API rejected readings.");
    return false;
  }

  Serial.println("HTTP request completed.");
  Serial.println("Check API response to confirm processing.");

  return true;
}

// =====================================================
// SETUP
// =====================================================
void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println();
  Serial.println("=== SAGIPFARMER CELLULAR MODE ===");
  Serial.println("Soil moisture: RAW ADC");
  Serial.println("Water level: Distance in cm");

  pinMode(TRIG_PIN, OUTPUT);
  digitalWrite(TRIG_PIN, LOW);
  pinMode(ECHO_PIN, INPUT);

  analogReadResolution(12);
  analogSetPinAttenuation(MOISTURE_PIN, ADC_11db);

  Serial.println("Starting modem...");

  modemReady = startModem();

  if (!modemReady) {
    Serial.println("ERROR: Modem startup failed.");
    return;
  }

  Serial.println("Modem ready.");
  Serial.println("Connecting cellular data...");

  cellularReady = connectCellular();

  if (!cellularReady) {
    Serial.println("ERROR: Cellular connection failed.");
    return;
  }

  Serial.println("Cellular connected.");
  Serial.println("Starting sensor uploads.");
  Serial.println();
}

// =====================================================
// LOOP
// =====================================================
void loop() {
  if (!modemReady || !cellularReady) {
    delay(5000);
    return;
  }

  if (lastUpload != 0 &&
      millis() - lastUpload < UPLOAD_INTERVAL) {
    delay(20);
    return;
  }

  lastUpload = millis();

  float waterDistance = readWaterDistance();
  int soilADC = readSoilADC();

  if (waterDistance < 0) {
    Serial.println("Water sensor invalid; upload skipped.");
    return;
  }

  Serial.print("Water distance: ");
  Serial.print(waterDistance, 2);
  Serial.println(" cm");

  Serial.print("Soil moisture ADC: ");
  Serial.println(soilADC);

  Serial.println("Uploading raw sensor readings...");

  bool success = sendSensorData(waterDistance, soilADC);

  if (success) {
    Serial.println("Upload request completed.");
  } else {
    Serial.println("Upload failed or API rejected readings.");
  }

  Serial.println();
}
