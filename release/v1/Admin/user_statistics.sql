SELECT login_id, first_name, last_name, email, u.created, 
    IFNULL(ua.roles, "N/A") as roles, 
    IFNULL(ua.courses, "N/A") as courses, 
    IFNULL(logins.logins_per_week, "N/A") as logins_per_week, 
    IFNULL(nt.rems_sent, "N/A") as NT_reminders_sent,
    IFNULL(nt_notes.notes_uploaded, "N/A") as NT_notes_uploaded,
    IF(LOCATE("STUDENT", ua.roles) > 0, 
        CONCAT(CONCAT(
            IFNULL(st_downs.totalDownloads, 0), " out of "),
            
            IFNULL(
                (SELECT COUNT(*) as ST_availableNotes 
                FROM user_access ua INNER JOIN notes n ON n.course_id=ua.course_id
                WHERE ua.user_id=u.id AND (n.user_id IS NULL OR n.user_id != u.id)
                AND 1=(SELECT ua.created BETWEEN _s.semester_start AND _s.semester_end FROM semesters _s ORDER BY _s.created DESC LIMIT 1)
                AND 1=(SELECT n.created BETWEEN _s.semester_start AND _s.semester_end FROM semesters _s ORDER BY _s.created DESC LIMIT 1)
                GROUP BY ua.user_id)
            , 0)
    ), "N/A") as ST_downloads
    
    FROM users u 

    LEFT JOIN (
        SELECT user_id, GROUP_CONCAT(DISTINCT role SEPARATOR ' and ') as roles,
                GROUP_CONCAT(DISTINCT c.course_number SEPARATOR ';') as courses
        FROM user_access ua INNER JOIN courses c ON ua.course_id=c.id 
        GROUP BY user_id
    ) as ua ON u.id = ua.user_id
    
    LEFT JOIN (
        SELECT lu.user_id, ROUND(COUNT(*)/ROUND(DATEDIFF(curdate(), 
                                    (SELECT created FROM user_access ua 
                                        WHERE ua.user_id = lu.user_id 
                                        ORDER BY created ASC LIMIT 1)
                                )/7, 0), 2) AS logins_per_week
        FROM log_user_logins lu
        WHERE 1=(SELECT lu.login_at BETWEEN _s.semester_start AND _s.semester_end FROM semesters _s ORDER BY _s.created DESC LIMIT 1)
        GROUP BY lu.user_id
    ) as logins ON u.id=logins.user_id
    
    LEFT JOIN (
        SELECT ln.user_id, ln.notification_code, COUNT(*) as rems_sent
        FROM log_notifications_sent ln
        WHERE 1=(SELECT ln.sent_at BETWEEN _s.semester_start AND _s.semester_end FROM semesters _s ORDER BY _s.created DESC LIMIT 1)
        GROUP BY ln.user_id, notification_code
        HAVING notification_code IN (2, 12)
    ) as nt ON u.id=nt.user_id
    
    LEFT JOIN (
        SELECT user_id, COUNT(*) as notes_uploaded
        FROM notes n
        WHERE 1=(SELECT n.created BETWEEN _s.semester_start AND _s.semester_end FROM semesters _s ORDER BY _s.created DESC LIMIT 1)
        GROUP BY user_id
    ) as nt_notes ON u.id=nt_notes.user_id
    
    LEFT JOIN (
        SELECT nfd.user_id, COUNT(DISTINCT notefile_id) as totalDownloads
        FROM notefile_downloads nfd
        WHERE nfd.user_id != (SELECT n.user_id FROM notes n INNER JOIN notefiles nf ON n.id=nf.note_id WHERE nf.id=nfd.notefile_id)
        AND 1=(SELECT nfd.downloaded_at BETWEEN _s.semester_start AND _s.semester_end FROM semesters _s ORDER BY _s.created DESC LIMIT 1)
        GROUP BY nfd.user_id
    ) as st_downs ON u.id=st_downs.user_id
    
    WHERE privilege = "USER";