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
        error_log(getExceptionTraceAsString($e));
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

function getExceptionTraceAsString($exception) {
    try{
        $rtn = "";
        $count = 0;
        foreach ($exception->getTrace() as $frame) {
            $args = "";
            if (isset($frame['args'])) {
                $args = array();
                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }   
                }   
                $args = join(", ", $args);
            }
            $rtn .= sprintf( "#%s %s(%s): %s(%s)\n",
                                     $count,
                                     $frame['file'],
                                     $frame['line'],
                                     $frame['function'],
                                     $args );
            $count++;
        }
        return $rtn;
    }catch(Exception $e){
        return "Could not do stacktrace.".$e->getMessage();
    }
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
        'payload' => toUTF8($successObj)
    ));
    http_response_code($statusCode);
    die();
}

function toUTF8($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = toUTF8($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}

?>
