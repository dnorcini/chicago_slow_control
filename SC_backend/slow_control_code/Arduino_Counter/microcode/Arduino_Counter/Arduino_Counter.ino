/*
 
 
 */

#include <SPI.h>
#include <Ethernet.h>

// Enter a MAC address and IP address for your controller below.
// The IP address will be dependent on your local network:
byte mac[] = { 0x90, 0xA2, 0xDA, 0x10, 0x73, 0xCD };
IPAddress ip(192,168,1,85);

// Initialize the Ethernet server library
// with the IP address and port you want to use 
// (port 80 is default for HTTP):
EthernetServer server(5000);

unsigned long count_A = 0;
unsigned long count_B = 0;


void countPulseA()
{
  count_A++;
  if (count_A > 100000000)
    count_A = 0;
}


void countPulseB()
{
  count_B++;
  if (count_B > 100000000)
    count_B = 0;
}


void setup() 
{
  // start the Ethernet connection and the server:
  Ethernet.begin(mac, ip);
  server.begin();
  
  attachInterrupt(digitalPinToInterrupt(2), countPulseA, RISING);  // physical pin 2
  attachInterrupt(digitalPinToInterrupt(3), countPulseB, RISING);  // physical pin 3
}


void loop() 
{
  int channel = -1;
  // listen for incoming clients
  EthernetClient client = server.available();
  if (client) 
  {
    while (client.connected())  
    {
      if (client.available()) 
      {
        char c = client.read();
        
        if (c == '\n') 
        {
          if (channel == 0)
            client.println(count_A);
          else if (channel == 1)
            client.println(count_B);
          else
            client.println("OK");
        }
        else if (c == 'r') 
        {
          count_A = 0;
          count_B = 0;
          channel = -1;
        }
        else if (c == '0') 
        {
          channel = 0;
        }
        else if (c == '1') 
        {
          channel = 1;
        }
      }
    }
    delay(10);
    // close the connection:
    client.stop();
  }
}

