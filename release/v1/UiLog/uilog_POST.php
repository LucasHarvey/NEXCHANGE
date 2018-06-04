<?php

$conn = database_connect();

requiredParams($conn, $_JSON, array("message"));

$message = $_JSON['message'];

$userid = retrieveUserInfo()[0];

if(!$userid){
    echoError($conn, 404, "UserNotFound");
}

// Check brute force before logging the error
$bruteStatusOK = getBruteStatus($conn, "UI_LOG", $userid, $GLOBALS['NEXCHANGE_ALLOWED_TRIES_UI'], $GLOBALS['NEXCHANGE_BRUTE_UI_INTERVAL'], $GLOBALS['NEXCHANGE_BRUTE_UI_WAIT']);

$ip = getIP();

if($bruteStatusOK){
    logFrontEnd($message);
    
    // Log the UI Error
    database_insert($conn, "INSERT INTO log_ui_errors (user_id, ip_address) values (?,?)", "ss", array($userid, $ip));

    echoSuccess($conn, array("messageCode" => "LoggedSuccessfully"), 201);
} else {
    echoError($conn, 403, "UILogDenied");
}

?>