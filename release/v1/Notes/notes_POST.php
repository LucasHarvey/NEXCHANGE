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

$noteTypes = "ssssss";
$created = date('Y-m-d H:i:s');
$noteValues = array($user_id,$course_id,$created,$noteName,$description,$date);

// Ensure that the user is a note taker for the course
$row = database_get_row($conn, "SELECT role FROM user_access WHERE user_id=? AND course_id=? AND role='NOTETAKER'", "ss", array($user_id, $course_id));
if(is_null($row)){
	echoError($conn, 403, "UserCreateNotesDenied");
}

if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseInsertError", "Could not start transaction.");
}

$succeeded = array();

include_once("./Notes/notes_conveniences.php");

//Verify all note extensions are allowed and file size is appropriate
validateUploadedFiles($conn);

// Move the note files onto the server and retrieve the note data
$noteData = moveFiles();

$fileName = $noteData[0];
$storageName = $noteData[1];
$fileType = $noteData[2];
$fileSize = $noteData[3];
$md5 = $noteData[4];
$succeeded = $noteData[5];

// Insert the note information into the database 
database_insert($conn, "INSERT INTO notes (user_id, course_id, created, name, description, taken_on) VALUES (?,?,?,?,?,?)", $noteTypes, $noteValues);

$note = database_get_row($conn, 
	"SELECT id FROM notes WHERE user_id=? AND course_id=? AND created=? LIMIT 1",
	 "sss", array($user_id, $course_id, $created));

if($note == null){
	// Delete the file from the server
	if(file_exists($storageName))
		unlink($storageName);
	echoError($conn, 500, "DatabaseInsertError");
}

// Insert the file information into the database
$result = insertNoteFile($conn, $note["id"], $fileName, $storageName, $fileType, $fileSize, $md5);

if(!$result){
	// Delete the file from the server
	if(file_exists($storageName))
		unlink($storageName);
	echoError($conn, 500, "DatabaseInsertError");
}

$users_Notified = database_get_all($conn, 
	"SELECT u.id, u.email FROM user_access ua INNER JOIN courses c ON ua.course_id=c.id ".
								 "INNER JOIN users u ON ua.user_id = u.id INNER JOIN notes n ON n.course_id = c.id ".
								 "WHERE ua.notifications=1 AND ua.role='STUDENT' AND n.id=?", "s",
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