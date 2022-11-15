<?php
ini_set("session.cookie_httponly", 1);
session_start();
$_SESSION['user_id'] = "";
$_SESSION['user_name'] = "";

session_destroy();
echo json_encode(array(
    "success" => true,
    "message" => "Success. You have been logged out.",
));
exit();
