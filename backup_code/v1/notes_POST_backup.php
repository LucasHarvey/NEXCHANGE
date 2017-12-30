<?php

requiredParams($_POST, array("noteName", "courseId", "takenOn"));

$allowed = ['pdf','docx', 'doc', 'pptx', 'ppt', 'xlsx', 'jpeg', 'jpg', 'png', 'txt', 'zip'];
$MAX_SINGLE_FILE_SIZE = 5 * 1024 * 1024; //2 mb

$conn = database_connect();

$user_id = getUserFromToken($conn);

if(empty($_FILES['file'])){
    echoError(400, "NoFilesUploaded");
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
	echoError(403, "UserCreateNotesDenied");
}

//Verify all note extensions are allowed and file size is appropriate
validateUploadedFiles($allowed, $MAX_SINGLE_FILE_SIZE);

database_insert($conn, "INSERT INTO notes (user_id, course_id, name, description, taken_on) VALUES (?,?,?,?,?)", $noteTypes, $noteValues);
$note = database_get_row($conn, 
	"SELECT id FROM notes WHERE user_id=? AND course_id=? ORDER BY created DESC LIMIT 1",
	 "ss", array($user_id, $course_id));
if($note == null){
	echoError(500, "DatabaseInsertError");
}

$failed = array();
$succeeded = array();

foreach($_FILES['file']['name'] as $key => $name){
	// Ensure that there is no error for the file
	if($_FILES['file']['error'][$key] != 0) {
	    array_push($failed, array(
        	"name" => $name,
        	"messageCode" => "UnknownFileUploadError",
        	"status" => 500
    	));
	    continue;
	}
	
	$fileName = $_FILES['file']['name'][$key];
	$fileSize = $_FILES['file']['size'][$key];
	$fileType = $_FILES['file']['type'][$key];
	$content = file_get_contents($_FILES['file']['tmp_name'][$key]);
	$md5 = md5_file($_FILES['file']['tmp_name'][$key]);
	
	$result = insertNoteFile($conn, $note["id"], $fileName, $fileSize, $fileType, $content, $md5);
	
	if($result){
    	array_push($succeeded, array(
    	    "name" => $name,
    	    "md5" => $md5
    	));
	}else{
    	array_push($failed, array(
        	"name" => $name,
        	"messageCode" => "DatabaseInsertError",
        	"status" => 500
    	));
	}
}
$conn->close();

// Why do we need a 207 here??
echoSuccess(array(
	'succeeded' => $succeeded,
	'failed' => $failed
), 207);


function insertNoteFile($conn, $noteId, $fileName, $fileSize, $fileType, $content, $md5){
	$_null = NULL;
	$insertTypes = "sssibs";
	$insertValues = array($noteId,$fileName,$fileType,$fileSize,$_null,$md5);
	
	return database_insert_long_data($conn, 
			"INSERT INTO notefiles (note_id, name, type, size, content, md5) VALUES (?,?,?,?,?,?)",
			$insertTypes, $insertValues, 4, $content, false);
}

function validateUploadedFiles($allowed, $MAX_SINGLE_FILE_SIZE){
    foreach($_FILES["file"]["name"] as $key => $name){
        if($_FILES['file']['error'][$key] == 0) {
            $fileDotSeparated = explode('.', $name); //MUST be on 2 lines.
            $ext = strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
            if(!in_array($ext, $allowed)){
            	echoError(409, "NoteExtensionUnauthorized");
            }
            
            if($_FILES['file']['size'][$key] > $MAX_SINGLE_FILE_SIZE){
                echoError(409, "FileIsTooBig");
            }
        }else{
            echoError(500, "UnknownFileUploadError");
        }
    }
}

?>