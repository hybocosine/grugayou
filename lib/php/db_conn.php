<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/lib/php/db_env.php");
$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_DBNAME, $DB_PORT );
?>