<?php

$conn = database_connect();

$user_id = getUserFromToken($conn);
$userIsNotAdmin = getUserPrivilege() != "ADMIN";

$whereClause = "";
if($userIsNotAdmin){
    $whereClause = " AND user_id=?";
}

requiredParams($conn, $_GET, array("id"));
$noteId = $_GET["id"];

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

database_delete($conn, "DELETE FROM notes WHERE id=?".$whereClause." LIMIT 1", $deleteType, $deleteVals);


echoSuccess($conn, array(
    "messageCode" => "NoteDeleted"
));

?>