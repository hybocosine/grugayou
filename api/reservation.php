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

    // Input value validation check
    if (true) {
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
    }

    // DB Connection Establishing
    include_once($_SERVER["DOCUMENT_ROOT"]."/lib/php/db_conn.php");
    if (!$conn) {
        http_response_code(500);
        die();
    }

    $query = "SELECT count(*) FROM `reservation` WHERE ".
    "( (from_unixtime($end_time) > start_time) or (from_unixtime($end_time) > end_time) ) ".
    " and ( (end_time > from_unixtime($start_time)) or (start_time > from_unixtime($start_time)) );";
    $result = mysqli_query($conn, $query);

    $row = mysqli_fetch_row($result);
    if ($row[0] > 0) {
        mysqli_close($conn);
        spitResult(1, "already reserved");
    }

    $query = "INSERT INTO `reservation` VALUES (from_unixtime($start_time), from_unixtime($end_time), $type, \"$content\", $std_id, \"$ip\");";
    mysqli_query($conn, $query);
    mysqli_close($conn);

    spitResult(0, array("recved"=>$recved_json));
} else if ($METHOD == "GET") {
    // DB Connection Establishing
    include_once($_SERVER["DOCUMENT_ROOT"]."/lib/php/db_conn.php");
    if (!$conn) {
        http_response_code(500);
        die();
    }

    formParamCheck("date");
    if ((strlen($_GET["date"]) != 6 && strlen($_GET["date"]) != 8) || !is_numeric($_GET["date"])) {
        spitResult(-1, "invalid param");
    }

    $year = substr($_GET["date"], 0, 4);
    $month = substr($_GET["date"], 4, 2);

    if (strlen($_GET["date"]) == 8) {
        // Get reservation list with day
        $day = substr($_GET["date"], 6, 2);

        $query = "SELECT * FROM reservation WHERE DATE_FORMAT(start_time, '%Y-%m-%d') = '$year-$month-$day' or DATE_FORMAT(end_time, '%Y-%m-%d') = '$year-$month-$day' ORDER BY start_time ASC;";
        $result = mysqli_query($conn, $query);

        $schedules = array();
        $total_time_slice = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $start_time = explode(" ", $row["start_time"]);
            $end_time = explode(" ", $row["end_time"]);
            $content = $row["content"];
            
            if ($start_time[0] != $end_time[0]) {
                // 날짜가 넘어가면
                $end_time[0] = $start_time[0];
                $end_time[1] = "24:00:00";
            }

            $start_hour =  explode(":", $start_time[1])[0];
            $start_min =  explode(":", $start_time[1])[1];
            $end_hour =  explode(":", $end_time[1])[0];
            $end_min =  explode(":", $end_time[1])[1];

            $schedule_times = array();
            while ($start_hour != $end_hour || $start_min != $end_min) {
                $total_time_slice += 1;
                array_push($schedule_times, $start_hour.":".$start_min);
                $start_min = ($start_min + 30) % 60;
                if ($start_min == 0) {
                    $start_hour += 1;
                    $start_min = "00";
                    if ($start_hour < 10) {
                        $start_hour = "0".$start_hour;
                    }
                }
            }
            array_push($schedules, array("content"=>$content, "times"=>$schedule_times));

        }
        mysqli_close($conn);
        spitResult(0, array("count"=> $total_time_slice, "reservation"=>$schedules));
    } else {
        // Get reservation list with month only
        mysqli_close($conn);
        spitResult(1, "not implemented");
    }
}
?>