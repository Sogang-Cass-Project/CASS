<html>
<body>
<form action="create_vm.php" method="post">
Creste VM Name : <input type = "text" name ="name"><br>
<input type="submit">
</form>

<form action="delete_vm.php" method="post">
Delete VM Name : <input type = "text" name ="name"><br>
<input type="submit">
</form>

<?php
function getinfo(){
	error_reporting(E_ALL);
	/* Get the port for the WWW service. */
	$service_port = 12345;
	/* Get the IP address for the target host. */
	$address = '163.239.22.35';
	/* Create a TCP/IP socket. */
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket === false) {
		echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
	} 
	else {
	}
	$result = socket_connect($socket, $address, $service_port);
	if ($result === false) {
		echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
	} 
	else {
	}

	$in = "1@test\r";
	socket_write($socket, $in, strlen($in));
	$out = '';
	$out = socket_read($socket,2048);
	$info='';
	$num = getNum($out,$info);

	echo "<center><table border ='1'><br />";
	$NameAndIp=getBarSplit($info);
	for($i=0 ; $i<$num ; $i++){
		$result = getName($NameAndIp[$i]);
		$VM_Name = $result[0];
		$ip = $result[1];
		$url = $result[2];
		$port = $result[3];
		echo "<tr>
		<td><center>$VM_Name</center></td>
		<td><center>$ip</center></td>
		<td>
			
			<form action=\"http://163.239.22.35:$port/admin\">
			<input type=\"submit\"/ value=\"Access To Web\">
			</form>
		
		</td>
		<td>
			<input type=\"button\" value=\"Access To VM\" onclick=\"location.href='$url'\";>
		</td>
		</tr>";
	}
	echo "</table></center>";
}
function getBarSplit($command){
	$result = explode("|",$command);

	return $result;
}
function getName($info){
	$result= explode(",",$info);
	return $result;
}
function getNum($command,&$temp){

	
	list($num,$left) = explode("@",$command);
	$temp = $left;
	return $num;

}
getinfo();
?>
</body>

</html>





