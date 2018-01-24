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
	echoError($conn, 500, "DatabaseUpdateError");
}

include_once("./Notes/notes_conveniences.php");

// Update the new note files
uploadFiles($noteId, "update");

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