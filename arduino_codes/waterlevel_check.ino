
#include <Arduino.h>

// ==========================================
// ULTRASONIC SENSOR TEST
// Board: LILYGO TTGO T-SIM A7670E R2
// TRIG: GPIO 5
// ECHO: GPIO 18
// Output: Distance in centimeters
// ==========================================

#define TRIG_PIN 5
#define ECHO_PIN 18

#define SOUND_SPEED 0.0343f

void setup() {
  Serial.begin(115200);
  delay(1000);

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);

  digitalWrite(TRIG_PIN, LOW);

  Serial.println();
  Serial.println("================================");
  Serial.println(" ULTRASONIC SENSOR TEST");
  Serial.println(" TRIG: GPIO 5");
  Serial.println(" ECHO: GPIO 18");
  Serial.println("================================");
  Serial.println();
}

void loop() {
  // Send ultrasonic trigger pulse
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(3);

  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  // Measure echo pulse duration
  unsigned long duration = pulseIn(ECHO_PIN, HIGH, 30000);

  if (duration == 0) {
    Serial.println("ERROR: No echo received.");
  } else {
    float distance = duration * SOUND_SPEED / 2.0f;

    Serial.print("Water Distance: ");
    Serial.print(distance, 2);
    Serial.println(" cm");
  }

  delay(1000);
}
