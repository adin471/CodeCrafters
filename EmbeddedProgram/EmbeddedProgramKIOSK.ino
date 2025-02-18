/*
*****************************************************************************************************   
*****************************************************************************************************
   SG/2124101/5CS024/Embedded Program/KIOSK
   The circuit:
     * LCD RS pin to digital pin 10
     * LCD Enable pin to digital pin 09
     * LCD D4 pin to digital pin 5
     * LCD D5 pin to digital pin 4
     * LCD D6 pin to digital pin 3
     * LCD D7 pin to digital pin 2
     * LCD R/W pin to ground
     * LCD VSS pin to ground
     * LCD VCC pin to 5V
     * 330 Ohm resistor:
     * ends to +5V and ground
     * wiper to LCD VO pin (pin 3)
     * TMP36 Temperature Sensor
     * HC-SR04 Ultrasonic Sensor
     * Trigger Pin connected to digital pin 12
     * Echo Pin connected to digital pin 11 
     
    This kiosk is designed to welcome visitors and provide information about the university.
    An Arduino microcontroller acts as the brains of the system, running a C++ program that controls the kiosk’s features.
    The program displays event schedules etc. Additionally, a TMP36 temperature sensor and a ultrasonic distance sensor is integrated
    to measure and detect the distance from the kiosk and the surrounding temperature, 
    which is displayed prominently on the LCD screen to provide real-time environmental information.
    
    Date of creation: 14th February 2025
    Created by: Satish Desurkar
    Student ID: 2124101
*********************************************************************************************************************************************************************************************
*********************************************************************************************************************************************************************************************
*/

	//Importing the required library
    #include <LiquidCrystal.h>

	// Creating & Initializing the integer variable
    int sensorPin = 0; 
	// Creating the float type variables to be used for ultrasonic sensor
    float time, distance;

    
    //Define Ultrasonic Sensor Pins
    const int trigPin = 12, echoPin = 11;

	// Define LCD pins
    //Constant integer values used to assign the pin connection between a microcontroller and an LCD
    const int rs = 10, en = 9, d4 = 5, d5 = 4, d6 = 3, d7 = 2;
    /*
    Creating the object lcd as an instance of class LiquidCrystal and initializing it by providing the pin numbers that connect microcontroller to the LCD.
    */
    LiquidCrystal lcd(rs, en, d4, d5, d6, d7);

	//void setup() is a special function and it is called once when our microcontroller starts up or is reset.
    void setup() {
      lcd.begin(16,2); // Initialize the LCD
      pinMode(trigPin, OUTPUT); // Ultrasonic Sensor Trigger Pin
      pinMode(echoPin, INPUT); // Ultrasonic Sensor Echo Pin
      Serial.begin(9600);      
    }

	// void loop() is a function which is executed repeatedly, over and over again.
	void loop() {
      // Ultrasonic Sensor
      digitalWrite(trigPin, LOW);
      delayMicroseconds(2);
      digitalWrite(trigPin, HIGH);
      delayMicroseconds(10);
      digitalWrite(trigPin, LOW);
      
      time = pulseIn(echoPin, HIGH);
      distance = (time*.0343)/2;
      
      // For Serial Monitor
      Serial.print("Distance:CM ");
      Serial.print(distance);      

      
      if(distance<100){
      
      // Display schedule
      lcd.print("Welcome to");
      lcd.setCursor(0,1); // Set cursor to the second row
      lcd.print("Open day!");
      delay(2000); // Display welcome message for 2 seconds
      lcd.clear(); // Tells the LCD screen to erase all the characters that are currently displayed and move the cursor back to the home position.

      lcd.setCursor(0,0); // Set cursor to the first row
      lcd.print("10:00 - 11:00 AM");
      lcd.setCursor(0,1);  // Set cursor to the second row
      lcd.print("Orientation");
      delay(5000); // Display message for 5 seconds
      lcd.clear();
      lcd.setCursor(0,0);
      lcd.print("Location: MC001");
      delay(5000); // Display message for 5 seconds
      lcd.clear();

      lcd.setCursor(0,0);
      lcd.print("11:00 - 12:00 AM");
      lcd.setCursor(0,1); // Set cursor to the second row
      lcd.print("Campus Tour");
      delay(5000); // Display message for 5 seconds
      lcd.clear();
  
      lcd.setCursor(0,0);
      lcd.print("12:00 - 01:00 PM");
      lcd.setCursor(0,1); // Set cursor to the second row
      lcd.print("Lunch Break");
      delay(5000); // Display message for 5 seconds
      lcd.clear();
      lcd.setCursor(0,0);
      lcd.print("Cafeteria");
      delay(5000); // Display message for 5 seconds
      lcd.clear();

  
      lcd.setCursor(0,0);
      lcd.print("01:00 - 02:00 PM");
      lcd.setCursor(0,1); // Set cursor to the second row
      lcd.print("Q&A Session");
      delay(5000); // Display message for 5 seconds
      lcd.clear();
      lcd.setCursor(0,0);
      lcd.print("Location: MC001");
      delay(5000); // Display message for 5 seconds
      lcd.clear();
  
      lcd.setCursor(0,0);
      lcd.print("02:00 - 03:00 PM");
      lcd.setCursor(0,1); // Set cursor to the second row
      lcd.print("Closing Remarks");
      delay(5000); // Display message for 5 seconds
      lcd.clear();
      lcd.setCursor(0,0);
      lcd.print("Location: MC001");
      delay(5000); // Display message for 5 seconds
      lcd.clear();


      /*     
      Summary: The above code reads the voltage from a temperature sensor,  converts it to a digital value,
      and then uses a specific formula to calculate the temperature in Celcisus.
      It then displays this temperature on an LCD screen and prints it to the Serial Monitor.
      The 4.68 value in the code is a calibration factor, which is used to improve the accuracy of the reading.
      Also the formula to calculate the temperature is specific to the sensor being used [TMP36].
      */
      
      // Implementation of Temperature Sensor [TMP36]
      int reading = analogRead(sensorPin);
      /*
      The above line of code reads the analog value from a sensor connected to a specific pin 
      on our microcontroller and stores that value in an integer variable called reading.
      Our temperature sensor produce an analog output, meaning the signal varies continuously within a range of voltages.
      The Arduino can’t directly understand these changing voltages.
      analogRead() converts the voltage into a digital number that the Arduino can work with.
      */
      
      float voltage = reading * 4.68;
      /*
      The above line of code (reading * 4.68) converts the digital reading back into a voltage.
      The magic number 4.68 is a calibration factor .
      */
      voltage /= 1024.0;
      /*
      The above line of code completes the voltage calculation.
      It’s equivalent to voltage = voltage /1024.0
      We divide by 1024.0 (and not 1023) because the analog reading can go up to  1023, representing the full range of the voltage. This division scales the reading value (0-1023) to the actual voltage (approximately 0 – 5V).
      */
      
      float temperatureC = (voltage - 0.5) * 100;
      /*
      We created a float variable (temperatureC) to store the calculated temperature  value in Celsius.
      The formula (voltage – 0.5) * 100 is use to convert voltage into Celsius (The -0.5 and the * 100 are specific calibration values for the sensor TMP36)
      */
  
      lcd.clear(); // Clears the LCD screen
      Serial.print(temperatureC); // Prints temperature to the Serial Monitor
      Serial.println(" degrees C");
      lcd.setCursor(0,0); // Sets the cursor to the top left corner of the LCD
      lcd.print("Temperature"); // Prints “Temperature” on LCD
      lcd.setCursor(0,1); // Sets cursor to the second row
      lcd.print("degrees C"); // Prints “degrees C” on LCD
      lcd.setCursor(11,1); // Sets the cursor to the 12th position on the second line and prints “degrees C”
      lcd.print(temperatureC); // Prints the calculated temperature
      delay(5000); // Pauses the program for 5 seconds so you have time to read the temperature on the LCD.
      lcd.clear(); // Clears the LCD screen
      }
      else if (distance > 100) {
      
      //For LCD Display
      lcd.setCursor(0,0);
      lcd.print("University of");
      lcd.setCursor(0,1);
      lcd.print("Wolverhampton");
      }


  }

