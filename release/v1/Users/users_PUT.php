<?php

$conn = database_connect();

$user_id = getUserFromToken();

requiredParams($conn, $_JSON, array("currentPassword"));
$currentPassword = $_JSON["currentPassword"];

//TODO: base 64 decode
if($currentPassword == "")
    echoError($conn, 403, "MissingArgumentPassword");
if(strlen($password) < $GLOBALS['PASSWORD_LENGTH'])
    echoError($conn, 403, "PasswordTooSmall");


$user = database_get_row($conn, "SELECT passwordhash FROM users where id=?", "s", $user_id);
if(!password_verify($currentPassword, $user['passwordhash'])){
    echoError($conn, 403, "AuthenticationFailed", "UsersPut");
}

$allowedProps = array("password", "email");
$changesKeysRemap = array("password" => "passwordhash");

$changes = array();
foreach($_JSON as $key => $value ){
    if(in_array($key, $allowedProps)){
        if(in_array($key, array_keys($changesKeysRemap))){
            $key = $changesKeysRemap[$key];
        }
        $changes[$key] = $value;
    }
}

if(empty($changes)){ //No legal changes can be made
    echoError($conn, 400, "NoChangesToMake");
}

$colNames = array();
$insertTypes = "";
$insertValues = array();
foreach($changes as $key => $value){
    if($key == "passwordhash"){
        $pass = base64_decode(substr($value, 6));
        if(strlen($pass) < $GLOBALS['PASSWORD_LENGTH']){
            echoError($conn, 400, "PasswordTooSmall");
        }
        $value = password_hash($pass, PASSWORD_BCRYPT);
    }elseif($key == "email"){
        validateEmail($value);
    }
    $insertTypes = $insertTypes."s";
    array_push($insertValues, $value);
    array_push($colNames, $key . "=?");
}
$cols = implode(",", $colNames);

$insertTypes = $insertTypes."s";
array_push($insertValues, $user_id);

database_update($conn, "UPDATE users SET $cols WHERE id=? LIMIT 1", $insertTypes, $insertValues);

echoSuccess($conn, array(
    "messageCode" => "UserUpdated",
    "userId" => $user_id
));

function validateEmail($email){
    if($email == ""){
        echoError($conn, 400, "MissingArgumentEmail");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echoError($conn, 400, "EmailNotValid");
    }
    if(strlen($email) > 255){
        echoError($conn, 400, "EmailTooLong");
    }
    return;
}

?>