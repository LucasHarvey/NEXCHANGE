<?php

function validatePassword($conn, $userId, $password){
    $user = database_get_row($conn, "SELECT passwordhash FROM users WHERE id=?", "s", $userId);
    if(!password_verify($password, $user["passwordhash"]))
        echoError($conn, 401, "AuthenticationFailed");
}

function validateSemester($conn, $semesterCode, $seasons){
    if($semesterCode == "")
	    echoError($conn, 400, "MissingArgumentSemesterCode");
	
    if(!in_array($semesterCode[0], $seasons) || strlen($semesterCode) != 5)
        echoError($conn, 400, "SemesterNotValid");
    
    $year = substr($semesterCode, 1);
    
    if(!ctype_digit($year) || intval($year)<2000 || intval($year)>9999)
        echoError($conn, 400, "SemesterNotValid");
}

function validateFiles($conn, $files, $allowed, $MAX_FILE_SIZE){
    if(empty($files))
        echoError($conn, 400, "NoFilesUploaded");
        
    if(count($files['name']) != 1)
	    echoError($conn, 400, "OneFileAllowed");
       
    validateUploadedFiles($conn, $files, $allowed, $MAX_FILE_SIZE);
}

function validateUploadedFiles($conn, $files, $allowed, $MAX_SINGLE_FILE_SIZE){
    foreach($files["name"] as $key => $name){
        if($files['error'][$key] == 0) {
            $fileDotSeparated = explode('.', $name); //MUST be on 2 lines.
            $ext = strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
            if(!in_array($ext, $allowed)){
            	echoError($conn, 409, "CourseExtensionUnauthorized");
            }
            
            if($files['size'][$key] > $MAX_SINGLE_FILE_SIZE){
                echoError($conn, 409, "FileIsTooBig");
            }
        }else{
        	$err = getFileError($files['error'][$key]);
        	echoError($conn, $err[0], $err[1]);
        }
    }
}

?>