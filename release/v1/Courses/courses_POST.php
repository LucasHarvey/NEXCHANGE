<?php
$conn = database_connect();

// Verify that the user is an admin
if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

if(empty($_FILES['file'])){
    echoError($conn, 400, "NoFilesUploaded");
}
$semesterCode = $_POST["semesterCode"];
//TODO VALIDATE SEMESTER CODE

$allowed = ['csv'];
$MAX_SINGLE_FILE_SIZE = 2 * 1024 * 1024; //2 mb
validateUploadedFiles($conn, $allowed, $MAX_SINGLE_FILE_SIZE);

if(count($_FILES['file']['name']) != 1){
	echoError($conn, 400, "OneFileAllowed");
}

$file = $_FILES['file'];
$tmp = $file['tmp_name'][0];

$errorNo = $file['error'][0];
if($errorNo != 0) {
	$err = getFileError($errorNo);
    echoError($conn, $err[0], $err[1], "Error: ".$errorNo);
}
    
$fileName = "courses-".$semesterCode."-".uniqid().".csv";

$storageName = "./CoursesCSV/".$fileName;
move_uploaded_file($tmp, $storageName);

$base = "/home/ubuntu/workspace/";
$script = $base."deployment/parse_courses.rb";
$fileIn = $base."release/v1/".$storageName;
$fileOut = $base."deployment/database/latest_courses.csv";

exec('ruby '.$script." ".$fileIn.' '.$fileOut.' '.$semesterCode.' 2>&1', $output, $returnValue);
if($returnValue != 0){
	echoError($conn, 500, "ErrorParsingCourseFile", "O: ".implode(",", $output)." --R:".$returnValue);
}

$uploadSQL = $base."deployment/database/upload_courses.sql";
exec('mysql --local-infile -uzacky1 --password= nexchange < '.$uploadSQL, $output2, $returnValue2);
if($returnValue2 != 0){
	echoError($conn, 500, "ErrorUploadingParsedCourseFile", "O: ".implode(",", $output2)." --R:".$returnValue2);
}

echoSuccess($conn, array("output" => $output[0]), 200);

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

function validateUploadedFiles($conn, $allowed, $MAX_SINGLE_FILE_SIZE){
    foreach($_FILES["file"]["name"] as $key => $name){
        if($_FILES['file']['error'][$key] == 0) {
            $fileDotSeparated = explode('.', $name); //MUST be on 2 lines.
            $ext = strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
            if(!in_array($ext, $allowed)){
            	echoError($conn, 409, "CourseExtensionUnauthorized");
            }
            
            if($_FILES['file']['size'][$key] > $MAX_SINGLE_FILE_SIZE){
                echoError($conn, 409, "FileIsTooBig");
            }
        }else{
        	$err = getFileError($_FILES['file']['error'][$key]);
        	echoError($conn, $err[0], $err[1]);
        }
    }
}

function getExtension($fileName){
	$fileDotSeparated = explode('.', $fileName); //MUST be on 2 lines.
    $ext = strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
    
    return ".".$ext;
}

?>