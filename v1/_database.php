<?php
function database_connect(){
    /***********************************/
    //Open the requested connection
    /***********************************/
    $GLOBALS['NEXCHANGE_TRANSACTION'] = false;
    
    $servername = getenv("IP");
    $username = getenv("C9_USER");
    $password = "";
    $dbport = "3306";
    $database = "nexchange";
    
    $dbh = new mysqli("p:".$servername, $username, $password, $database, $dbport);
    if($dbh->connect_error){
        echoError(null, 500, "DatabaseConnectError");
    }
    return $dbh;
}

function SqlArrayReferenceValues($arr){
    //Reference is required for PHP 5.3+
    if (strnatcmp(phpversion(),'5.3') >= 0) {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
 }

/*
    $dbh => Return of database_connect()
 */
function database_prepare($dbh, $_query, $types, $params){
    try{
        if($query = $dbh->prepare($_query)){
            if(!is_array($params)){
                $query->bind_param($types, $params);
                return $query;
            }

            if(count($params) != substr_count($_query, "?")){
                echoError($dbh, 500, "DatabasePrepError", "Param and query vars dont match -- Query: " . $_query);
            }
            
            if(count($params) != 0 && strlen($types) != 0){
                array_unshift($params, $types);
                call_user_func_array(array($query, 'bind_param'), SqlArrayReferenceValues($params));
            }
            
            return $query;
        }
        $error = $dbh->error;
        echoError($dbh, 500, "DatabasePrepError", $error);
    }catch(Exception $e){
        $error = $dbh->error;
        echoError($dbh, 500, "DatabaseError", $error);
    }
}

/*
    $queryHandle is the handle returned by a $databaseObj->prepare 
*/
function database_execute_no_fetch($queryHandle){
    if($queryHandle){
        if(!$queryHandle->execute()){
            database_handle_error($queryHandle);
            return;
        }
        return;
    }
    
    echoError($dbh, 500, "DatabaseExecuteError");
}

/*
    $queryHandle is the handle returned by a $databaseObj->prepare 
*/
function database_execute_single($queryHandle){
    if($queryHandle){
        if(!$queryHandle->execute()){
            database_handle_error($queryHandle);
            return;
        }
        
        $result = $queryHandle->get_result();
        
        if($result->num_rows > 0){
            return $result->fetch_assoc();
        }
        return null; //Query returned no results.
    }
    
    echoError($dbh, 500, "DatabaseExecuteError");
}

/*
    $queryHandle is the handle returned by a $databaseObj->prepare 
*/
function database_execute_fetch_all($queryHandle){
    if($queryHandle){
        if(!$queryHandle->execute()){
            database_handle_error($queryHandle);
            return;
        }
        
        $result = $queryHandle->get_result();
        
        $finalResult = array();
        while($row = $result->fetch_assoc()){
            array_push($finalResult, $row);
        }
        return $finalResult;
    }
    
    echoError($dbh, 500, "DatabaseExecuteError");
}

function database_handle_error($queryHandle){
    switch($queryHandle->sqlstate){
        case 23000:
            echoError($queryHandle, 400, "DatabaseDuplicationError");
            break;
        case 45001:
            echoError($queryHandle, 400, "UserCreateNotesDenied");
            break;
        case 45002:
            echoError($queryHandle, 409, "UserAlreadyRegisteredInCourse");
            break;
        default:
            throw new Exception($queryHandle->error);
    }
}

function database_start_transaction($dbh, $readOnly = false){
    $GLOBALS['NEXCHANGE_TRANSACTION'] = true;
    if($readOnly){
        return $dbh->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);
    }
    return $dbh->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
}

function database_commit($dbh){
    $GLOBALS['NEXCHANGE_TRANSACTION'] = false;
    return $dbh->commit();
}
function database_rollback($dbh){
    $GLOBALS['NEXCHANGE_TRANSACTION'] = false;
    return $dbh->rollback();
}

/* CONVENIENCES */
function database_execute($dbh, $query, $types, $params, $throw = true){
    $stmt = database_prepare($dbh, $query, $types, $params);
    try{
        database_execute_no_fetch($stmt);
        $stmt->close();
        if(!$throw){
            return true;
        }
    }catch(Exception $e){
        $stmt->close();
        if($throw){
            $error = $dbh->error;
            echoError($dbh, 500, "DatabaseError", $error); //Most likely a bad request. TBD
        }
        return false;
    }
}

function database_insert($dbh, $query, $types, $params, $throw = true){
    $stmt = database_prepare($dbh, $query, $types, $params);
    try{
        database_execute_no_fetch($stmt);
        $stmt->close();
        if(!$throw){
            return true;
        }
    }catch(Exception $e){
        $stmt->close();
        if($throw){
            $error = $dbh->error;
            echoError($dbh, 500, "DatabaseInsertError", $error); //Most likely a bad request. TBD
        }
        return false;
    }
}

function database_update($dbh, $query, $types, $params, $throw = true){
    $stmt = database_prepare($dbh, $query, $types, $params);
    try{
        database_execute_no_fetch($stmt);
        $stmt->close();
        if(!$throw){
            return true;
        }
    }catch(Exception $e){
        $stmt->close();
        if($throw){
            $error = $dbh->error;
            echoError($dbh, 500, "DatabaseUpdateError", $error); //Most likely a bad request. TBD
        }
        return false;
    }
}

function database_delete($dbh, $query, $types, $params, $throw = false){
    $stmt = database_prepare($dbh, $query, $types, $params);
    try{
        database_execute_no_fetch($stmt);
        $stmt->close();
        
        if($throw){
            return true;
        }
    }catch(Exception $e){
        $stmt->close();
        $error = $dbh->error;
        if($throw){
            return $error;
        }
        echoError($dbh, 500, "DatabaseDeleteError", $error); //Most likely a bad request. TBD
    }
}

function database_get_row($dbh, $query, $types, $params){
    $stmt = database_prepare($dbh, $query, $types, $params);
    try{
        $value = database_execute_single($stmt);
        $stmt->close();
        return $value;
    }catch(Exception $e){
        $stmt->close();
        $error = $dbh->error;
        echoError($dbh, 500, "DatabaseSelectError", $error); //Most likely a bad request. TBD
    }
}

function database_get_all($dbh, $query, $types, $params){
    $stmt = database_prepare($dbh, $query, $types, $params);
    try{
        $value = database_execute_fetch_all($stmt);
        $stmt->close();
        return $value;
    }catch(Exception $e){
        $stmt->close();
        $error = $dbh->error;
        echoError($dbh, 500, "DatabaseSelectError", $error); //Most likely a bad request. TBD
    }
}

function database_contains($dbh, $tableName, $idBind){
    $query = "SELECT id FROM $tableName WHERE id=?";
    $result = database_get_row($dbh, $query, "s", array($idBind));
    return $result != null;
}

?>

