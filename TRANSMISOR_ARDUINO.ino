#include <SPI.h>
#include <nRF24L01.h>
#include <RF24.h>

RF24 radio(9,10); // CE, CSN

const byte direccion[6] = "00001";

void setup() {

  Serial.begin(9600);

  if (!radio.begin()) {
    Serial.println("NRF24L01 no detectado");
    while (1);
  }

  radio.setChannel(108);

  // MISMAS configuraciones que el ESP32
  radio.setDataRate(RF24_250KBPS);
  radio.setPALevel(RF24_PA_LOW);

  radio.openWritingPipe(direccion);

  radio.stopListening();

  Serial.println("Transmisor listo...");
}

void loop() {

  char texto[32] = "Hola ESP";

  bool enviado = radio.write(&texto, sizeof(texto));

  if (enviado) {
    Serial.println("Mensaje enviado: Hola ESP");
  } 
  else {
    Serial.println("Error al enviar");
  }

  delay(1000);
}