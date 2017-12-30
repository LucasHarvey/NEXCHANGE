<?php

$conn = database_connect();

$user_id = getUserFromToken($conn);

requiredParams($_JSON, array("noteId"));
$noteId = $_JSON["noteId"];

if(!database_contains($conn, "notes", $noteId)){
    echoError(404, "NoteNotFound");
}

if(database_get_row($conn, "SELECT id FROM notes WHERE id=? AND user_id=?", "ss", array($noteId, $user_id)) == null){
    echoError(403, "AuthorizationFailed");
}

$allowedProps = array("name", "description", "takenOn");
$changesKeysRemap = array("takenOn" => "taken_on");

$changes = array();
foreach($_JSON as $key => $value ){
    if(in_array($key, $allowedProps)){
        if(in_array($key, array_keys($changesKeysRemap))){
            $key = $changesKeysRemap[$key];
        }
        $changes[$key] = $value;
    }
}

if(empty($changes)){ //No legal changes can be made
    echoError(400, "NoChangesToMake");
}

$colNames = array();
$insertTypes = "";
$insertValues = array();
foreach($changes as $key => $value){
    $insertTypes = $insertTypes . "s";
    array_push($insertValues, $value);
    array_push($colNames, $key . "=?");
}
$cols = implode(",", $colNames);

array_push($insertValues, $noteId);
$insertTypes = $insertTypes . "s";

database_update($conn, "UPDATE notes SET $cols WHERE id=? LIMIT 1", $insertTypes, $insertValues);

$conn->close();

echoSuccess(array(
    "messageCode" => "NoteUpdated",
    "note_id" => $noteId
));

?>