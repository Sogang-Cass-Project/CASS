<html>
<body>
<?php
function test($name){
error_reporting(E_ALL);


/* Get the port for the WWW service. */
/*$service_port = getservbyname('www', 'tcp');*/
$service_port = 12345;

/* Get the IP address for the target host. */
/*$address = gethostbyname('www.example.com');*/
$address = '163.239.22.35';

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
} else {
}

$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
}

$in = "3@".$name;
$in .="\r";
socket_write($socket, $in, strlen($in));
$out = '';
}
$input = $_POST["name"];
if($input === ''){

}
else{
	test($input);
}
echo "<script>
document.location.href='final.php';
</script>";
?>

