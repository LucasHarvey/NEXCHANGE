<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

// Change the insert keys
$allowedProps = array("semesterStart", "semesterEnd", "marchBreakStart", "marchBreakEnd");
$changesKeysRemap = array(
    "semesterStart" => "semester_start", 
    "semesterEnd" => "semester_end",
    "marchBreakStart" => "march_break_start",
    "marchBreakEnd" => "march_break_end"
    );

$changes = array();
foreach($_JSON as $key => $value ){
    if(in_array($key, $allowedProps)){
        if(in_array($key, array_keys($changesKeysRemap))){
            $key = $changesKeysRemap[$key];
        }
        $changes[$key] = $value;
    }
}

$colNames = array();
$insertTypes = "";
$insertValues = array();
foreach($changes as $key => $value){
    $insertTypes = $insertTypes."s";
    array_push($insertValues, $value);
    array_push($colNames, $key . "=?");
}
$cols = implode(",", $colNames);

if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseUpdateError", "Could not start transaction.");
}
                        
database_update($conn, "UPDATE semester_dates SET $cols", $insertTypes, $insertValues);
                        
if(!database_commit($conn)){
	if(!database_rollback($conn)){
	    $GLOBALS["NEXCHANGE_TRANSACTION"] = false;
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

echoSuccess($conn, array(
    "messageCode" => "SemesterUpdated"
));

?>