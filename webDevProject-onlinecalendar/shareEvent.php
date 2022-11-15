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

$eid = $json_obj['event_id'];
$sid = $json_obj['share_id'];
$token = $json_obj['token'];
$userid = $json_obj['user_id'];

$alert = "Known Error";
if (isset($_SESSION['token']) && isset($_SESSION['user_id'])) {
    if (!hash_equals($token, $_SESSION['token'])) {
        $alert = $token . "ERROR: token - Request Forgery" . $_SESSION['token'];
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

$stmt = $mysqli->prepare("select uid,title,date,time,tags,detail from event where eid=?");
if (!$stmt) {
    $alert = "Query Prep Failed: %s\n" . $mysqli->error;
    jsonAlert(false, $alert);
}

$stmt->bind_param('i', $eid);

$stmt->execute();
$stmt->bind_result($s_uid, $s_title, $a_date, $s_time, $s_tags, $s_detail);
$stmt->fetch();
$stmt->close();

$shareinsert = $mysqli->prepare("insert into event (uid,title,date,time,tags,detail) values (?, ?, ?, ?, ?, ?)");
if (!$shareinsert) {
    $alert = "Query Prep Failed: %s\n" . $mysqli->error;
    jsonAlert(false, $alert);
}

$shareinsert->bind_param('isssss', $sid, $s_title, $a_date, $s_time, $s_tags, $s_detail);

$shareinsert->execute();

if (!$shareinsert->errno) {
    $alert = "Success. Event shared";
    jsonAlert(true, $alert);
    exit;
} else {
    $alert = "ERROR: Event failed to be shared";
    jsonAlert(false, $alert);
    exit;
}
$shareinsert->close();
