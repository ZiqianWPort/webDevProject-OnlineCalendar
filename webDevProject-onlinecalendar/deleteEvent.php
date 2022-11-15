<?php
// login.php
require_once "database.php";
require_once "jsonAlert.php";
ini_set("session.cookie_httponly", 1);
session_start();
header("Content-Type: application/json");

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

$eventid = $json_obj['event_id'];
$token = $json_obj['token'];
$userid = $json_obj['user_id'];

$alert = "Known Error";
if (isset($_SESSION['token'])) {
    if (!hash_equals($token, $_SESSION['token'])) {
        $alert = "Error: Token - Request Forgery";
        jsonAlert(false, $alert);
        exit;
    }
    if ($userid != $_SESSION['user_id']) {
        $alert = "ERROR: Session Hijack" . $_SESSION['user_id'];
        jsonAlert(false, $alert);
        exit;
    }
} else {
    $alert = "Please login first!";
    jsonAlert(false, $alert);
    exit;
}

$stmt = $mysqli->prepare("delete from event where eid = ?");
if (!$stmt) {
    $alert = "Query Prep Failed: %s\n" . $mysqli->error;
    jsonAlert(false, $alert);
}

$stmt->bind_param('i', $eventid);

$stmt->execute();

if (!$stmt->errno) {
    echo json_encode(array(
        "success" => true,
        "message" => "Success, Event deleted",
    ));
    exit;
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "ERROR: Event not deleted",
    ));
    exit;
}
$stmt->close();
