<html>
<body>


<?php

$servername = "localhost";
$username = "root";
$password = "123";
$dbname = "TESTDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT *  FROM DEVICE";
$result = $conn->query($sql);

echo "<center><table border = '1' ><br />";
echo "<tr>
	<td><center>TYPE</center></td>
	<td><center>NAME</center></td>
	<td><center>function</center></td>
	<td><center>function2</center></td>
	<td><center>function3</center></td>
	<td><center>function4</center></td>";

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {

        echo "<tr>
   		<td><center>". $row["type"]. "</center></td>".
		"<td><center> " . $row["name"]. "</center></td>".
		"<td><center> " . $row["func1"]. "</center></td>".
		"<td><center> " . $row["func2"]. "</center></td>".
		"<td><center> " . $row["func3"]. "</center></td>".
		"<td><center> " . $row["func4"]. "</center></td>".
		"</tr>";
    }
} else {
    echo "0 results";
}
echo "</table></center>";

$conn->close();
?>

</body>
</html>

