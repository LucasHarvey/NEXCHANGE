<?php

$conn = database_connect();

requiredParams($conn, $_JSON, array("message"));
$message = $_JSON['message'];

$ip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);

// Check brute force before logging the error
$bruteStatusOK = getBruteStatus($conn, $ip);

if($bruteStatusOK){
    logFrontEnd($message);
    
    // Log the UI Error
    database_insert($conn, "INSERT INTO log_ui_errors (ip_address) values (?)", "s", $ip);

    echoSuccess($conn, array("messageCode" => "LoggedSuccessfully"), 201);
} else {
    echoError($conn, 403, "UILogDenied");
}

function getBruteStatus($conn, $ip){
    
    $errors = database_get_all($conn, "SELECT UNIX_TIMESTAMP(error_at) as uiErrorTime FROM log_ui_errors WHERE ip_address=? ORDER BY error_at DESC LIMIT 5", "s", $ip);
    
    if(count($errors) < 5)
        return true;
        
    $intervalBetweenErrors = $errors[0]["uiErrorTime"] - $errors[4]["uiErrorTime"];
    $now = time();
    $latestError = $errors[0]["uiErrorTime"];
    $waitTime = $now - $latestError;
    
    // Check if:
    //- First and most recent errors are at least 30 seconds from each other OR
    //- The latest attempt was at least 1 minutes ago
    if($intervalBetweenErrors >= $GLOBALS["NEXCHANGE_BRUTE_UI_INTERVAL"] || $waitTime >= $GLOBALS["NEXCHANGE_BRUTE_UI_WAIT"])
        return true;
    
    return false;
}
?>