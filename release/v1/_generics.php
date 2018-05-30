<?php
include_once "_globals.php";
include_once "_overrides.php";

//Error handler helpers
include_once "_errorHandlers.php"; //Should always be first thing.

//Convert the request body to JSON if the content type is set to json
if($_SERVER["REQUEST_METHOD"] != "GET"){
    if(in_array("CONTENT_TYPE", array_keys($_SERVER)) && $_SERVER["CONTENT_TYPE"] == "application/json"){
        $_JSON = json_decode(file_get_contents('php://input'), true);
        if($_JSON == null){
            $_JSON = array();
        }
    }
}

//We will be outputting json.
header('Content-type: application/json');

// Database helper
include_once "_database.php";

//Authentication helpers
include_once "_authentication.php";

if(!isset($NO_AUTH_CHECKS) || $NO_AUTH_CHECKS !== true){
    $conn = database_connect();
    $authed = authorized($conn);
    
    if($authed[0] === true){ //Is authorized??
        // Get the user ID and privilege from the old token
        $userInfo = retrieveUserInfo($authed[1]);
        if($userInfo)
            generateAuthToken($userInfo[0], $userInfo[1]);// Generate a new JWT and xsrfToken
        
        $conn->close();
        return;
    }
    if($authed[1] != null && isTokenExpired($authed[1])){ //was the token once valid
        echoError($conn, 401, "AuthenticationExpired", "Generics");
    }
    echoError($conn, 401, "AuthorizationFailed");
}

//Make sure that the json obj contains all parameters in paramsArray
function requiredParams($conn, $json, $paramsArray){
    foreach($paramsArray as $key){
        if(!in_array($key, array_keys($json))){
            echoError($conn, 400, "MissingArgument".ucfirst($key));
        }
    }
}

function generateWhereStatement($conn, $allowedProps, $changesKeysRemap, $columnWhereClause, $props){
    
    $searchValues = array();
    foreach($props as $key => $value ){
        if($value == ""){
            continue;
        }
        if(!in_array($key, $allowedProps)){
            echoError($conn, 400, "KeyNotFound", "Key: '".$key."' was not found.");
        }
        if(in_array($key, array_keys($changesKeysRemap))){
            $key = $changesKeysRemap[$key];
            if($key == NULL) continue;
        }
        $searchValues[$key] = $value;
    }
    
    $where = "";
    $insertVals = array();
    if(!empty($searchValues)){
        $cols = array();
        $colsOperator = array();
    
        foreach ($searchValues as $k=>$value) {
            array_push($cols, $k);
            array_push($colsOperator, " ".$columnWhereClause[$k]." ");
        }
        
        $cols = array_map(function($x) use (&$colsOperator) {
            return $x . array_shift($colsOperator) . '?';
        }, $cols);
        $where = " WHERE ".implode(" AND ", $cols);
    }
    
    return array($where, $searchValues);
}

function getIP($ip = null, $deep_detect = TRUE){
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    } else {
        $ip = $_SERVER["REMOTE_ADDR"];
    }
    return $ip;
}

?>