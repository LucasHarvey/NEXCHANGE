<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_GET, array("semesterCode"));

$semesterCode = $_GET["semesterCode"];

$season = ["I", "W", "S", "F"];

if(!in_array($semesterCode[0], $season) || strlen($semesterCode) != 5)
    echoError($conn, 400, "SemesterNotValid");

$year = substr($semesterCode, 1);

if(!ctype_digit($year) || intval($year)<2000 || intval($year)>9999)
    echoError($conn, 400, "SemesterNotValid");

if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseUpdateError", "Could not start transaction.");
}

$semesterDetails = database_get_row($conn, "SELECT semester_start as semesterStart, semester_end as semesterEnd, march_break_start as marchBreakStart, march_break_end as marchBreakEnd ".
											"FROM semesters WHERE semester_code=?", "s", $semesterCode);

if($semesterDetails == null)
	echoError($conn, 400, "SemesterFetchFailedDNE");

if(!database_commit($conn)){
	if(!database_rollback($conn)){
	    $GLOBALS["NEXCHANGE_TRANSACTION"] = false;
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

$semesterStart = $semesterDetails["semesterStart"];
$semesterEnd = $semesterDetails["semesterEnd"];
$marchBreakStart = $semesterDetails["marchBreakStart"];
$marchBreakEnd = $semesterDetails["marchBreakEnd"];

echoSuccess($conn, array(
	"semesterCode" => $semesterCode,
    "semesterStart" => $semesterStart,
    "semesterEnd" => $semesterEnd,
	"marchBreakStart" => $marchBreakStart,
	"marchBreakEnd" => $marchBreakEnd
));

?>