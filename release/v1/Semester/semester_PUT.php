<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_JSON, array("semesterCode"));

$semesterCode = $_JSON["semesterCode"];
if($semesterCode == "")
	echoError($conn, 400, "MissingArgumentSemesterCode");

$seasons = ["I", "W", "S", "F"];

if(!in_array($semesterCode[0], $seasons) || strlen($semesterCode) != 5)
    echoError($conn, 400, "SemesterNotValid");

$year = substr($semesterCode, 1);

if(!ctype_digit($year) || intval($year)<2000 || intval($year)>9999)
    echoError($conn, 400, "SemesterNotValid");
    
$semesterStart = null;
$semesterEnd = null;
$marchBreakStart = null;
$marchBreakEnd = null;

if(array_key_exists("semesterStart", $_JSON)){
    $semesterStart = $_JSON["semesterStart"];
    if($semesterStart == "")
        echoError($conn, 400, "MissingArgumentSemesterStart");
}

if(array_key_exists("semesterEnd", $_JSON)){
    $semesterEnd = $_JSON["semesterEnd"];
    if($semesterEnd == "")
        echoError($conn, 400, "MissingArgumentSemesterEnd");
}

if(array_key_exists("marchBreakStart", $_JSON))
    $marchBreakStart = $_JSON["marchBreakStart"];
if(array_key_exists("marchBreakEnd", $_JSON))
    $marchBreakEnd = $_JSON["marchBreakEnd"];
    
if($semesterStart != null){
    if(strlen($semesterStart) > 10)
        echoError($conn, 400, "SemesterStartNotValid");
}

if($semesterEnd != null){
    if(strlen($semesterEnd) > 10)
        echoError($conn, 400, "SemesterEndNotValid");
}

if($marchBreakStart != null){
    if(strlen($marchBreakStart) > 10)
        echoError($conn, 400, "MarchBreakStartFormatNotValid");
}

if($marchBreakEnd != null){
    if(strlen($marchBreakEnd) > 10)
        echoError($conn, 400, "MarchBreakEndFormatNotValid");
}

if($semesterStart != null && $semesterEnd != null){
    // Semester end must be after semester start
    if(strtotime($semesterEnd) <= strtotime($semesterStart))
        echoError($conn, 400, "SemesterDatesNotValid");
}

if($marchBreakStart != null && $marchBreakEnd != null){
    // March break end must be after march break start
    if(strtotime($marchBreakEnd) <= strtotime($marchBreakStart))
        echoError($conn, 400, "MarchBreakNotValid");
        
}

if($marchBreakStart != null && $semesterStart != null){
    if(strtotime($marchBreakStart) < strtotime($semesterStart))
        echoError($conn, 400, "MarchBreakStartNotValid");
}

if($marchBreakEnd != null && $semesterEnd != null){
    if(strtotime($marchBreakEnd) > strtotime($semesterEnd))
        echoError($conn, 400, "MarchBreakEndNotValid");
}
    
if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseUpdateError", "Could not start transaction.");
}

$semester = database_get_row($conn, "SELECT semester_code from semesters WHERE semester_code=?", "s", $semesterCode);


if($semester == null)
    echoError($conn, 400, "SemesterUpdateFailedDNE");

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

// Ensure that changes can be made
if(empty($changes)){ //No legal changes can be made
    echoError($conn, 400, "NoChangesToMake");
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

database_update($conn, "UPDATE semesters SET $cols WHERE semester_code=?", $insertTypes, $insertValues);

                        
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