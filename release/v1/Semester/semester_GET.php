<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseUpdateError", "Could not start transaction.");
}

$_query = "SELECT semester_start as semesterStart, semester_end as semesterEnd, march_break_start as marchBreakStart, march_break_end as marchBreakEnd FROM semester_dates";
$query = $conn->prepare($_query);

$semesterDetails = database_execute_single($query);

if(!database_commit($conn)){
	if(!database_rollback($conn)){
	    $GLOBALS["NEXCHANGE_TRANSACTION"] = false;
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

if($semesterDetails == null)
	echoError($conn, 400, "SemesterSettingsNotFound");

$semesterStart = $semesterDetails["semesterStart"];
$semesterEnd = $semesterDetails["semesterEnd"];
$marchBreakStart = $semesterDetails["marchBreakStart"];
$marchBreakEnd = $semesterDetails["marchBreakEnd"];

echoSuccess($conn, array(
    "semesterStart" => $semesterStart,
    "semesterEnd" => $semesterEnd,
	"marchBreakStart" => $marchBreakStart,
	"marchBreakEnd" => $marchBreakEnd
));

?>