<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_JSON, array("semesterCode"));

$semesterCode = $_JSON["semesterCode"];

$season = ["I", "W", "S", "F"];

if(!in_array($semesterCode[0], $season) || strlen($semesterCode) != 5)
    echoError($conn, 400, "SemesterNotValid");

$year = substr($semesterCode, 1);

if(!ctype_digit($year) || intval($year)<2000 || intval($year)>9999)
    echoError($conn, 400, "SemesterNotValid");

$semesterStart = null;
$semesterEnd = null;
$marchBreakStart = null;
$marchBreakEnd = null;

if(array_key_exists("semesterStart", $_JSON))
    $semesterStart = $_JSON["semesterStart"];
if(array_key_exists("semesterEnd", $_JSON))
    $semesterEnd = $_JSON["semesterEnd"];
if(array_key_exists("marchBreakStart", $_JSON))
    $marchBreakStart = $_JSON["marchBreakStart"];
if(array_key_exists("marchBreakEnd", $_JSON))
    $marchBreakEnd = $_JSON["marchBreakEnd"];
    
if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseUpdateError", "Could not start transaction.");
}

$semester = database_get_row($conn, "SELECT * from semester_dates WHERE semester_code=?", "s", $semesterCode);

if($semester != null){
    
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
    $insertTypes = $insertTypes."s";
    array_push($insertValues, $semesterCode);
    
    database_update($conn, "UPDATE semester_dates SET $cols WHERE semester_code=?", $insertTypes, $insertValues);
} else {
    
    $insertValues = array($semesterCode, $semesterStart, $semesterEnd, $marchBreakStart, $marchBreakEnd);
    database_insert($conn, "INSERT INTO semester_dates (semester_code, semester_start, semester_end, march_break_start, march_break_end) VALUES (?,?,?,?,?)",
                        "sssss", $insertValues);
}
                        
if(!database_commit($conn)){
	if(!database_rollback($conn)){
	    $GLOBALS["NEXCHANGE_TRANSACTION"] = false;
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

echoSuccess($conn, array(
    "messageCode" => "SemesterSettingsUpdated"
));

?>