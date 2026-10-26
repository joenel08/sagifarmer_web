
#include <Arduino.h>

// ==============================================
// LILYGO TTGO T-SIM A7670E R2
// CELLULAR SIGNAL TEST
// SIM: Globe Philippines
// Signal rating: 1-10
// ==============================================

#define MODEM_RX       27
#define MODEM_TX       26
#define MODEM_PWRKEY    4
#define MODEM_POWERON  12

HardwareSerial modem(1);

// Read modem response
String readModem(unsigned long timeout = 2000) {
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

// Send AT command
String sendAT(const char *command,
              unsigned long timeout = 2000) {
  while (modem.available()) {
    modem.read();
  }

  modem.println(command);
  return readModem(timeout);
}

// Start modem
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

  Serial.println("Waiting for modem startup...");
  delay(8000);

  for (int i = 0; i < 10; i++) {
    String response = sendAT("AT", 1500);

    if (response.indexOf("OK") >= 0) {
      sendAT("ATE0", 1000);
      return true;
    }

    delay(1000);
  }

  return false;
}

// Get signal strength
void checkSignal() {
  String response = sendAT("AT+CSQ", 2000);

  Serial.println("--------------------------------");

  if (response.indexOf("+CSQ:") < 0) {
    Serial.println("ERROR: Cannot read signal strength.");
    Serial.println(response);
    return;
  }

  int start = response.indexOf("+CSQ:") + 5;
  int comma = response.indexOf(',', start);

  if (comma < 0) {
    Serial.println("ERROR: Invalid CSQ response.");
    return;
  }

  int csq = response.substring(start, comma).toInt();

  Serial.print("Raw CSQ: ");
  Serial.println(csq);

  if (csq == 99 || csq < 0 || csq > 31) {
    Serial.println("Signal: UNKNOWN");
    Serial.println("Rating: N/A");
    Serial.println("Check SIM, antenna, and network coverage.");
    return;
  }

  // Convert CSQ 0-31 to rating 1-10
  int rating = 1 + (csq * 9 / 31);

  // Approximate RSSI in dBm
  int rssi = -113 + (csq * 2);

  Serial.print("Approx. RSSI: ");
  Serial.print(rssi);
  Serial.println(" dBm");

  Serial.print("Signal rating: ");
  Serial.print(rating);
  Serial.println("/10");

  if (rating <= 2) {
    Serial.println("Quality: VERY WEAK");
  } else if (rating <= 4) {
    Serial.println("Quality: WEAK");
  } else if (rating <= 6) {
    Serial.println("Quality: FAIR");
  } else if (rating <= 8) {
    Serial.println("Quality: GOOD");
  } else {
    Serial.println("Quality: EXCELLENT");
  }

  // Check network registration
  String network = sendAT("AT+CEREG?", 2000);
  Serial.print("Network registration: ");
  Serial.println(network);
}

void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println();
  Serial.println("================================");
  Serial.println(" A7670E SIGNAL STRENGTH TEST");
  Serial.println(" SIM: Globe");
  Serial.println(" Rating: 1-10");
  Serial.println("================================");

  if (!startModem()) {
    Serial.println("ERROR: Modem not responding.");
    Serial.println("Check power, battery, and UART wiring.");
    return;
  }

  Serial.println("Modem ready.");
}

void loop() {
  checkSignal();
  delay(5000);
}
