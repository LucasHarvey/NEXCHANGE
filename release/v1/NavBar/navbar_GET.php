<?php

function getNavbarItems($conn, $token = null){
    
    if(!$token) 
        $token = getAuthToken();
    
    $user_id = getUserFromToken($token);

    if(getUserPrivilege($token) == "ADMIN"){
        $signup = array(
            "content" => "SIGN UP NEW USER",
            "url" => "./signup"
        );
        
        $userAccess = array(
            "content" => "GRANT USER ACCESS",
            "url" => "./userAccess"
        );
        
        $addCourse = array(
            "content" => "SEMESTER DETAILS",
            "url" => "./semesterDetails"
        );
        
        $manageNotes = array(
            "content" => "MANAGE SYSTEM",
            "url" => "./manage"
        );
            
        $settings = array(
            "content" => "MY PROFILE",
            "url" => "./profile"
        );
        
        return array($signup, $userAccess, $addCourse, $manageNotes, $settings);
    }
    
    $userAccesses = database_get_all($conn, "SELECT role FROM user_access WHERE user_id=?", "s", $user_id);
    
    $baseNavigation = array();
    
    array_push($baseNavigation, array(
        "content" => "HOME",
        "url" => "./home"
    ));
    
    foreach ($userAccesses as $item){
        if($item["role"] == "NOTETAKER"){
            array_push($baseNavigation, array(
                "content" => "UPLOAD NOTES",
                "url" => "./upload"
            ));
            break;
        }
    }
    
     array_push($baseNavigation, array(
        "content" => "MY COURSES",
        "url" => "./courses"
    ));
    
    array_push($baseNavigation, array(
        "content" => "MY PROFILE",
        "url" => "./profile"
    ));
    
    return $baseNavigation;
}

?>
  

