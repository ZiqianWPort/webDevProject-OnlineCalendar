<?php
require_once "database.php";
require_once "jsonAlert.php";

header("Content-Type: application/json");
//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

$username = $json_obj['username'];
$password = $json_obj['password'];
$hashpw = password_hash($password, PASSWORD_DEFAULT);
$alert = "Known Error";

$checkname = $mysqli->prepare("SELECT COUNT(*), id, pw FROM user WHERE username=?");
if (!$checkname) {
    $alert = "User Query Prep Failed:" . $mysqli->error;
    jsonAlert(false, $alert);
    exit;
}
$checkname->bind_param('s', $username);
$checkname->execute();
$checkname->bind_result($cnt, $user_id, $pwd_hash);
$checkname->fetch();

if ($cnt) {
    if ($cnt == 1 && password_verify($password, $pwd_hash)) {
        ini_set("session.cookie_httponly", 1);
        session_start();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $username;
        $tokenval = bin2hex(openssl_random_pseudo_bytes(32));
        $_SESSION['token'] = $tokenval;

        echo json_encode(array(
            "success" => true,
            "message" => "Success! you have been logged in",
            "token" => $tokenval,
            "username" => $username,
            "userid" => $user_id,

        ));
        exit;
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "ERROR: Incorrect Password or Username.",

        ));
        exit;
    }
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "ERROR: Username Does not Exist",
    ));
    exit;
}

$checkname->close();
