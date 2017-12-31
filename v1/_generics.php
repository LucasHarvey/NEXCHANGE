<?php
$GLOBALS['PAGE_SIZES'] = 20; //used for pagination

//Convert the request body to JSON if the content type is set to json
if($_SERVER["REQUEST_METHOD"] != "GET"){
    if(in_array("content-type", array_keys(apache_request_headers())) && apache_request_headers()["content-type"] == "application/json"){
        $_JSON = json_decode(file_get_contents('php://input'), true);
        if($_JSON == null){
            $_JSON = array();
        }
    }
}

//We will be outputting json.
header('Content-type: application/json');

//Error handler helpers
include_once "_errorHandlers.php";

// Database helper
include_once "_database.php";

//Authentication helpers
include_once "_authentication.php";

if(!isset($NO_AUTH_CHECKS) || $NO_AUTH_CHECKS !== true){
    $conn = database_connect();
    $authed = authorized();
    if($authed[0] === true){ //Is authorized??
        refreshUserToken($conn);
        $conn->close();
        return;
    }
    if($authed[1] != null && isTokenExpired($conn, $authed[1])){ //was the token once valid
        echoError($conn, 401, "AuthenticationExpired");
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

/*function recursive_array_search($needle,$haystack) {
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value || (is_array($value) && recursive_array_search($needle,$value) !== false)) {
            return $current_key;
        }
    }
    return false;
}*/

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

?>