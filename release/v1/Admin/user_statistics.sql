SELECT login_id, first_name, last_name, email, created, 
    IFNULL(ua.roles, "N/A") as roles, 
    IFNULL(ua.courses, "N/A") as courses, 
    IFNULL(logins.logins_per_week, "N/A") as logins_per_week, 
    IFNULL(nt.rems_sent, "N/A") as NT_reminders_sent,
    IFNULL(nt_notes.notes_uploaded, "N/A") as NT_notes_uploaded,
    IF(LOCATE("STUDENT", ua.roles) > 0, 
        CONCAT(CONCAT(
            IFNULL(st_downs.totalDownloads, 0), "/"), 
            IFNULL(st_avail.availableNotes, 0)
    ), "N/A") as ST_downloads
    
    FROM users u 
    
    LEFT JOIN (
        SELECT user_id, GROUP_CONCAT(DISTINCT role SEPARATOR ' and ') as roles,
                GROUP_CONCAT(DISTINCT c.course_number SEPARATOR ';') as courses
        FROM user_access ua INNER JOIN courses c ON ua.course_id=c.id 
        GROUP BY user_id
    ) as ua ON u.id = ua.user_id
    
    LEFT JOIN (
        SELECT lu.user_id, COUNT(*)/ROUND(DATEDIFF(curdate(), 
                                    (SELECT created FROM user_access ua 
                                        WHERE ua.user_id = lu.user_id 
                                        ORDER BY created ASC LIMIT 1)
                                )/7, 0) AS logins_per_week
        FROM log_user_logins lu
        GROUP BY lu.user_id
    ) as logins ON u.id=logins.user_id
    
    LEFT JOIN (
        SELECT ln.user_id, ln.notification_code, COUNT(*) as rems_sent
        FROM log_notifications_sent ln
        GROUP BY ln.user_id, notification_code
        HAVING notification_code IN (2, 12)
    ) as nt ON u.id=nt.user_id
    
    LEFT JOIN (
        SELECT user_id, COUNT(*) as notes_uploaded
        FROM notes 
        GROUP BY user_id
    ) as nt_notes ON u.id=nt_notes.user_id
    
    LEFT JOIN (
        SELECT user_id, COUNT(DISTINCT notefile_id) as totalDownloads
        FROM notefile_downloads nfd
        GROUP BY user_id
    ) as st_downs ON u.id=st_downs.user_id
    
    LEFT JOIN (
        SELECT ua.user_id, COUNT(*) as availableNotes
        FROM user_access ua INNER JOIN notes n ON n.course_id=ua.course_id
        GROUP BY ua.user_id
    ) as st_avail ON u.id=st_avail.user_id
    
    WHERE privilege = "USER";