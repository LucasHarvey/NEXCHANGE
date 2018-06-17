<?php


/* Common code for uploading note files*/

function moveFiles(){
	
	$succeeded = array();
	
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
				$fileExtension = getExtension($fileName, true);
				$md5 = md5_file($files['tmp_name'][$key]);
	    		
	    		// Generate a unique file name for storage using the file content
	    		// If the file happens to already exist, change the unique id until it doesn't
	    		do{
	    		    $storageName =  uniqid() . getExtension($fileName);
	    		}while(file_exists($GLOBALS['REL_FILES_PATH'].$storageName));
				
				// Add the proper directory
				$relPath = $GLOBALS['REL_FILES_PATH'] . $storageName;
				
				// Move the temporary file to the Files folder
			    move_uploaded_file($tmp, $relPath);
			
			    // Ensure that file name is 100 characters or less
			    if(strlen($fileName) > 100){
		            $ext = getExtension($fileName);
		            // Calculate the amount of characters left excluding the extension
		            $lenLeft = 100 - strlen($ext);
		            // Rename the file by truncating the file name such that strlen($fileName . $ext) = 100
		            $fileName = substr($fileName,0,$lenLeft) . $ext;
			    }
			    
			    cleanFileName($fileName);
			    
			    // Add the name and md5 of the file to the succeeded array (using the original name)
			    array_push($succeeded, array(
			    	"name" => $fileName, 
			    	"md5" => $md5));
			    
				$noteFileData = array($fileName, $storageName, $fileType, $fileExtension, $fileSize, $md5, $succeeded);
			
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
			}while(file_exists($GLOBALS['REL_FILES_PATH'].$storageName));
			
			// Use the name of the first file as the note name for the zip
			$fileName = $files['name'][0];
			
			// Cut the name to proper size to avoid extension being truncated
			if(strlen($fileName) > 100){
		            $ext = getExtension($fileName);
		            // Calculate the amount of characters left excluding the extension
		            $lenLeft = 100 - strlen($ext);
		            // Rename the file by truncating the file name such that strlen($fileName . $ext) = 100
		            $fileName = substr($fileName,0,$lenLeft) . $ext;
			}
			
			// Clean the name to remove forbidden characters
			$fileName = cleanFileName($fileName, true);
			
			// Add the proper directory 
			$relPath = $GLOBALS['REL_FILES_PATH'] . $storageName;
			
			if ($zip->open($relPath, ZipArchive::CREATE) === TRUE){
			    
			    // Add files to the zip file
			    foreach($files['name'] as $key => $name){
			    	
			    	$tmp = $files['tmp_name'][$key];
			    	$md5 = md5_file($tmp);
			    	
			    	// Add the file to the zip with its original name within the zip
			    	$zip->addFile($tmp, $name);
			    	
				    // Add the name and md5 of the file to the succeeded array (using the original name)
				    array_push($succeeded, array(
				    	"name" => $name, 
				    	"md5" => $md5));
			    }
			 
			 
			    // All files are added, so close the zip file
			    $zip->close();
			   
			}
				
			// Get the file size of the zip
			$fileSize = filesize($relPath);
			$fileType = "application/zip";
			$fileExtension = "zip";
			$md5 = md5_file($relPath);
			
			$noteFileData = array($fileName, $storageName, $fileType, $fileExtension, $fileSize, $md5, $succeeded);
			
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

function insertNoteFile($conn, $noteId, $fileName, $storageName, $fileType, $fileExtension, $fileSize, $md5){
	$insertTypes = "sssssis";
	$insertValues = array($noteId,$fileName,$storageName,$fileType,$fileExtension, $fileSize,$md5);

	return database_insert($conn, 
		"INSERT INTO notefiles (note_id, file_name, storage_name, type, extension, size, md5) VALUES (?,?,?,?,?,?,?)",
		$insertTypes, $insertValues, true);
}

function retrieveStorageName($conn, $noteId){
	// Get the storage_name of the old file
    $oldFile = database_get_row($conn,
        "SELECT storage_name FROM notefiles WHERE note_id=?", 
        "s", $noteId);
        
    return $oldFile['storage_name'];
	
}

function updateNoteFile($conn, $noteId, $fileName, $storageName, $fileType, $fileExtension, $fileSize, $md5){
 
	$insertTypes = "ssssiss";
	$insertValues = array($fileName,$storageName,$fileType, $fileExtension, $fileSize,$md5,$noteId);
	
	return  database_update($conn,
	    "UPDATE notefiles SET file_name=?, storage_name=?, type=?, extension=?, size=?, md5=? WHERE note_id=? LIMIT 1",
		$insertTypes, $insertValues, true);
}

function validateUploadedFiles($conn){
    foreach($_FILES["file"]["name"] as $key => $name){
        if($_FILES['file']['error'][$key] == 0) {
            $fileDotSeparated = explode('.', $name); //MUST be on 2 lines.
            $ext = strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
            if(!in_array($ext, $GLOBALS['ALLOWED_FILE_EXTENSIONS'])){
            	echoError($conn, 409, "NoteExtensionUnauthorized");
            }
            
            if($_FILES['file']['size'][$key] > $GLOBALS['MAX_SINGLE_FILE_SIZE']){
                echoError($conn, 409, "FileIsTooBig");
            }
        }else{
        	$err = getFileError($_FILES['file']['error'][$key]);
        	echoError($conn, $err[0], $err[1]);
        }
    }
}

function getExtension($fileName, $withoutPeriod=false){
	$fileDotSeparated = explode('.', $fileName); //MUST be on 2 lines.
    $ext = strtolower(end($fileDotSeparated)); //MUST be on 2 lines.
    
    if($withoutPeriod) return $ext;
    
    return ".".$ext;
}

function cleanFileName($fileName, $zip=false){
	$fileDotSeparated = explode('.', $fileName); 
    $newFileName = "";
	for($i=0;$i<(count($fileDotSeparated)-1); $i++){
		// Use the name of the first file as the zip name
		$newFileName .= preg_replace("/[^a-zA-Z0-9._]+/", "", $fileDotSeparated[$i]);
	}
	if($zip){
		return $newFileName .= ".zip";
	}
	return $newFileName . strtolower(end($fileDotSeparated)); 
}

function deleteFile($storageName){
	if(file_exists($GLOBALS['REL_FILES_PATH'].$storageName)){
		if(unlink($GLOBALS['REL_FILES_PATH'].$storageName)) return true;
	}
	
	return false;
}


?>
