<?php
function jsonAlert($staBool, $msg)
{
    echo json_encode(array(
        "success" => $staBool,
        "message" => $msg,
    ));
}

?>