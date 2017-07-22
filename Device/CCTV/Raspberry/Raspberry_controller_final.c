#include "stdio.h"
#include "stdlib.h"
#include "string.h"
#include "malloc.h"
#include <stdint.h> //uint8_t definitions
#include "MQTTClient.h"
#include <errno.h> //error output

//wiring Pi
#include <wiringPi.h>
#include <wiringSerial.h>

#define ADDRESS     "tcp://163.239.22.43:1883" // MQtt Server
#define CLIENTID    "Client"
#define TOPIC       "/test/123/" //Topic
#define PAYLOAD     "Hello World!"
#define QOS         1
#define TIMEOUT     10000L

// Find Serial device on Raspberry with ~ls /dev/tty*
// ARDUINO_UNO "/dev/ttyACM0"
// FTDI_PROGRAMMER "/dev/ttyUSB0"
// HARDWARE_UART "/dev/ttyAMA0"
//====================================
char device[]= "/dev/ttyACM0";
//====================================
// filedescriptor
int fd;
int number;
char ch;
unsigned long baud = 9600;
unsigned long time=0;


volatile MQTTClient_deliveryToken deliveredtoken;
void setup(void);


void makecommand(char *text, char *test[],int num){

        if(num==4){ //parameter 3

                sprintf(text,"insert into DEVICE values ('%s','%s','%s','','','');",test[1],test[2],test[3]);
        }
        else if(num==5){ //4

                sprintf(text,"insert into DEVICE values ('%s','%s','%s','%s','','');",test[1],test[2],test[3],test[4]);
        }
        else if(num==6){  //5

                sprintf(text,"insert into DEVICE values ('%s','%s','%s','%s','%s','');",test[1],test[2],test[3],test[4],test[5]);
        }
        else{ //6

                sprintf(text,"insert into DEVICE values ('%s','%s','%s','%s','%s','%s');",test[1],test[2],test[3],test[4],test[5],test[6]);
        }

}
void delivered(void *context, MQTTClient_deliveryToken dt)
{
    printf("Message with token value %d delivery confirmed\n", dt);
    deliveredtoken = dt;
}

int msgarrvd(void *context, char *topicName, int topicLen, MQTTClient_message *message)
{
    int i;
    int length = message-> payloadlen;
    char* payloadptr;
	char *msg=(char*)malloc((length+1)*sizeof(char));
    printf("Message arrived %d\n", length);
    printf("     topic: %s\n", topicName);
    printf("   message: ");

    payloadptr = message->payload;
    for(i=0; i<message->payloadlen; i++)
    {
		msg[i]= *payloadptr;
        putchar(*payloadptr++);
    }
	msg[i]=0;
    putchar('\n');
	
	//strcpy(payloadptr,msg);
	printf("--%s-- %d ll\n",msg,strlen(msg));
	if(strcmp(msg,"motion start")==0){
		system("sudo service motion start");
	}
	else if(strcmp(msg,"motion end")==0){
		system("sudo service motion stop");
	}
	else if(length>=2){
		number = atoi(msg);
		printf("here is the angle : %d\n",number);
		serialPutchar (fd, number);
	}
	else{}
	
    MQTTClient_freeMessage(&message);
    MQTTClient_free(topicName);
    free(msg);
	return 1;
}

void connlost(void *context, char *cause)
{
    printf("\nConnection lost\n");
    printf("     cause: %s\n", cause);
}

int main(int argc, char* argv[])
{
    MQTTClient client;
    MQTTClient_connectOptions conn_opts = MQTTClient_connectOptions_initializer;
    MQTTClient_message pubmsg = MQTTClient_message_initializer;
    MQTTClient_deliveryToken token;
    int rc;
    char arr[100]; 
    setup();
    makecommand(arr,argv,argc);
    MQTTClient_create(&client, ADDRESS, CLIENTID,
        MQTTCLIENT_PERSISTENCE_NONE, NULL);
    conn_opts.keepAliveInterval = 20;
    conn_opts.cleansession = 1;

    MQTTClient_setCallbacks(client, NULL, connlost, msgarrvd, delivered);

    if ((rc = MQTTClient_connect(client, &conn_opts)) != MQTTCLIENT_SUCCESS)
    {
        printf("Failed to connect, return code %d\n", rc);
        exit(-1);       
    }
    printf("Subscribing to topic %s\nfor client %s using QoS%d\n\n"
           "Press Q<Enter> to quit\n\n", TOPIC, CLIENTID, QOS);
    
    pubmsg.qos = QOS;
    pubmsg.retained =0;
    
    //Sending Message
    //Regist Device Infomation.
    pubmsg.payload = arr//Make SQl to Register the Device;
    pubmsg.payloadlen = strlen(pubmsg.payload);
    MQTTClient_publishMessage(client,TOPIC,&pubmsg,&token);


    //Start Listening Message
    MQTTClient_subscribe(client, TOPIC, QOS);

	
  	while(1){
  		char exitflag;
  		scanf("%c",&exitflag);
		if(exitflag=='q')break;
	}
	
    MQTTClient_disconnect(client, 10000);
    MQTTClient_destroy(&client);
    return rc;
}
void setup(){
 
  printf("%s \n", "Raspberry Startup!");
  fflush(stdout);
 
  //get filedescriptor
  if ((fd = serialOpen (device, baud)) < 0){
    fprintf (stderr, "Unable to open serial device: %s\n", strerror (errno)) ;
    exit(1); //error
  }
 
  //setup GPIO in wiringPi mode
  if (wiringPiSetup () == -1){
    fprintf (stdout, "Unable to start wiringPi: %s\n", strerror (errno)) ;
    exit(1); //error
  }
 
}

