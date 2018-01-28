<?php

$conn = database_connect();

requiredParams($conn, $_POST, array("noteId"));
$noteId = $_POST["noteId"];

$user_id = getUserFromToken();
if(getUserPrivilege() == "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

// Check that the note exists
if(!database_contains($conn, "notes", $noteId)){
    echoError($conn, 404, "NoteNotFound");
}

// Check that the user is editing a note they posted
if(database_get_row($conn, "SELECT id FROM notes WHERE id=? AND user_id=?", "ss", array($noteId, $user_id)) == null){
    echoError($conn, 403, "AuthorizationFailed");
}

// Change the insert keys
$allowedProps = array("name", "description", "takenOn");
$changesKeysRemap = array("takenOn" => "taken_on");

$changes = array();
foreach($_POST as $key => $value ){
    if(in_array($key, $allowedProps)){
        if(in_array($key, array_keys($changesKeysRemap))){
            $key = $changesKeysRemap[$key];
        }
        $changes[$key] = $value;
    }
}

// Ensure that changes can be made
if(empty($changes) && empty($_FILES['file'])){ //No legal changes can be made
    echoError($conn, 400, "NoChangesToMake");
}

if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseUpdateError", "Could not start transaction.");
}

// Allowed file types
$allowed = ['pdf','docx', 'doc', 'ppt', 'xlsx', 'jpeg', 'jpg', 'png', 'txt', 'zip'];

//Max file size
$MAX_SINGLE_FILE_SIZE = 2 * 1024 * 1024; //2 mb

$succeeded = array();

include_once("./Notes/notes_conveniences.php");

//Verify all note extensions are allowed and file size is appropriate
validateUploadedFiles($conn, $allowed, $MAX_SINGLE_FILE_SIZE);

// Move the note files onto the server and retrieve the note data
$noteData = moveFiles();

$fileName = $noteData[0];
$storageName = $noteData[1];
$fileType = $noteData[2];
$fileSize = $noteData[3];
$md5 = $noteData[4];
$succeeded = $noteData[5];

// Update the note information 
if(!empty($changes)){
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
    
    array_push($insertValues, $noteId);
    $insertTypes = $insertTypes . "s";
    
    // Update the note data in the notes table
    database_update($conn, "UPDATE notes SET $cols WHERE id=? LIMIT 1", $insertTypes, $insertValues);
}

// Get the updated note data
$note = database_get_row($conn, "SELECT id, name, description, taken_on, created FROM notes WHERE id=? LIMIT 1", "s", $noteId);

if($note == null){
    // Delete the file from the server
    if(file_exists($storageName))
		unlink($storageName);
	echoError($conn, 500, "DatabaseUpdateError");
}

// Retrieve the old storage name for the file
$oldStorageName = retrieveStorageName($conn, $note["id"]);

// Update the file information in the database
$result = updateNoteFile($conn, $note["id"], $fileName, $storageName, $fileType, $fileSize, $md5);

// If the update failed, delete the most recent file
if(!$result){
	// Delete the most recent file from the server
    if(file_exists($storageName))
        unlink($storageName);
	echoError($conn, 500, "DatabaseUpdateError");
} 

// Delete the old file
if(!file_exists($oldStorageName) || !unlink($oldStorageName));
    echoError($conn, 404, "NoteFileDeleteFailure");

if(!database_commit($conn)){
	if(!database_rollback($conn)){
	    $GLOBALS["NEXCHANGE_TRANSACTION"] = false;
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

echoSuccess($conn, array(
    "messageCode" => "NoteUpdated",
    "note_id" => $noteId,
    "succeeded" => $succeeded,
	"note" => $note
));


?>