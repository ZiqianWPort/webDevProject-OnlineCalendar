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
$userid = $json_obj['add_id'];
$title = $json_obj['add_title'];
$date = $json_obj['add_date'];
$time = $json_obj['add_time'];
$tags = $json_obj['add_tag'];
$detail = $json_obj['add_detail'];
$token = $json_obj['token'];

$alert = "Known Error";
if (isset($_SESSION['token']) && isset($_SESSION['user_id'])) {
    if (!hash_equals($token, $_SESSION['token'])) {
        $alert = $token . "ERROR: Token - Request Forgery" . $_SESSION['token'];
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

$stmt = $mysqli->prepare("update event set title = ?, date = ?, time = ?, tags = ?, detail = ? where eid = ?");
if (!$stmt) {
    $alert = "Query Prep Failed: %s\n" . $mysqli->error;
    jsonAlert(false, $alert);
}

$stmt->bind_param('sssssi', $title, $date, $time, $tags, $detail, $eventid);

$stmt->execute();

if (!$stmt->errno) {
    echo json_encode(array(
        "success" => true,
        "message" => "Success, Event has been edited",
    ));
    exit;
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "ERROR: Event not edited",
    ));
    exit;
}
$stmt->close();
