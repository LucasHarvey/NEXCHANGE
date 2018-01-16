<?php
$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_GET, array("userId", "courseId"));

$user_id = $_GET["userId"];
$course_id = $_GET["courseId"];

if(!database_contains($conn, "users", $user_id)){
    echoError($conn, 404, "UserNotFound");
}

// Check that the user access exists
$accessExists = database_get_row($conn, "SELECT user_id FROM user_access WHERE user_id=? AND course_id=?", "ss", array($user_id, $course_id));

// If the user access doesn't exist, stop here
if($accessExists == null) echoError($conn, 404, "UserAccessNotFound");
    
database_delete($conn, "DELETE FROM user_access WHERE user_id=? AND course_id=?", "ss", array($user_id, $course_id));

echoSuccess($conn, array(
    "messageCode" => "UserAccessDeleted",
    "userId" => $user_id,
    "courseId" => $course_id
));

?>