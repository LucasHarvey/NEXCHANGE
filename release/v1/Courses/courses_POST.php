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

$allowed = ['csv'];
$MAX_SINGLE_FILE_SIZE = 2 * 1024 * 1024; //2 mb
validateUploadedFiles($conn, $allowed, $MAX_SINGLE_FILE_SIZE);

if(!empty($_FILES['file'])){
	
	// Check if there is only one file
	if(count($_FILES['file']['name']) == 1){
	    $file = $_FILES['file'];
	    
	    var_dump($file);
	    die();
	}else{
	    echoError($conn, 400, "OneFileAllowed");
	}
}

?>