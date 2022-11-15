<?php
// login.php
require_once "database.php";
require_once "jsonAlert.php";
header("Content-Type: application/json"); 

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

//Variables can be accessed as such:
$username = $json_obj['reg_username'];
$password = $json_obj['reg_password'];

$hashpw = password_hash($password, PASSWORD_DEFAULT);
$alert = "Known Error";


if (!preg_match('/^[\w_\-]+$/', $username)) {
    $alert = "Incorrect Username:" . $username . " has special characters";
    jsonAlert(false, $alert);
    exit;
}

$checkname = $mysqli->prepare("select * from user where username = ?");
if (!$checkname) {
    $alert = "User Query Prep Failed: %s\n" . $mysqli->error;
    jsonAlert(false, $alert);
    exit;

}
$checkname->bind_param('s', $username);
$checkname->execute();
$result = $checkname->get_result();
$row = mysqli_num_rows($result);
if ($row) {
    $alert = "Incorrect Username: This UserName has already been used";
    jsonAlert(false, $alert);
    exit;
}
$checkname->close();

$stmt = $mysqli->prepare("insert into user (username, pw) values (?, ?)");
if (!$stmt) {
    $alert = "Query Prep Failed: %s\n" . $mysqli->error;
    jsonAlert(false, $alert);
}

$stmt->bind_param('ss', $username, $hashpw);
$stmt->execute();

if (!$stmt->errno) {
    echo json_encode(array(
        "success" => true,
        "message" => "Success. User has been registered",
    ));
    exit;
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "ERROR: Incorrect Username or Password",
    ));
    exit;
}
$stmt->close();
