
#include <Arduino.h>

// ==========================================
// SOIL MOISTURE SENSOR TEST
// Board: LILYGO TTGO T-SIM A7670E R2
// Sensor analog output: GPIO 34
// Output: Raw ADC reading (0-4095)
// ==========================================

#define MOISTURE_PIN 34

void setup() {
  Serial.begin(115200);
  delay(1000);

  analogReadResolution(12);
  analogSetPinAttenuation(MOISTURE_PIN, ADC_11db);

  Serial.println();
  Serial.println("================================");
  Serial.println(" SOIL MOISTURE SENSOR TEST");
  Serial.println(" Board: ESP32");
  Serial.println(" Sensor pin: GPIO 34");
  Serial.println(" Output: RAW ADC ONLY");
  Serial.println("================================");
  Serial.println();
}

void loop() {
  long total = 0;
  const int samples = 10;

  // Read 10 samples to reduce noise
  for (int i = 0; i < samples; i++) {
    total += analogRead(MOISTURE_PIN);
    delay(10);
  }

  int rawADC = total / samples;

  Serial.print("Soil Moisture Raw ADC: ");
  Serial.println(rawADC);

  delay(1000);
}
