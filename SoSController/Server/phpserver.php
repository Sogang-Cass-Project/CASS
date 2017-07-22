#!/usr/local/bin/php -q
<?php
error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

$address = '163.239.22.35';
$port = 12345;
if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

if (socket_listen($sock, 5) === false) {
    echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

do {
    	if (($msgsock = socket_accept($sock)) === false) {
        	echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        	break;
    	}
    	if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
        	echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
     		break 2;
    	}
    	if (!$buf = trim($buf)) {
        	continue;
    	}
    	echo "$buf\n";
	$command = explode("@",$buf);
	echo "$command[0]\n";

	if($command[0]==='1'){
		#Sending Information
		Info($msgsock,$command[1]);
	}#Giving Defalut Infomation

	else if($command[0]==='2'){
		getSubnetNetworkId($subnet_network);
		makeVm($subnet_network,$command[1]);

	}#Create Instance Information

	else if($command[0]==='3'){
		echo $command[1];
		deleteVm($command[1]);

	}#Delete Instace Information
	else{


	}
	socket_close($msgsock);
} while (true);

socket_close($sock);
function Info(&$Message_Socket,$command){

	$command = '';
	$last_line = exec('openstack server list',$retval);
	$number = count($retval);
	
	$number = (int)$number;
	$num_vm = $number-4;
	$command = "$num_vm@";

	for($i = 3 ; $i < ($number-1); $i++){

		list($bar,$id,$name,$status,$networks,$image) = explode("| ",$retval[$i]);
		$id = makeSplit($id);
		$name = makeSplit($name);
		$status = makeSplit($status);
		$networks = makeSplit($networks);
		$image = makeSplit($image);
		$ip = getFloatingIp($name);
		$Acc_Port = getAccessPort($ip)+33377;
		$url = getUrl($name);
		
		$command = "$command$name,$ip,$url,$Acc_Port|";

	}	



	socket_write($Message_Socket,$command,strlen($command));

}
function getAccessPort($ip){

	$result = explode(".",$ip);
	return $result[3];

}


function getUrl($name){
	$command = "openstack console url show $name";
	$last_line=exec($command,$retval);
	
	
	list($bar,$Field,$url) = explode("| ",$retval[4]);
	$url = makeSplit($url);
	
	echo $url;
	return $url;

}
function makeVm($subnet,$name){
#Create VM..
# command :  openstack server create --flavor m1.medium --image VM1 --nic net-id ($public-id) --security-group default ($VM_Name)

#Attach Floating ip to Instance
#openstack server add floating ip ($VM_Name) ($FloatingIP);

	$command_makingvm = "openstack server create --flavor m1.medium --image VM1 --nic net-id=".$subnet." --security-group default ".$name;
	$last_line = exec($command_makingvm,$retval);
	makeFloatingIp($floatingip);
	$command_attachingip = "openstack server add floating ip ".$name." ".$floatingip;
	$last_line = exec($command_attachingip,$retval);
	
	
	$port = getAccessPort($floatingip);
	$port = 33377+(int)$port;
	$command_makingport = "echo \"0.0.0.0 $port $floatingip 80\" >> /etc/rinetd.conf";
	$last_line =  exec($command_makingport,$retval);
	$last_line = exec("sudo service rinetd restart",$retval);
		
}
function deleteVm($name){

	#Get information about instance
	$ip = getFloatingIp($name);
	echo $ip."\n";
	echo "$name \n";;

	#Delete Instance..
	$command_deletevm = "openstack server delete ".$name;
	$last_line = exec($command_deletevm,$retval);

	#Delete Floating ip..
	$command_deleteFloatingip = "openstack floating ip delete $ip";
	$last_line = exec($command_deleteFloatingip,$retval);


}
function getFloatingIp($name){

	#Get information about instance
	$command_getInstanceInfo = "openstack server show ".$name;
	$last_line = exec($command_getInstanceInfo,$retval);
	list($bar,$Field,$value) = explode("| ",$retval[15]);
	$net = explode(" ",$value);
	echo "Floatingip : $net[0] $net[1]";
	return $net[1];
}
function makeSplit($temp){
	
	$value = explode(" ",$temp);
	return  $value[0];	
}
function makeFloatingIp(&$temp){
	
	$last_line = exec('openstack floating ip create public',$retval);
	list($bar,$field,$value) = explode("| ",$retval[6]);
	$temp = makeSplit($value);

}
function getSubnetNetworkId(&$temp){
	$last_line = exec('openstack network list',$retval);
	list($bar,$id,$name,$subnet) = explode("| ",$retval[4]);
	$temp = makeSplit($id);
	
}
function getPublicNetworkId(&$temp){
	$last_line = exec('openstack network list',$retval);
	list($bar,$id,$name,$subnet) = explode("| ",$retval[5]);
	$temp = makeSplit($id);
	
}
function filewrite($str){
	echo $str;
	$myfile = fopen('newfile.txt','w');
	if(FALSE === fwrite($myfile,$str)){

		exit("error.");
	}
	fclose($myfile);
}
function fileread(){
	$myfile = fopen("newfile.txt","r");
	while(!feof($myfile)){
		$str = fgets($myfile,999);
		echo $str;
	}
}
?>
