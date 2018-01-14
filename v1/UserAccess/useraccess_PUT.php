<?php

/*
allowing a user to modify their own access. Only allowed to modify if they want notifications or not.
*/

$conn = database_connect();

$user_id = getUserFromToken();

requiredParams($conn, $_JSON, array("courseId", "notifications"));
$courseId = $_JSON["courseId"];
$notifications = $_JSON['notifications'];

$selectVals = array($courseId, $user_id);
if(!database_start_transaction($conn, true)){
	echoError($conn, 500, "DatabaseInsertError", "Could not start transaction.");
}
if(database_get_row($conn, "SELECT user_id, course_id FROM user_access WHERE course_id=? AND user_id=?", "ss", $selectVals) == null){
    echoError($conn, 403, "AuthorizationFailed");
}

$insertVals = array($notifications, $user_id, $courseId);
database_update($conn, "UPDATE user_access SET notifications=? WHERE user_id=? AND course_id=? LIMIT 1", "iss", $insertVals);
$value = database_get_row($conn, "SELECT notifications FROM user_access WHERE course_id=? AND user_id=?", "ss", $selectVals);
if(!database_commit($conn)){
	if(!database_rollback($conn)){
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

echoSuccess($conn, array(
    "courseId" => $courseId,
    "notifications" => $value['notifications'],
    "messageCode" => "UserAccessUpdated"
));

?>