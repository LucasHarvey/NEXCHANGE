<?php

function globalErrorHandler($errorNumber, $errorString, $errorFile, $errorLine){
    error_log("$errorNumber - $errorString in $errorFile on line $errorLine", 0);
    echoError(null, 500, "UnknownServerError", "$errorNumber - $errorString in $errorFile on line $errorLine");
}

error_reporting(E_ALL);
set_error_handler("globalErrorHandler");

function logFrontEnd($message){
    error_log("FrontError: ".json_encode($message));
}

function echoError($conn, $status, $messageCode, $message = ""){
    error_log("EchoError: $status - $messageCode with M: $message", 0);
    $ignoreMsgCodes = ['AuthenticationExpired', 'AuthenticationFailed'];
    if(!in_array($messageCode, $ignoreMsgCodes)){
        $e = new Exception();
        error_log($e->getTraceAsString());
    }
    
    if($conn != null){ //would occur if error happened in a script without need of a DB...?!
        if($GLOBALS['NEXCHANGE_TRANSACTION']){
            if(!database_rollback($conn)){
                $GLOBALS['NEXCHANGE_TRANSACTION'] = false; //Prevent infinite loop of not being able to rollback transaction.
        		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
        	}
        }
        $conn->close();
    }
    echo json_encode(array(
        'status' => $status,
        'requestedAt' => $_SERVER['REQUEST_TIME'],
        'requested' => $_SERVER['REQUEST_URI'],
        'messageCode' => $messageCode,
        'message' => ""
    ));
    http_response_code($status);
    die();
}

function echoSuccess($conn, $successObj, $statusCode = 200){
    if($conn != null){ //would occur if error happened in a script without need of a DB...?!
        if($GLOBALS['NEXCHANGE_TRANSACTION']){
            if(!database_commit($conn)){
        		echoError($conn, 500, "DatabaseCommitError", "Could not rollback the transaction");
        	}
        }
        $conn->close();
    }
    echo json_encode(array(
        'status' => $statusCode,
        'requestedAt' => $_SERVER['REQUEST_TIME'],
        'payload' => $successObj
    ));
    http_response_code($statusCode);
    die();
}

?>
