<?php

$conn = database_connect();
$user_id = getUserFromToken($conn);

$query = "SELECT id, login_id as 'studentId', first_name as 'firstName', last_name as 'lastName', email FROM users WHERE id=? LIMIT 1";

$user = database_get_row($conn, $query, "s", $user_id);

if(count($user) == 0){
    echoError($conn, 404, "UserNotFound");
}

$userAccesses = database_get_all($conn, "SELECT role FROM user_access WHERE user_id=?", "s", $user_id);

foreach ($userAccesses as $item){
    if($item["role"] == "NOTETAKER"){
        echoSuccess($conn, array("user" => $user, "role" => "NOTETAKER"));
        break;
    }
}

echoSuccess($conn, array("user" => $user, "role" => "STUDENT"));

?>