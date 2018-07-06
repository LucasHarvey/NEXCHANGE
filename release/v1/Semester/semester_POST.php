<?php
$conn = database_connect();

$userId = getUserFromToken();

// Verify that the user is an admin
if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_POST, array("semesterCode", "password", "newSemesterStart", "newSemesterEnd", "newMarchBreakStart", "newMarchBreakEnd"));

$password = $_POST["password"];
if($password == "")
    echoError($conn, 401, "MissingArgumentPassword");
if(strlen($password) < $GLOBALS['PASSWORD_LENGTH']){
    echoError($conn, 401, "PasswordTooSmall");
}

$password = base64_decode($password);
$user = database_get_row($conn, "SELECT passwordhash FROM users WHERE id=?", "s", $userId);
if(!password_verify($password, $user["passwordhash"])){
    echoError($conn, 401, "AuthenticationFailed");
}

$semesterCode = $_POST["semesterCode"];
if($semesterCode == "")
	echoError($conn, 400, "MissingArgumentSemesterCode");

$created = date('Y-m-d H:i:s');

$newSemesterStart = $_POST["newSemesterStart"];
$newSemesterEnd = $_POST["newSemesterEnd"];
$newMarchBreakStart = $_POST["newMarchBreakStart"];
$newMarchBreakEnd = $_POST["newMarchBreakEnd"];

if($newSemesterStart == "") $newSemesterStart = null;
if($newSemesterEnd == "") $newSemesterEnd = null;
if($newMarchBreakStart == "") $newMarchBreakStart = null;
if($newMarchBreakEnd == "") $newMarchBreakEnd = null;
    
$seasons = ["I", "W", "S", "F"];

if(!in_array($semesterCode[0], $seasons) || strlen($semesterCode) != 5)
    echoError($conn, 400, "SemesterNotValid");

$year = substr($semesterCode, 1);

if(!ctype_digit($year) || intval($year)<2000 || intval($year)>9999)
    echoError($conn, 400, "SemesterNotValid");
    
$coursesForSemester = database_get_row($conn, "SELECT id FROM courses WHERE semester=? LIMIT 1", "s", $semesterCode);
$semesterExists = database_get_row($conn, "SELECT semester_code FROM semesters WHERE semester_code=? LIMIT 1", "s", $semesterCode);

if(!isNewSemester($conn, $semesterCode, $seasons))
    echoError($conn, 400, "SemesterOutdated");
    
if($coursesForSemester == null && $semesterExists == null){
    
    if($newSemesterStart == null)
        echoError($conn, 400, "MissingArgumentSemesterStart");
    
    if($newSemesterEnd == null)
        echoError($conn, 400, "MissingArgumentSemesterEnd");
           
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
    
    if($newMarchBreakStart != null){
        if(strtotime($newMarchBreakStart) < strtotime($newSemesterStart))
            echoError($conn, 400, "MarchBreakStartNotValid");
    }
    
    if($newMarchBreakEnd != null){
        if(strtotime($newMarchBreakEnd) > strtotime($newSemesterEnd))
            echoError($conn, 400, "MarchBreakEndNotValid");
    }
    
    $insertTypes = "ssssss";
    $insertVals = array($semesterCode, $newSemesterStart, $newSemesterEnd, $newMarchBreakStart, $newMarchBreakEnd, $created);
        
    // INSERT the semester into the DB
    database_insert($conn, "INSERT INTO semesters (semester_code, semester_start, semester_end, march_break_start, march_break_end, created) VALUES (?,?,?,?,?,?)", $insertTypes, $insertVals);
} else {
    if($newSemesterStart != null || $newSemesterEnd != null || $newMarchBreakStart != null || $newMarchBreakEnd != null)
        echoError($conn, 400, "SemesterExists");
}

function isNewSemester($conn, $semesterCode, $seasons){
    $existingSemesters = database_get_all($conn, "SELECT semester_code FROM semesters ORDER BY created DESC", "", array());
    if(count($existingSemesters) == 0)
        return true;
        
    foreach($existingSemesters as $existingSemester){
        if($existingSemester["semester_code"] == $semesterCode)
            return true;
    }
    
    $latestSemesterCode = $existingSemesters[0]["semester_code"];
    
    $latestSeason = $latestSemesterCode[0];
    $latestYear = substr($latestSemesterCode, 1);
    
    $season = $semesterCode[0];
    $year = substr($semesterCode, 1);
    
    if(intval($year) < intval($latestYear))
        return false;
        
    if(intval($year) == intval($latestYear)){
        $seasonKey = array_search($season, $seasons);
        $latestSeasonKey = array_search($latestSeason, $seasons);
        
        if($seasonKey < $latestSeasonKey)
            return false;
    }
    
    return true;
}

?>