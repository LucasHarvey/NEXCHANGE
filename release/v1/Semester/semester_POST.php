<?php
$conn = database_connect();

$newSemesterStart = null;
$newSemesterEnd = null;
$newMarchBreakStart = null;
$newMarchBreakEnd = null;

requiredParams($conn, $_POST, array("semesterCode", "password"));

$semesterCode = $_POST["semesterCode"];
$created = date('Y-m-d H:i:s');

if(array_key_exists($_POST, "newSemesterStart"))
    $newSemesterStart = $_POST["newSemesterStart"];
if(array_key_exists($_POST, "newSemesterEnd"))
    $newSemesterEnd = $_POST["newSemesterEnd"];
if(array_key_exists($_POST, "newMarchBreakStart"))
    $newMarchBreakStart = $_POST["newMarchBreakStart"];
if(array_key_exists($_POST, "newMarchBreakEnd"))
    $newMarchBreakEnd = $_POST["newMarchBreakEnd"];
    
$seasons = ["I", "W", "S", "F"];

if(!in_array($semesterCode[0], $seasons) || strlen($semesterCode) != 5)
    echoError($conn, 400, "SemesterNotValid");

$year = substr($semesterCode, 1);

if(!ctype_digit($year) || intval($year)<2000 || intval($year)>9999)
    echoError($conn, 400, "SemesterNotValid");
    
$coursesForSemester = database_get_row("SELECT id FROM courses WHERE semester=? LIMIT 1", "s", $semesterCode);
$semesterExists = database_get_row("SELECT semester_code FROM semesters WHERE semester_code=? LIMIT 1", "s", $semesterCode);

if(!isNewSemester($conn, $semesterCode))
    echoError($conn, 400, "SemesterOutdated");

if($coursesForSemester == null && $semesterExists == null){
    // If no courses with semester code in DB, newSemesterStart and newSemesterEnd must by present
    requiredParams($conn, $_POST, array("newSemesterStart", "newSemesterEnd"));
    
    // Semester end must be after semester start
    if(strtotime($newSemesterEnd) <= strtotime($newSemesterStart))
        echoError($conn, 400, "SemesterDatesNotValid");
    
    if($newMarchBreakStart != null && $newMarchBreakEnd != null){
        // March break end must be after march break start
        if(strtotime($newMarchBreakEnd) <= strtotime($newMarchBreakStart))
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
    
    $insertTypes = "ssssss";
    $insertVals = array($semesterCode, $newSemesterStart, $newSemesterEnd, $newMarchBreakStart, $newMarchBreakEnd, $created);
        
    // INSERT the semester into the DB
    database_insert($conn, "INSERT INTO semesters (semester_code, semester_start, semester_end, march_break_start, march_break_end, created) VALUES (?,?,?,?,?,?)", $insertTypes, $insertVals);
} else {
    if($newSemesterStart != null || $newSemesterEnd != null || $newMarchBreakStart != null || $newMarchBreakEnd != null)
        echoError($conn, 400, "SemesterExists");
}

function isNewSemester($conn, $semesterCode){
    $latestSemesterCode = database_get_row($conn, "SELECT semester_code FROM semesters ORDER BY created DESC LIMIT 1")["semester_code"];
    if($latestSemesterCode == null)
        return true;
    
    $latestSeason = $latestSemesterCode[0];
    $latestYear = substr($latestSemesterCode, 1);
    
    $season = $semesterCode[0];
    $year = substr($semesterCode, 1);
    
    if(intval($year) < intval($latestYear))
        return false;
        
    if(intval($year) == intval($latestYear)){
        $seasonKey = array_search($season, $seasons);
        $latestSeasonKey = array_search($latestSeason, $seasons);
        if($seasonKey <= $latestSeasonKey)
            return false;
    }
    
    return true;
}

?>