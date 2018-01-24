<? php

/* Common code for uploading note files*/

// Allowed file types
$allowed = ['pdf','docx', 'doc', 'ppt', 'xlsx', 'jpeg', 'jpg', 'png', 'txt', 'zip'];

//Max file size
$MAX_SINGLE_FILE_SIZE = 2 * 1024 * 1024; //2 mb

$succeeded = array();

//Verify all note extensions are allowed and file size is appropriate
validateUploadedFiles($conn, $allowed, $MAX_SINGLE_FILE_SIZE);

function moveFiles(){
	
	if(!empty($_FILES['file'])){
		
		// Check if there is only one file
		if(count($_FILES['file']['name']) == 1){
			
			// Put the files in a variable for easy access
			$files = $_FILES['file'];
			
			foreach($files['name'] as $key => $name){
				
				$tmp = $files['tmp_name'][$key];
				$fileName = $files['name'][$key];
				$fileSize = $files['size'][$key];
				$fileType = $files['type'][$key];
				$md5 = md5_file($files['tmp_name'][$key]);
	    		
	    		// Generate a unique file name for storage using the file content
	    		// If the file happens to already exist, change the unique id until it doesn't
	    		do{
	    		    $storageName =  uniqid() . getExtension($fileName);
	    		}while(file_exists("./Files/".$storageName));
				
				// Add the proper directory
				$storageName = "./Files/" . $storageName;
				
				// Move the temporary file to the Files folder
			    move_uploaded_file($tmp, $storageName);
			    
			    // Ensure that file name is 100 characters or less
			    if(strlen($fileName) > 100){
		            $ext = getExtension($fileName);
		            // Calculate the amount of characters left excluding the extension
		            $lenLeft = 100 - strlen($ext);
		            // Rename the file by truncating the file name such that strlen($fileName . $ext) = 100
		            $fileName = substr($fileName,0,$lenLeft) . $ext;
			    }
			    
			    // Add the name and md5 of the file to the succeeded array (using the original name)
			    array_push($succeeded, array($fileName, $md5));
			    
				$noteFileData = array($fileName, $storageName, $fileType, $fileSize, $md5);
			
				return $noteFileData;
				
			}
		}
		
		// Check if there is more than one file 
		if(count($_FILES['file']['name']) > 1){
			
			// Put the files in a variable for easy access
			$files = $_FILES['file'];
			
			//Create a zip object
			$zip = new ZipArchive;
	
			// Generate a unique file name for storage using the file content
	    	// If the file happens to already exist, change the unique id until it doesn't
			do{
			    $storageName = uniqid().".zip";
			}while(file_exists("./Files/".$storageName));
			
			// Keep the same name to insert into db before modifiying $storageName with rel path
			$fileName = $storageName;
			
			// Add the proper directory (added here because we need to add the uuid in front of $storageName)
			$storageName = "./Files/" . $storageName;
			
			if ($zip->open($storageName, ZipArchive::CREATE) === TRUE){
			    
			    // Add files to the zip file
			    foreach($files['name'] as $key => $name){
			    	
			    	$tmp = $files['tmp_name'][$key];
			    	$md5 = md5_file($tmp);
			    	
			    	// Add the file to the zip with its original name within the zip
			    	$zip->addFile($tmp, $name);
			    	
			    	// Add the name and md5 of the file to the succeeded array (using the original name)
			    	array_push($succeeded, array($name, $md5));
			    }
			 
			 
			    // All files are added, so close the zip file
			    $zip->close();
			   
			}
				
			// Get the file size of the zip
			$fileSize = filesize($storageName);
			$fileType = "zip";
			$md5 = md5_file($storageName);
			
			$noteFileData = array($fileName, $storageName, $fileType, $fileSize, $md5);
			
			return $noteFileData;
				
		}
	} 
}

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

function insertNoteFile($conn, $noteId, $fileName, $storageName, $fileType, $fileSize, $md5){
	$insertTypes = "ssssis";
	$insertValues = array($noteId,$fileName,$storageName,$fileType,$fileSize,$md5);

	return database_insert($conn, 
		"INSERT INTO notefiles (note_id, file_name, storage_name, type, size, md5) VALUES (?,?,?,?,?,?)",
		$insertTypes, $insertValues, false);
}

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

function validateUploadedFiles($conn, $allowed, $MAX_SINGLE_FILE_SIZE){
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
