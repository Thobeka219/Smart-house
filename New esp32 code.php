#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <WiFi.h>
#include <WebServer.h>

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

const char* ssid = "Elnita-Students-NEW";
const char* password = "Elnita@2024";

WebServer server(80);

const int ledPin1 = 2;
const int ledPin2 = 4;
const int ledPin3 = 16;
const int motionSensorPin = 19;
bool lastMotionState = LOW;

const char* htmlPage = R"rawliteral(
<!DOCTYPE HTML><html>
<head>
  <title>ESP32 LED Control</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>ESP32 LED Control</h1>
  <p>LED 1:</p>
  <button onclick="toggleLED('on1')">ON</button>
  <button onclick="toggleLED('off1')">OFF</button>
  <p>LED 2:</p>
  <button onclick="toggleLED('on2')">ON</button>
  <button onclick="toggleLED('off2')">OFF</button>
  <p>LED 3:</p>
  <button onclick="toggleLED('on3')">ON</button>
  <button onclick="toggleLED('off3')">OFF</button>
  <p id="motionStatus">Motion: Not Detected</p>
  <script>
    function toggleLED(state) {
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "/" + state, true);
      xhr.send();
    }
    
    setInterval(() => {
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "/motionStatus", true);
      xhr.onload = function() {
        if (xhr.status === 200) {
          document.getElementById("motionStatus").innerText = "Motion: " + xhr.responseText;
        }
      };
      xhr.send();
    }, 1000);
  </script>
</body>
</html>
)rawliteral";

void handleRoot() {
  server.send(200, "text/html", htmlPage);
}

void handleLEDOn1() {
  digitalWrite(ledPin1, HIGH);
  server.send(200, "text/plain", "LED 1 is ON");
}

void handleLEDOff1() {
  digitalWrite(ledPin1, LOW);
  server.send(200, "text/plain", "LED 1 is OFF");
}

void handleLEDOn2() {
  digitalWrite(ledPin2, HIGH);
  server.send(200, "text/plain", "LED 2 is ON");
}

void handleLEDOff2() {
  digitalWrite(ledPin2, LOW);
  server.send(200, "text/plain", "LED 2 is OFF");
}

void handleLEDOn3() {
  digitalWrite(ledPin3, HIGH);
  server.send(200, "text/plain", "LED 3 is ON");
}

void handleLEDOff3() {
  digitalWrite(ledPin3, LOW);
  server.send(200, "text/plain", "LED 3 is OFF");
}

void handleMotionStatus() {
  int motionDetected = digitalRead(motionSensorPin);
  if (motionDetected) {
    server.send(200, "text/plain", "Detected");
  } else {
    server.send(200, "text/plain", "Not Detected");
  }
}

void setup() {
  Serial.begin(115200);

  // Initialize OLED display
  if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) { // Address 0x3C for 128x64
    Serial.println(F("SSD1306 allocation failed"));
    for(;;);
  }
  display.display();
  delay(2000); // Pause for 2 seconds

  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 0);
  display.println("Initializing...");
  display.display();

  pinMode(ledPin1, OUTPUT);
  digitalWrite(ledPin1, LOW);

  pinMode(ledPin2, OUTPUT);
  digitalWrite(ledPin2, LOW);

  pinMode(ledPin3, OUTPUT);
  digitalWrite(ledPin3, LOW);

  pinMode(motionSensorPin, INPUT);

  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("");
  Serial.println("WiFi connected");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  server.on("/", handleRoot);
  server.on("/on1", handleLEDOn1);
  server.on("/off1", handleLEDOff1);
  server.on("/on2", handleLEDOn2);
  server.on("/off2", handleLEDOff2);
  server.on("/on3", handleLEDOn3);
  server.on("/off3", handleLEDOff3);
  server.on("/motionStatus", handleMotionStatus);
  server.begin();
  Serial.println("HTTP server started");

  display.clearDisplay();
  display.setCursor(0, 0);
  display.println("System Ready");
  display.display();
}

void loop() {
  server.handleClient();

  // Read motion sensor
  int motionDetected = digitalRead(motionSensorPin);
  if (motionDetected != lastMotionState) {
    lastMotionState = motionDetected;
    display.clearDisplay();
    display.setCursor(0, 0);
    if (motionDetected) {
      display.println("Motion: Detected");
    } else {
      display.println("Motion: Not Detected");
    }
    display.display();
  }
}
