<?php 
//inlcude our config file
include('../../config.inc.php');

//Capture the Vote
$vote = strtolower($_GET['vote']);

// Acceptable values
$validVotes = ['yes', 'no'];

//Validate the vote
$isValid = in_array($vote, $validVotes);

//If vote is not in the array
if($isValid == false){
	$message = [
		'status' => false,
		'message' => 'Vote is invalid'
	];

	//Convert the message into JSON
	$message=json_encode($message);

	//Prepare the header
	header('Content-Type: application/json');

	//Send the message variable to js
	echo $message;

	//Terminates the current process
	exit();
}

//Get the users IP address
//For testing I am commenting this line out
// $ipaddress = $_SERVER['REMOTE_ADDR'];

//For testing, create random number to pretend we are using different IP Addresses
$ipaddress = rand() . "\n";

//Connect to database
$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//Check to make sure the voter has not voted before
$sql = "SELECT ip_address FROM vote WHERE ip_address = '$ipaddress'";

//Run the query
$result = $dbc->query($sql);

//Count the number of records returned
if($result->num_rows >= 1){

	//Prepare the message
	$message = [
		'status' => false,
		'message' => 'You cannot vote more than once'
	];

	//prepare the header
	header('Content-Type: application/json');

	echo json_encode($message);

	//Stop
	exit();
}

//Prepare the insert query
$sql = "INSERT INTO vote VALUES (NULL, '$vote', '$ipaddress')";

//Run the query
$dbc->query($sql);

if($dbc->affected_rows == 1){
	//Vote sent

	//Get the vote summary
	$sql = "SELECT SUM( (CASE WHEN vote = 'yes' THEN 1 ELSE 0 END) ) AS TotalYes, SUM( (CASE WHEN vote = 'no' THEN 1 ELSE 0 END) ) AS TotalNo FROM vote";

	//Run the query
	$result = $dbc->query($sql);

	//Convert into an associative array
	$result = $result->fetch_assoc();

	//Write the message which will be sent to js
	$message = [
		'status' => true,
		'message' => 'Thank you for your vote',
		'totalYes' => $result['TotalYes'],
		'totalNo' => $result['TotalNo']
	];

	//Prepare the header
	header('Content-Type: application/json');

	//Turn variable into json and then send to js
	echo json_encode($message);
}






