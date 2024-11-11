<?php
header("content-type:json");
function spitResult($status, $component="") {
    $returner = array();
    if ($status == 0) {
        $returner["status"] = "success";
        if ($component != "") {
            $returner = array_merge($returner, $component);
        }
    } else {
        $returner["status"] = "fail";
        $returner["err_code"] = $status;
        if ($component != "") {
            $returner["message"] = $component;
        }
    }
    echo json_encode($returner, JSON_UNESCAPED_UNICODE);
    die();
}

function jsonParamCheck(...$arr) {
    global $recved_json;
    foreach ($arr as $component) {
        if (!isset($recved_json[$component])) {
            spitResult(1, "insufficient param");
        }
    }
}

function formParamCheck(...$arr) {
    foreach ($arr as $component) {
        if (!isset($_GET[$component]) || $_GET[$component] == "") {
            spitResult(1, "insufficient param");
        }
    }
}

$METHOD = $_SERVER['REQUEST_METHOD'];

$recved_body = file_get_contents('php://input');
$recved_json = json_decode($recved_body, true);
?>