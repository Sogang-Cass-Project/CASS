#include </usr/include/mysql/mysql.h>
#include <string.h>
#include <stdio.h>
#include "MQTTClient.h"
#include <errno.h>
#include "stdlib.h"
#include "malloc.h"
#include "string.h"
#include <stdint.h>

//MQSQL
#define DB_HOST "127.0.0.1"
#define DB_USER "root"
#define DB_PASS "123"
#define DB_NAME "TESTDB"
#define CHOP(x) x[strlen(x) - 1] = ' '

//Mysql variables;
MYSQL       *connection=NULL, conn;
MYSQL_RES   *sql_result;
MYSQL_ROW   sql_row;
int query_stat; 


//MQTT variables;
#define ADDRESS     "tcp://163.239.22.43:1883"
#define CLIENTID    "ExampleClientSub"
#define TOPIC       "/test/123/"
#define PAYLOAD     "Hello World!"
#define QOS         1
#define TIMEOUT     10000L

//MQTT Function
volatile MQTTClient_deliveryToken deliveredtoken;
void setup(void);
void delivered(void *context, MQTTClient_deliveryToken dt);
int msgarrvd(void *context, char *topicName, int topicLen, MQTTClient_message *message);
void connlost(void *context, char *cause);
    

//MYSQL Function
void mysql_regist(void);
void mysql_unregist(void);
int main(void)
{
    MQTTClient client;
    MQTTClient_connectOptions conn_opts = MQTTClient_connectOptions_initializer;
    int rc;
    char msg[150]="CREATE TABLE DIC ( type varchar(10), name varchar(20), func1 varchar(20), func2 varchar(20), func3 varchar(20), func4 varchar(20));";
    //MQtt setup;
    setup();
    //Mysql setup;
    mysql_regist();

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

/*
    query_stat = mysql_query(connection,msg);
    if (query_stat != 0)
    {
        fprintf(stderr, "Mysql query error : %s", mysql_error(&conn));
        return 1;
    }
    
    sql_result = mysql_store_result(connection);
    mysql_free_result(sql_result);
  */


    MQTTClient_subscribe(client, TOPIC, QOS);


    while(1){
        char exitflag;
        scanf("%c",&exitflag);
        if(exitflag=='q')break;
    }
	
    //Mysql Unsetup;
    mysql_unregist();
	
    //Mqtt Unsetup;
    MQTTClient_disconnect(client, 10000);
    MQTTClient_destroy(&client);
    return rc;
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


    //sql execution
    query_stat = mysql_query(connection,msg);
    if (query_stat != 0)
    {
        fprintf(stderr, "Mysql query error : %s", mysql_error(&conn));
        return 1;
    }
    
    sql_result = mysql_store_result(connection);
    mysql_free_result(sql_result);
  
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
void setup(){
  fflush(stdout);
}
void mysql_unregist(void){

	mysql_close(connection);

}

void mysql_regist(void)
{
    mysql_init(&conn);

    connection = mysql_real_connect(&conn, DB_HOST,
                                    DB_USER, DB_PASS,
                                    DB_NAME, 3306,
                                    (char *)NULL, 0);

    if (connection == NULL)
    {
        fprintf(stderr, "Mysql connection error : %s", mysql_error(&conn));
        return ;
    }

}
