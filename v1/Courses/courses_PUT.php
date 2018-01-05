<?php
$PASSWORD_LENGTH = 9;

$conn = database_connect();

$userId = getUserFromToken();

// Verify that the user is an admin
if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_JSON, array("courseId"));
$course_id = $_JSON["courseId"];

// Check that the note exists
if(!database_contains($conn, "courses", $course_id)){
    echoError($conn, 404, "CourseNotFound");
}

// Change the insert keys
$allowedProps = array("teacherFullName", "courseName", "courseNumber", "section", "semester");
$changesKeysRemap = array("teacherFullName" => "teacher_fullname", "courseName" => "course_name", "courseNumber" => "course_number");

$changes = array();
foreach($_JSON as $key => $value ){
    if(in_array($key, $allowedProps)){
        if(in_array($key, array_keys($changesKeysRemap))){
            $key = $changesKeysRemap[$key];
        }
        $changes[$key] = $value;
    }
}

// Ensure that changes can be made
if(empty($changes)){ //No legal changes can be made
    echoError($conn, 400, "NoChangesToMake");
}

$colNames = array();
$insertTypes = "";
$insertValues = array();

// Prepare the insert query
foreach($changes as $key => $value){
    $insertTypes = $insertTypes . "s";
    array_push($insertValues, $value);
    array_push($colNames, $key . "=?");
}
$cols = implode(",", $colNames);

// Add the course ID to the end of $insertValues
array_push($insertValues, $course_id);
$insertTypes = $insertTypes . "s";

if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseInsertError", "Could not start transaction.");
}

// Update the note data in the notes table
database_update($conn, "UPDATE courses SET $cols WHERE id=? LIMIT 1", $insertTypes, $insertValues);

if(!database_commit($conn)){
	if(!database_rollback($conn)){
	    $GLOBALS["NEXCHANGE_TRANSACTION"] = false;
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

// Select the updated course data
$course = database_get_row($conn, "SELECT id, teacher_fullname as teacherFullName, course_name as courseName, course_number as courseNumber, section, semester, created FROM courses WHERE id=? LIMIT 1", "s", $course_id);

if($course == null){
    echoError($conn, 404, "CourseNotFound");
}
echoSuccess($conn, $course);

?>