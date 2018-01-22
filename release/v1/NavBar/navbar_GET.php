<?php

function getNavbarItems($conn, $token = null){
    
    if(!$token) 
        $token = getAuthToken();
    
    $user_id = getUserFromToken($token);

    if(getUserPrivilege($token) == "ADMIN"){
        $signup = array(
            "content" => "SIGN UP NEW USER",
            "url" => "./signup.html"
        );
        
        $userAccess = array(
            "content" => "GRANT USER ACCESS",
            "url" => "./userAccess.html"
        );
        
        $addCourse = array(
            "content" => "ADD COURSES",
            "url" => "./addCourses.html"
            );
        
        $manageNotes = array(
            "content" => "MANAGE SYSTEM",
            "url" => "./manage.html"
        );
        
        return array($signup, $userAccess, $addCourse, $manageNotes);
    }
    
    $userAccesses = database_get_all($conn, "SELECT role FROM user_access WHERE user_id=?", "s", $user_id);
    
    $baseNavigation = array();
    
    array_push($baseNavigation, array(
        "content" => "HOME",
        "url" => "./home.html"
    ));
    
    foreach ($userAccesses as $item){
        if($item["role"] == "NOTETAKER"){
            array_push($baseNavigation, array(
                "content" => "UPLOAD NOTES",
                "url" => "./upload.html"
            ));
            break;
        }
    }
    
     array_push($baseNavigation, array(
        "content" => "MY COURSES",
        "url" => "./courses.html"
    ));
    
    array_push($baseNavigation, array(
        "content" => "MY PROFILE",
        "url" => "./settings.html"
    ));
    
    return $baseNavigation;
}

?>
  

