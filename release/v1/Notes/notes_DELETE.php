<?php

$conn = database_connect();

$user_id = getUserFromToken();
if($user_id == null)
    echoError($conn, 403, "AuthorizationFailed");
    
$userIsNotAdmin = getUserPrivilege() != "ADMIN";

$whereClause = "";
if($userIsNotAdmin){
    $whereClause = " AND user_id=?";
}

requiredParams($conn, $_GET, array("id"));
$noteId = $_GET["id"];
if($noteId == "")
    echoError($conn, 400, "MissingArgumentNoteId");

if(!database_contains($conn, "notes", $noteId)){
    echoError($conn, 404, "NoteNotFound");
}

$deleteType = "s";
$deleteVals = array($noteId);
if($userIsNotAdmin){
    $deleteType = $deleteType."s";
    array_push($deleteVals, $user_id);
}

if(database_get_row($conn, "SELECT id FROM notes WHERE id=?".$whereClause, $deleteType, $deleteVals) == null){
    echoError($conn, 403, "AuthorizationFailed");
}

$oldFile = database_get_row($conn, "SELECT storage_name FROM notefiles WHERE note_id=? LIMIT 1", "s", $noteId);

if(!$oldFile)
    echoError($conn, 404, "NoteFileNotFound");

$storageName = $oldFile["storage_name"];

// Delete the old file
if(!file_exists("./Files/".$storageName) || !unlink("./Files/".$storageName))
    echoError($conn, 404, "NoteFileDeleteFailure");

database_delete($conn, "DELETE FROM notes WHERE id=?".$whereClause." LIMIT 1", $deleteType, $deleteVals);

echoSuccess($conn, array(
    "messageCode" => "NoteDeleted"
));

?>
