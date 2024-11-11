<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/lib/php/common.php");

if ($METHOD != "POST") {
    spitResult(1, "inappropriate method");
}

if (!is_array($recved_json)) {
    spitResult(1, "malformed input");
}

spitResult(0, $recved_json);
?>