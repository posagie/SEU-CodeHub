<?php
function dbConnect(){
	require_once "../adodb5/adodb.inc.php";
	$host = "localhost";
	$database = "posagiec_Codehub";
	$user = "posagiec_SEUser";
	$pwd = "codehub810";
	$db = ADONewConnection("mysqli");
	$db -> Connect($host, $user, $pwd, $database);
	return $db;
	// Check connection
    if($db === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
}
?>