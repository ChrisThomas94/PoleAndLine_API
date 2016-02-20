<?php
 
/**
 * File to handle all API requests
 * Accepts GET and POST
 * 
 * Each request will be identified by TAG
 * Response will be JSON data
 
  /**
 * check for POST request 
 */
// Request type is Register new user
// include DB_function
require_once 'include/DB_Functions.php';
$db = new DB_Functions();

$inputJSON = file_get_contents('php://input');
echo $inputJSON;

$filename="test.txt";
file_put_contents($filename, $inputJSON);

$_POST = json_decode(file_get_contents('php://input'), true);


$name = (isset($_POST['name']) ? $_POST['name'] : null);
$email = (isset($_POST['email']) ? $_POST['email'] : null);
$password = (isset($_POST['password']) ? $_POST['password'] : null);

  
//login
// check for user
$user = $db->getUserByEmailAndPassword($email, $password);
if ($user != false) {
	// user found
	$response["error"] = FALSE;
	$response["uid"] = $user["unique_id"];
	$response["user"]["name"] = $user["name"];
	$response["user"]["email"] = $user["email"];
	$response["user"]["created_at"] = $user["created_at"];
	$response["user"]["updated_at"] = $user["updated_at"];
	echo json_encode($response);
} else {
	// user not found
	// echo json with error = 1
	$response["error"] = TRUE;
	$response["error_msg"] = "Incorrect email or password!";
	echo json_encode($response);
}
//end login
  
// check if user is already existed
if ($db->isUserExisted($email)) {
	// user is already existed - error response
	$response["error"] = TRUE;
	$response["error_msg"] = "User already existed";
	echo json_encode($response);
} else {
	// store user
	$user = $db->storeUser($name, $email, $password);
	if ($user) {
		// user stored successfully
		$response["error"] = FALSE;
		$response["uid"] = $user["unique_id"];
		$response["user"]["name"] = $user["name"];
		$response["user"]["email"] = $user["email"];
		$response["user"]["created_at"] = $user["created_at"];
		$response["user"]["updated_at"] = $user["updated_at"];
		echo json_encode($response);
	} else {
		// user failed to store
		$response["error"] = TRUE;
		$response["error_msg"] = "Error occured in Registartion";
		echo json_encode($response);
	}
}

?>