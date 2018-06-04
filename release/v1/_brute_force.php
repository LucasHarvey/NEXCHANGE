<?php

function getBruteStatus($conn, $type, $userid, $allowedTries, $interval, $waitTime){
    
    $results = [];
    
    if($type == "LOGIN_ATTEMPT"){
        $results = database_get_all($conn, "SELECT UNIX_TIMESTAMP(attempt_at) as timestamp FROM login_attempts WHERE user_id=? ORDER BY attempt_at DESC LIMIT 5", "s", $userid);
    } elseif ($type == "UI_LOG"){
        $results = database_get_all($conn, "SELECT UNIX_TIMESTAMP(error_at) as timestamp FROM log_ui_errors WHERE user_id=? ORDER BY error_at DESC LIMIT 5", "s", $userid);
    }
    
    if(count($results) < $allowedTries)
        return true;
        
    $intervalBetweenLogs = $results[0]["timestamp"] - $results[4]["timestamp"];
    $now = time();
    $latestLog = $results[0]["timestamp"];
    $currentTime = $now - $latestLog;
    
    // Check if:
    //- First and most recent logs are at least X seconds from each other OR
    //- The latest attempt was at least Y minutes ago
    if($intervalBetweenLogs >= $interval || $currentTime >= $waitTime)
        return true;
    
    return false;
}

?>