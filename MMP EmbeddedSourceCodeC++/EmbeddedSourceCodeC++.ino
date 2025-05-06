/*
*****************************************************************************************************   
*****************************************************************************************************
   SG/2124101/5CS024/Embedded Program/KIOSK
   The circuit:
     * LCD RS pin to digital pin 13
     * LCD Enable pin to digital pin 12
     * LCD D4 pin to digital pin 11
     * LCD D5 pin to digital pin 10
     * LCD D6 pin to digital pin 9
     * LCD D7 pin to digital pin 8
     * LCD R/W pin to ground
     * LCD VSS pin to ground
     * LCD VCC pin to 5V
     * 330 Ohm resistor:
     * ends to +5V and ground
     * wiper to LCD VO pin (pin 3)
     * TMP36 Temperature Sensor
     * HC-SR04 Ultrasonic Sensor
     * Trigger Pin connected to digital pin 7
     * Echo Pin connected to digital pin 6 
     
    This kiosk is designed to welcome visitors and provide information about the university.
    An Arduino microcontroller acts as the brains of the system, running a C++ program that controls the kiosk’s features.
    The program displays event schedules etc. Additionally, a TMP36 temperature sensor and a ultrasonic distance sensor is integrated
    to measure and detect the distance from the kiosk and the surrounding temperature, 
    which is displayed prominently on the LCD screen to provide real-time environmental information.
    
    Date of creation: 16th April 2025
    Created by: Satish Desurkar
    Student ID: 2124101
*********************************************************************************************************************************************************************************************
*********************************************************************************************************************************************************************************************
*/
//Importing the required library
#include <LiquidCrystal.h>

// Creating & Initializing the integer variable
int sensorPin = A0;

// Creating the float type variables to be used for ultrasonic sensor
float time, distance;
const float speedOfSound = 0.0343; //cm/microseconds

//Define Push Button pins
const int PushButton1 = 4;
const int PushButton2 = 3;
const int PushButton3 = 2;

// Menu State
int menuState = 0; //0: Initial State, 1: Main Menu, 2: Schedule, 3: QR Info, 4: Course Info
bool visitorDetected = false; // To track if a visitor is within range
bool visitorSeen = false;   // To welcome back

// Define Ultrasonic Sensor Pins
const int trigPin = 7;
const int echoPin = 6;

// Define LCD pins
//Constant integer values used to assign the pin connection between a microcontroller and an LCD
const int rs = 13, en = 12, d4 = 11, d5 = 10, d6 = 9, d7 = 8;

//Creating the object lcd as an instance of class LiquidCrystal and initializing it by providing the pin numbers that connect microcontroller to the LCD.
LiquidCrystal lcd(rs, en, d4, d5, d6, d7);

// The void setup is a special function and it is called once when our microcontroller starts up or is reset.
void setup(){
lcd.begin(16, 2); // Initializing our LCD
pinMode(trigPin, OUTPUT); // Ultrasonic Sensor Trigger Pin
pinMode(echoPin, INPUT); // Ultrasonic Sensor Echo Pin
pinMode(PushButton1, INPUT_PULLUP); // Initializing our push buttons
pinMode(PushButton2, INPUT_PULLUP);
pinMode(PushButton3, INPUT_PULLUP);
Serial.begin(9600);
universityName(); // Initially display university name
}

// The void loop function is executed repeatedly over and over again.
void loop(){

// Trigger Ultrasonic Sensor
digitalWrite(trigPin, LOW);
delayMicroseconds(2);
digitalWrite(trigPin, HIGH);
delayMicroseconds(10);
digitalWrite(trigPin, LOW);

// Measure the echo pulse
time = pulseIn(echoPin, HIGH);
 
// Range checks
if (distance > 334){
   Serial.println("You are out of the distance threshold");
}
else if (distance < 2.32){
    Serial.println("You are out of the distance threshold");
}

// Calculate the distance
distance = (time * speedOfSound)/2;
Serial.print("Distance: ");
Serial.print(distance);
Serial.println(" CM");
Serial.println();
delay(250);

// Check if the visitor is within the threshold of 200cm
if(distance <= 100){
visitorDetected = true;
Serial.println("Visitor detected within the threshold distance. Show main menu.");
if(menuState == 0){
greetingsMessage(); // Welcome message displayed to visitor
menuState = 1; // Redirect to main menu after welcome
showMainMenu();
}else if(menuState == 1){
// Allow user to choose an option from the available options
manageMainMenuInput();
}else if(menuState > 1){
// Based on the option chosen by the visitor show the information
manageInfoDisplay();
}
}else{
visitorDetected = false;
menuState = 0; // Reset to initial welcome state
if(visitorSeen){
universityName(); // Display University name when no visitor in threshold distance
}
visitorSeen = false; // Reset welcome back for the next visitor
}
delay(50);
}

void universityName(){
lcd.clear();
lcd.print("University of");
lcd.setCursor(0, 1);
lcd.print("Wolverhampton");
}

void greetingsMessage(){
lcd.clear();
if(visitorSeen){
lcd.clear();
lcd.print("Welcome Back!");
}else{
lcd.print("Welcome to");
lcd.setCursor(0, 1);
lcd.print("Uni Open Day");
visitorSeen = true;
}
delay(2000);
}

void showMainMenu(){
lcd.clear();
lcd.print("Choose Option");
lcd.setCursor(0, 1);
lcd.print("1.Sch 2.QR 3.Info");
}

void manageMainMenuInput(){
// Read button states
bool button1Pressed = (digitalRead(PushButton1) == LOW);
bool button2Pressed = (digitalRead(PushButton2) == LOW);
bool button3Pressed = (digitalRead(PushButton3) == LOW);

// Manage button presses with debouncing
if(button1Pressed){
delay(200); // Debounce delay
if(digitalRead(PushButton1) == LOW){ // Check again after delay
menuState = 2;
showSchedule();
Serial.println("Push button 1 pressed, menuState = " + String(menuState));
}
}else if(button2Pressed){
delay(200); // Debounce delay
if(digitalRead(PushButton2) == LOW){ // Check again after delay
menuState = 3; // QR Code Information
showQRCode();
Serial.println("Push button 2 pressed, menuState = " + String(menuState));
}
}else if(button3Pressed){
delay(200); // Debounce delay
if(digitalRead(PushButton3) == LOW){ // Check again after delay
menuState = 4; // Website details
showWebsite();
Serial.println("Push button 3 pressed, menuState = " + String(menuState));
}
}
}

void manageInfoDisplay(){
if(menuState == 2){
showSchedule();
}else if(menuState == 3){
showQRCode();
}else if(menuState == 4){
showWebsite();
}
delay(3000); // Show Information for a while
lcd.clear();
thankYou();
delay(2000);
lcd.clear();
menuState = 0; // Go back to initial state
}

void showSchedule(){
lcd.clear();
showTemperature(); // Temperature details
delay(2000);
lcd.clear();
lcd.print("Open Day Event");
delay(2000);
lcd.clear();
lcd.print("10:00 - 11:00 AM");
lcd.setCursor(0, 1);
lcd.print("Orientation");
delay(3000);
lcd.clear();
lcd.print("Location: MC001");
delay(3000);
lcd.clear();
lcd.print("11:00 - 12:00 AM");
lcd.setCursor(0, 1);
lcd.print("Campus Tour");
delay(3000);
lcd.clear();
lcd.print("City Campus");
delay(3000);
lcd.clear();
lcd.print("12:00 - 01:00 PM");
lcd.setCursor(0, 1);
lcd.print("Lunch Break");
delay(3000);
lcd.clear();
lcd.print("Location: Cafe");
delay(3000);
lcd.clear();
lcd.print("01:00 - 02:00 PM");
lcd.setCursor(0, 1);
lcd.print("Q & A Session");
delay(3000);
lcd.clear();
lcd.print("Location: MC001");
delay(3000);
lcd.clear();
lcd.print("02:00 - 03:00 PM");
lcd.setCursor(0, 1);
lcd.print("Closing Remarks");
delay(3000);
lcd.clear();
lcd.print("Location: MC001");
delay(2000);
lcd.clear();
thankYou();
}

void showQRCode(){
lcd.clear();
lcd.print("QR Code Access");
lcd.setCursor(0, 1);
lcd.print("Visit Help Desk");
delay(3000);
}

void showWebsite(){
lcd.clear();
lcd.print("Explore Courses:");
lcd.setCursor(0, 1);
lcd.print("www.wlv.ac.uk");
delay(3000);
}

void thankYou(){
lcd.clear();
lcd.print("Thank you for");
lcd.setCursor(0, 1);
lcd.print("attending");
delay(3000);
}

void showTemperature(){
int reading = analogRead(sensorPin);
float voltage = reading * 4.68/1024.0;
float temperature = (voltage - 0.5) * 100;
lcd.setCursor(0, 0);
lcd.print("Temp: ");
lcd.print(temperature);
lcd.print(char(176));
lcd.print("C");
delay(3000);
}
