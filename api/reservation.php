<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/lib/php/common.php");

if ($METHOD == "POST") {
    // Making new reservation

    jsonParamCheck("time", "duration", "type", "content", "std_id");

    $start_time = strtotime($recved_json["time"].":00");
    $duration = $recved_json["duration"];
    $type = $recved_json["type"];
    $content = $recved_json["content"];
    $std_id = $recved_json["std_id"];
    $ip = $_SERVER["REMOTE_ADDR"];

    if ($timestamp === false) {
        spitResult(-1, "invalid param");
    }

    if (gettype($duration) != "integer" || $duration < 1 || $duration > 6) {
        spitResult(-1, "invalid param");
    }
    $end_time = $start_time + ($duration * 30) * 60;

    if (gettype($type) != "integer" || $type < 0 || $type > 1) {
        spitResult(-1, "invalid param");
    }

    if ($content == "") {
        spitResult(-1, "invalid param");
    }

    if (gettype($std_id) != "integer" || strlen($std_id."") != 10 ) {
        spitResult(-1, "invalid param");
    }
    
    include_once($_SERVER["DOCUMENT_ROOT"]."/lib/php/db_conn.php");
    if (!$conn) {
        http_response_code(500);
        die();
    }

    $query = "INSERT INTO `reservation` VALUES (from_unixtime($start_time), from_unixtime($end_time), $type, \"$content\", $std_id, \"$ip\");";
    mysqli_query($conn, $query);

    spitResult(0, array("recved"=>$recved_json));
} else if ($METHOD == "GET") {
    // Get reservation list
    spitResult(0, array("reservation"=>array()));
}
?>