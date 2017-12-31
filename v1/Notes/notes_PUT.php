<?php

$conn = database_connect();

$user_id = getUserFromToken($conn);
if(getUserPrivilege() == "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_POST, array("noteId"));
$noteId = $_POST["noteId"];

// Allowed file types
$allowed = ['pdf','docx', 'doc', 'ppt', 'xlsx', 'jpeg', 'jpg', 'png', 'txt', 'zip'];

//Max file size
$MAX_SINGLE_FILE_SIZE = 5 * 1024 * 1024;

// Check that the note exists
if(!database_contains($conn, "notes", $noteId)){
    echoError($conn, 404, "NoteNotFound");
}

// Check that the user is editing a note they posted
if(database_get_row($conn, "SELECT id FROM notes WHERE id=? AND user_id=?", "ss", array($noteId, $user_id)) == null){
    echoError($conn, 403, "AuthorizationFailed");
}

// Change the insert keys
$allowedProps = array("name", "description", "takenOn");
$changesKeysRemap = array("takenOn" => "taken_on");

$changes = array();
foreach($_POST as $key => $value ){
    if(in_array($key, $allowedProps)){
        if(in_array($key, array_keys($changesKeysRemap))){
            $key = $changesKeysRemap[$key];
        }
        $changes[$key] = $value;
    }
}

// Ensure that changes can be made
if(empty($changes) && empty($_FILES['file'])){ //No legal changes can be made
    echoError($conn, 400, "NoChangesToMake");
}

if(!database_start_transaction($conn)){
	echoError($conn, 500, "DatabaseInsertError", "Could not start transaction.");
}

if(!empty($changes)){
    $colNames = array();
    $insertTypes = "";
    $insertValues = array();
    
    // Prepare the insert query
    foreach($changes as $key => $value){
        $insertTypes = $insertTypes . "s";
        array_push($insertValues, $value);
        array_push($colNames, $key . "=?");
    }
    $cols = implode(",", $colNames);
    
    array_push($insertValues, $noteId);
    $insertTypes = $insertTypes . "s";
    
    // Update the note data in the notes table
    database_update($conn, "UPDATE notes SET $cols WHERE id=? LIMIT 1", $insertTypes, $insertValues);
}

// Get the updated note data
$note = database_get_row($conn, "SELECT id, name, description, taken_on, created FROM notes WHERE id=? LIMIT 1", "s", $noteId);

/* FILE UPDATE SECTION */
$failed = array();
$succeeded = array();

// Determine if the user wants to upload a new file
if(!empty($_FILES['file'])){
    // Verify all note extensions are allowed and file size is appropriate
    validateUploadedFiles($allowed, $MAX_SINGLE_FILE_SIZE);

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
    		$storageName = "";
    		
    		// Generate a unique file name for storage using the file content
    		// If the file happens to already exist, add a unique id until it doesn't
    		do{
    		    $storageName =  uniqid() . getExtension($fileName);
    		}while(file_exists("./Files/".$storageName));
    		
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
    		$result = updateNoteFile($conn, $noteId, $fileName, $storageName, $fileType, $fileSize, $md5);

    		
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
    	$result = insertNoteFile($conn, $noteId, $fileName, $storageName, $fileType, $fileSize, $md5);
    	
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

if(!database_commit($conn)){
	if(!database_rollback($conn)){
	    $GLOBALS["NEXCHANGE_TRANSACTION"] = false;
		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
	}
	echoError($conn, 500, "DatabaseCommitError", "Could not commit transaction.");
}

echoSuccess($conn, array(
    "messageCode" => "NoteUpdated",
    "note_id" => $noteId,
    "succeeded" => $succeeded,
	"failed" => $failed,
	"note" => $note
));


function updateNoteFile($conn, $noteId, $fileName, $storageName, $fileType, $fileSize, $md5){
    
    // Get the storage_name of the old file
    $oldFile = database_get_row($conn,
        "SELECT storage_name FROM notefiles WHERE note_id=?", 
        "s", array($noteId));
        
    $oldStorageName = $oldFile['storage_name'];
    
    // Ensure that the old file exists and delete it
	if(!file_exists($oldStorageName) || !unlink($oldStorageName)){
	    echoError($conn, 404, "NoteFileNotFound");
	} 
    
	$insertTypes = "sssiss";
	$insertValues = array($fileName,$storageName,$fileType,$fileSize,$md5,$noteId);
	
	return database_update($conn,
	    "UPDATE notefiles SET file_name=?, storage_name=?, type=?, size=?, md5=? WHERE note_id=? LIMIT 1",
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