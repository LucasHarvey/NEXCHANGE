<?php

$conn = database_connect();

requiredParams($conn, $_POST, array("noteName", "courseId", "takenOn"));

$user_id = getUserFromToken();
if(getUserPrivilege() == "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

if(empty($_FILES['file'])){
    echoError($conn, 400, "NoFilesUploaded");
}

$course_id = $_POST["courseId"];
$noteName = $_POST['noteName'];
$description = $_POST['description'];
$date = $_POST['takenOn'];

$noteTypes = "sssss";
$noteValues = array($user_id,$course_id,$noteName,$description,$date);

// Ensure that the user is a note taker for the course
$row = database_get_row($conn, "SELECT role FROM user_access WHERE user_id=? AND course_id=? AND role='NOTETAKER'", "ss", array($user_id, $course_id));
if(is_null($row)){
	echoError($conn, 403, "UserCreateNotesDenied");
}

if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseInsertError", "Could not start transaction.");
}

// Insert the note information into the database and store success/failure in variable
database_insert($conn, "INSERT INTO notes (user_id, course_id, name, description, taken_on) VALUES (?,?,?,?,?)", $noteTypes, $noteValues);

$note = database_get_row($conn, 
	"SELECT id FROM notes WHERE user_id=? AND course_id=? ORDER BY created DESC LIMIT 1",
	 "ss", array($user_id, $course_id));

if($note == null){
	echoError($conn, 500, "DatabaseInsertError");
}

include_once("./Notes/notes_conveniences.php");

// Insert the new note files and retrieve the storage name
uploadFiles($note["id"], "insert");

$users_Notified = database_get_all($conn, 
	"SELECT u.id, u.email FROM user_access ua INNER JOIN courses c ON ua.course_id=c.id ".
								 "INNER JOIN users u ON ua.user_id = u.id INNER JOIN notes n ON n.course_id = c.id ".
								 "WHERE ua.notifications=1 AND ua.role='STUDENT' AND n.id=?", 
								   $note["id"]);

if(!database_commit($conn)){
	if(!database_rollback($conn)){
		$GLOBALS['NEXCHANGE_TRANSACTION'] = false;
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

include_once("./_EMAIL_TASKS/notify_upload_task.php");
notify_note_upload_email_task($conn, $users_Notified, $note['id']);

//TODO LOG failures.
echoSuccess($conn, array(
	'succeeded' => $succeeded,
), 207);



?>