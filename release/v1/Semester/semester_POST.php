<?php
$conn = database_connect();

$newSemesterStart = null;
$newSemesterEnd = null;
$newMarchBreakStart = null;
$newMarchBreakEnd = null;

$semesterCode = $_POST["semesterCode"];

if(array_key_exists($_POST, "newSemesterStart"))
    $newSemesterStart = $_POST["newSemesterStart"];
if(array_key_exists($_POST, "newSemesterEnd"))
    $newSemesterEnd = $_POST["newSemesterEnd"];
if(array_key_exists($_POST, "newMarchBreakStart"))
    $newMarchBreakStart = $_POST["newMarchBreakStart"];
if(array_key_exists($_POST, "newMarchBreakEnd"))
    $newMarchBreakEnd = $_POST["newMarchBreakEnd"];
    
$coursesForSemester = database_get_row("SELECT id FROM courses WHERE semester=? LIMIT 1", "s", $semesterCode);
$semesterExists = database_get_row("SELECT semester_code FROM semester_dates WHERE semester_code=? LIMIT 1", "s", $semesterCode);

if($coursesForSemester == null && $semesterExists == null){
    // If no courses with semester code in DB, newSemesterStart and newSemesterEnd must by present
    requiredParams($conn, $_POST, array("newSemesterStart", "newSemesterEnd"));
    
    // Semester end must be after semester start
    if($newSemesterEnd <= $newSemesterStart)
        echoError($conn, 400, "SemesterDatesNotValid");
    
    if($newMarchBreakStart != null && $newMarchBreakEnd != null){
        // March break end must be after march break start
        if($newMarchBreakEnd <= $newMarchBreakStart)
        echoError($conn, 400, "MarchBreakNotValid");
    }

    // If march break end is present, march break start must be present
    if($newMarchBreakEnd != null && $newMarchBreakStart == null){
        echoError($conn, 400, "MissingArgumentMarchBreakStart");
    }

    // If march break start is present, march break end must be present
    if($newMarchBreakStart != null && $newMarchBreakEnd == null){
        echoError($conn, 400, "MissingArgumentMarchBreakEnd");
    }
    
    $insertTypes = "sssss";
    $insertVals = array($semesterCode, $newSemesterStart, $newSemesterEnd, $newMarchBreakStart, $newMarchBreakEnd);
        
    // INSERT the semester into the DB
    database_insert($conn, "INSERT INTO semester_dates (semester_code, semester_start, semester_end, march_break_start, march_break_end) VALUES (?,?,?,?,?)", $insertTypes, $insertVals);
} else {
    if($newSemesterStart != null || $newSemesterEnd != null || $newMarchBreakStart != null || $newMarchBreakEnd != null)
        echoError($conn, 400, "SemesterExists");
}


?>