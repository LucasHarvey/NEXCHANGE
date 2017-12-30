<?php

$conn = database_connect();

requiredParams($conn, $_POST, array("noteName", "courseId", "takenOn"));

$allowed = ['pdf','docx', 'doc', 'pptx', 'ppt', 'xlsx', 'jpeg', 'jpg', 'png', 'txt', 'zip'];
$MAX_SINGLE_FILE_SIZE = 5 * 1024 * 1024; //2 mb

$user_id = getUserFromToken($conn);
if(getUserPrivilege($conn, $user_id) == "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

if(empty($_FILES['file'])){
    echoError($conn, 400, "NoFilesUploaded");
}

$course_id = $_POST["courseId"];
$noteName = $_POST['noteName'];
$description = $_POST['description'];
$date = $_POST['takenOn'];

$noteTypes = "sssss";
$noteValues = array($user_id,$course_id,$noteName,$description,$date);

// Ensure that the user is a note taker for the course
$row = database_get_row($conn, "SELECT role FROM user_access WHERE user_id=? AND course_id=? AND role='NOTETAKER'", "ss", array($user_id, $course_id));
if(is_null($row)){
	echoError($conn, 403, "UserCreateNotesDenied");
}

//Verify all note extensions are allowed and file size is appropriate
validateUploadedFiles($allowed, $MAX_SINGLE_FILE_SIZE);

if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseInsertError", "Could not start transaction.");
}

database_insert($conn, "INSERT INTO notes (user_id, course_id, name, description, taken_on) VALUES (?,?,?,?,?)", $noteTypes, $noteValues);
$note = database_get_row($conn, 
	"SELECT id FROM notes WHERE user_id=? AND course_id=? ORDER BY created DESC LIMIT 1",
	 "ss", array($user_id, $course_id));
if($note == null){
	echoError($conn, 500, "DatabaseInsertError");
}


/* FILE UPLOAD SECTION */

if(!empty($_FILES['file'])){
		
	$failed = array();
	$succeeded = array();
	
	// Check if there is only one file
	if(count($_FILES['file']['name']) == 1){
		
		// Put the files in a variable for easy access
		$files = $_FILES['file'];
		
		foreach($files['name'] as $key => $name){
			// Ensure that there is no error for the file
			if($files['error'][$key] != 0) {
			    array_push($failed, array(
		        	"name" => $name,
		        	"messageCode" => "UnknownFileUploadError",
		        	"status" => 500
		    	));
			    continue;
			}
			
			$tmp = $files['tmp_name'][$key];
			$fileName = $files['name'][$key];
			$fileSize = $files['size'][$key];
			$fileType = $files['type'][$key];
			$md5 = md5_file($files['tmp_name'][$key]);
			
			// Generate a unique file name for storage using the file content
			$storageName =  uniqid() . getExtension($fileName);
			
			// If the file happens to already exist, add a unique id until it doesn't
			while(file_exists("./Files/".$storageName)){
				$storageName =  uniqid() . getExtension($fileName);
			}
			
			// Add the proper directory
			$storageName = "./Files/" . $storageName;
			
			// Move the temporary file to the Files folder
		    move_uploaded_file($tmp, $storageName);
		    
		    // Ensure that file name is 100 characters or less
		    if(strlen($fileName) > 100){
		    	$fileDotSeparated = explode('.', $fileName); //MUST be on 2 lines.
	            $ext = "." . strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
	            // Calculate the amount of characters left excluding the extension
	            $lenLeft = 100 - strlen($ext);
	            // Rename the file by truncating the file name such that strlen($fileName . $ext) = 100
	            $fileName = substr($fileName,0,$lenLeft) . $ext;
		    }
			
			// Insert the file information into the database
			$result = insertNoteFile($conn, $note["id"], $fileName, $storageName, $fileType, $fileSize, $md5);
			
			if($result){
				array_push($succeeded, array(
		    	    "name" => $fileName,
		    	    "md5" => $md5
		    	));
			}else{
		    	array_push($failed, array(
		        	"name" => $fileName,
		        	"messageCode" => "DatabaseInsertError",
		        	"status" => 500
		    	));
			}
		}
	}
	
	// Check if there is more than one file 
	if(count($_FILES['file']['name']) > 1){
		
		// Put the files in a variable for easy access
		$files = $_FILES['file'];
		
		// Array of files to be deleted
		$deleteFiles = array();
		
		// Array of uploaded files
		$uploadedFiles = array();
		
		//Create a zip object
		$zip = new ZipArchive;
	
		// Name the zip file using a uuid (this may seem sketch to the user)
		$storageName = uniqid().".zip";
		
		// If the file happens to already exists, add a unique id until it doesn't
		while(file_exists("./Files/".$storageName)){
			$storageName = uniqid().".zip";
		}
		
		// Keep the same name to insert into db before modifiying $storageName with rel path
		$fileName = $storageName;
		
		// Add the proper directory (added here because we need to add the uuid in front of $storageName)
		$storageName = "./Files/" . $storageName;
		
		if ($zip->open($storageName, ZipArchive::CREATE) === TRUE){
		    
		    // Add files to the zip file
		    foreach($files['name'] as $key => $name){
		    	// Look for errors in the file before adding it to the zip
				if($files['error'][$key] != 0) {
				    array_push($failed, array(
			        	"name" => $name,
			        	"messageCode" => "UnknownFileUploadError",
			        	"status" => 500
			    	));
				    continue;
				}
		    	
		    	$tmp = $files['tmp_name'][$key];
		    	$md5 = md5_file($tmp);
		    	
		    	// Add the file to the zip with its original name within the zip
		    	$zip->addFile($tmp, $name);
		    	
		    	// Add the name and md5 of the file to the succeeded array (using the original name)
		    	array_push($uploadedFiles, array($name, $md5));
		    }
		 
		 
		    // All files are added, so close the zip file
		    $zip->close();
		   
		}
			
		// Get the file size of the zip
		$fileSize = filesize($storageName);
		$fileType = "zip";
		$md5 = md5_file($storageName);
		
		// Insert the file information into the database
		$result = insertNoteFile($conn, $note["id"], $fileName, $storageName, $fileType, $fileSize, $md5);
		
		if($result){
			
			// Loop through the files to add them to $succeeded
			foreach($uploadedFiles as $file){
				
				// Add the file to $succeeded
				array_push($succeeded, array(
	    	    "name" => $file[0],
	    	    // The file was moved at line 111 (from tmp_name to name)
	    	    "md5" => $file[1]
	    		));
			}
	    	
		} else {
			foreach($uploadedFiles as $file){
				// Files already in $failed will not be in $uploadedFiles
				array_push($failed, array(
		        	"name" => $file[0],
		        	"messageCode" => "DatabaseInsertError",
		        	"status" => 500
		    	));	
			}
		}
	}
}

/* END OF FILE UPLOAD SECTION */

if(!database_commit($conn)){
	if(!database_rollback($conn)){
		$GLOBALS['NEXCHANGE_TRANSACTION'] = false;
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

echoSuccess($conn, array(
	'succeeded' => $succeeded,
	'failed' => $failed
), 207);


function insertNoteFile($conn, $noteId, $fileName, $storageName, $fileType, $fileSize, $md5){
	$insertTypes = "ssssis";
	$insertValues = array($noteId,$fileName,$storageName,$fileType,$fileSize,$md5);

	return database_insert($conn, 
		"INSERT INTO notefiles (note_id, file_name, storage_name, type, size, md5) VALUES (?,?,?,?,?,?)",
		$insertTypes, $insertValues, false);
}

function validateUploadedFiles($allowed, $MAX_SINGLE_FILE_SIZE){
    foreach($_FILES["file"]["name"] as $key => $name){
        if($_FILES['file']['error'][$key] == 0) {
            $fileDotSeparated = explode('.', $name); //MUST be on 2 lines.
            $ext = strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
            if(!in_array($ext, $allowed)){
            	echoError($conn, 409, "NoteExtensionUnauthorized");
            }
            
            if($_FILES['file']['size'][$key] > $MAX_SINGLE_FILE_SIZE){
                echoError($conn, 409, "FileIsTooBig");
            }
        }else{
            echoError($conn, 500, "UnknownFileUploadError");
        }
    }
}

function getExtension($fileName){
	$fileDotSeparated = explode('.', $fileName); //MUST be on 2 lines.
    $ext = strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
    
    return ".".$ext;
}

?>