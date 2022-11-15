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

$id = $json_obj['id'];
$date = $json_obj['date'];
$token = $json_obj['token'];
$alert = "Known Error";

if (isset($_SESSION['token']) && isset($_SESSION['user_id'])) {
    if ($id != $_SESSION['user_id'] || !hash_equals($token, $_SESSION['token'])) {
        $alert = "Error token. Request forgery detected";
        jsonAlert(false, $alert);
        exit;
    }
    if ($id != $_SESSION['user_id']) {
        $alert = "Session hijack detected" . $_SESSION['user_id'];
        jsonAlert(false, $alert);
        exit;
    }
} else {
    $alert = "Please login first. You are guest account now.";
    jsonAlert(false, $alert);
    exit;
}
$id = $_SESSION['user_id'];

//check database for issues
$checkEvent = $mysqli->prepare("SELECT * from event WHERE uid = ? AND date = ?");
if (!$checkEvent) {
    $alert = "User Query Prep Failed: %s\n" . $mysqli->error;
    jsonAlert(false, $alert);
    exit;
}
$checkEvent->bind_param('is', $id, $date);
$checkEvent->execute();
if ($checkEvent->errno) {
    $alert = "ERROR: Failed to fetch";
    jsonAlert(false, $alert);
    exit;
}
$checkEvent->bind_result($seid, $suid, $stitle, $sdate, $stime, $stags, $sdetails);

$json_sending = array();
$event_count = 0;
while ($checkEvent->fetch()) {
    $event_count += 1;
    $eid = htmlspecialchars($seid);
    $uid = htmlspecialchars($suid);
    $title = htmlspecialchars($stitle);
    $date = htmlspecialchars($sdate);
    $time = htmlspecialchars($stime);
    $tags = htmlspecialchars($stags);
    $detail = htmlspecialchars($sdetails);

    $event_inst = array(
        'eid' => $eid,
        'uid' => $uid,
        'title' => $title,
        'date' => $date,
        'time' => $time,
        'tags' => $tags,
        'detail' => $detail,
    );
    array_push($json_sending, $event_inst);
}

echo json_encode(array(
    "success" => true,
    "message" => "Success, User has been registered",
    "sent_data" => $json_sending,
));
exit;
