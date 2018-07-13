<?php

if(!is_resource($conn))
    $conn = database_connect();

if(!isset($userId))
    $userId = getUserFromToken();

if(getUserPrivilege() != "ADMIN")
    echoError($conn, 403, "AuthorizationFailed");

$password = $_POST["password"];
$password = base64_decode($password);

include_once "./Semester/semester_conveniences.php";
validatePassword($conn, $userId, $password);

$semesterCode = $_POST["semesterCode"];
$seasons = ["I", "W", "S", "F"];
if(!array_key_exists("newSemesterStart", $_POST)){
    $semesterExists = database_get_row($conn, "SELECT semester_code FROM semesters WHERE semester_code=? LIMIT 1", "s", $semesterCode);
    
    if($semesterExists == null)
        echoError($conn, 400, "AdditionalCoursesFailedDNE");
}

validateSemester($conn, $semesterCode, $seasons);

$allowed = ['csv'];
$files = $_FILES['file'];
validateFiles($conn, $files, $allowed, $GLOBALS['MAX_SINGLE_FILE_SIZE']);

$file = $_FILES['file'];
$tmp = $file['tmp_name'][0];

$errorNo = $file['error'][0];
if($errorNo != 0) {
	$err = getFileError($errorNo);
    echoError($conn, $err[0], $err[1], "Error: ".$errorNo);
}

// Generate a unique file name for storage using the file content
// If the file happens to already exist, change the unique id until it doesn't
do{
    $fileName = "courses-".$semesterCode."-".uniqid().".csv";
}while(file_exists("./CoursesCSV/".$fileName));

$storageName = "./CoursesCSV/".$fileName;
move_uploaded_file($tmp, $storageName);

$script = "./Courses/parse_courses.rb";
$fileIn = $storageName;
$fileOutCourses = "./CoursesCSV/courses.latest.csv";
$fileOutTimes = "./CoursesCSV/course_times.latest.csv";

exec('ruby '.$script." ".$fileIn.' '.$fileOutCourses.' '.$fileOutTimes.' '.$semesterCode.' 2>&1', $output, $returnValue);
if($returnValue != 0){
    echoError($conn, 400, "ErrorParsingCourseFile", "O: ".implode(",", $output)." --R:".$returnValue);
}

$execCMD = "mysqlimport --ignore --fields-terminated-by=';' --columns='id,teacher_fullname,course_name,course_number,section,semester' --local -uroot --password=THE_PASSWORD nexchange ".$fileOutCourses;
exec($execCMD, $output2, $returnValue2);
if($returnValue2 != 0){
    echoError($conn, 500, "ErrorUploadingParsedCourseFile", "O: ".implode(",", $output2)." --R:".$returnValue2);
}

$execCMD2 = "mysqlimport --ignore --fields-terminated-by=';' --columns='id,course_id, days_of_week, time_start, time_end' --local -uroot --password=THE_PASSWORD nexchange ".$fileOutTimes;
exec($execCMD2, $output3, $returnValue3);
if($returnValue3 != 0){
    echoError($conn, 500, "ErrorUploadingParsedCourseFile", "Course Times. O: ".implode(",", $output3)." --R:".$returnValue3);
}

echoSuccess($conn, array("output" => $output[0]), 201);

function getFileError($errorNo){
	switch ($errorNo) {
        case UPLOAD_ERR_OK:
        	return true;
        case UPLOAD_ERR_NO_FILE:
    		return array(400, "NoFilesUploaded");
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
        	return array(409, "FileIsTooBig");
        default:
        	return array(500, "UnknownFileUploadError");
    }
}

function getExtension($fileName){
	$fileDotSeparated = explode('.', $fileName); //MUST be on 2 lines.
    $ext = strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
    
    return ".".$ext;
}



?>