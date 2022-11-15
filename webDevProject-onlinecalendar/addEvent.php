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

$id = $json_obj['add_id'];
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
    if ($id != $_SESSION['user_id']) {
        $alert = "ERROR: Session Hijack" . $_SESSION['user_id'];
        jsonAlert(false, $alert);
        exit;
    }
} else {
    $alert = "Please log in first!";
    jsonAlert(false, $alert);
    exit;
}

$stmt = $mysqli->prepare("insert into event (uid, title,date,time,tags,detail) values (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    $alert = "Query Prep Failed: %s\n" . $mysqli->error;
    jsonAlert(false, $alert);
}

$stmt->bind_param('isssss', $id, $title, $date, $time, $tags, $detail);

$stmt->execute();

$members = $json_obj['members'];

if (!$stmt->errno) {
    if ($members == '') {
        echo json_encode(array(
            "success" => true,
            "message" => "Sucuessfully Added Event to User's Calendar",
        ));
        exit;
    }
}
$stmt->close();

$idArray = explode(',', $members);
foreach ($idArray as &$idToCheck) {

    if ($idToCheck == $id) {
        $alert = "ERROR: you can not share Event with yourself";
        jsonAlert(false, $alert);
        exit;
    }
    $checkid = $mysqli->prepare("select * from user where id = ?");
    if (!$checkid) {
        $alert = "ERROR: User Query Prep Failed: %s\n" . $mysqli->error;
        jsonAlert(false, $alert);
        exit;
    }
    $checkid->bind_param('s', $idToCheck);
    $checkid->execute();
    $result = $checkid->get_result();
    $row = mysqli_num_rows($result);
    if (!$row) {
        $alert = "ERROR: Incorrect Username: This UserName Does Not exist in Database";
        jsonAlert(false, $alert);
        exit;
    }

    if ($checkid->errno) {
        echo json_encode(array(
            "success" => false,
            "message" => "ERROR: Username Does Not Exist",
        ));
        exit;
    }
    $checkid->close();

    $stmt = $mysqli->prepare("insert into event (uid, title,date,time,tags,detail) values (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $alert = "ERROR: Query Prep Failed: %s\n" . $mysqli->error;
        jsonAlert(false, $alert);
    }
    $newTitle = "Shared by #" . $id . ":  " . $title;
    $stmt->bind_param('isssss', $idToCheck, $newTitle, $date, $time, $tags, $detail);

    $stmt->execute();

    if ($stmt->errno) {
        echo json_encode(array(
            "success" => false,
            "message" => "ERROR: Insertion failed",
        ));
        exit;
    }
    $stmt->close();
}

echo json_encode(array(
    "success" => true,
    "message" => "Success, Event Added To Other Users As Well",
));
exit;
