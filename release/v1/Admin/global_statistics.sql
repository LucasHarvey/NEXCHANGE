/*Number of users*/
SELECT COUNT(*) as count_users FROM users GROUP BY privilege HAVING privilege="USER";
/*Number of notetakers: */
SELECT COUNT(*) AS count_notetakers FROM (SELECT COUNT(*) AS count FROM user_access WHERE role="NOTETAKER" GROUP BY user_id) AS T;
/*Number of students: */
SELECT COUNT(*) AS count_students FROM (SELECT COUNT(*) AS count FROM user_access WHERE role="STUDENT" GROUP BY user_id) AS T;
/*Number of admins*/
SELECT COUNT(*) AS count_admins FROM users GROUP BY privilege HAVING privilege="ADMIN";

/*Login frequency of admins*/
SELECT count(*) AS admin_login_count, ROUND((COUNT(*)
        / (SELECT COUNT(*) AS total 
            FROM log_user_logins lu INNER JOIN users u ON lu.user_id=u.id
            GROUP BY u.privilege HAVING u.privilege="ADMIN")
        ) * 100, 2) AS percent_of_total_admin_logins, 
        DATE_ADD(login_at, INTERVAL(1-DAYOFWEEK(login_at)) DAY) AS start_of_week,
        DATE_ADD(login_at, INTERVAL(7-DAYOFWEEK(login_at)) DAY) AS end_of_week
    FROM log_user_logins lu INNER JOIN users u ON lu.user_id=u.id
    WHERE u.privilege="ADMIN"
    GROUP BY week(login_at);

/*Login frequency of USERS*/
SELECT count(*) AS user_login_count, 
        ROUND((COUNT(*)
        / (SELECT COUNT(*) AS total 
            FROM log_user_logins lu INNER JOIN users u ON lu.user_id=u.id
            GROUP BY u.privilege HAVING u.privilege="USER")
        ) * 100, 2) AS percent_of_total_user_logins, 
        DATE_ADD(login_at, INTERVAL(1-DAYOFWEEK(login_at)) DAY) AS start_of_week,
        DATE_ADD(login_at, INTERVAL(7-DAYOFWEEK(login_at)) DAY) AS end_of_week
    FROM log_user_logins lu INNER JOIN users u ON lu.user_id=u.id
    WHERE u.privilege="USER"
    GROUP BY week(login_at);

/*Login frequency of STUDENTS*/
SELECT count(*) AS student_total_login, 
        ROUND((COUNT(*) / (SELECT COUNT(*) AS totalStudents
            FROM log_user_logins lu
            WHERE lu.user_id IN (SELECT user_id FROM user_access WHERE role="STUDENT"))
        ) * 100, 2) AS percent_of_total_student_logins, 
        ROUND((COUNT(*) / (SELECT COUNT(*) AS totalUsers
            FROM log_user_logins lu INNER JOIN users u ON lu.user_id=u.id
            WHERE privilege="USER")
        ) * 100, 2) AS percent_of_total_user_logins,
        DATE_ADD(login_at, INTERVAL(1-DAYOFWEEK(login_at)) DAY) AS start_of_week,
        DATE_ADD(login_at, INTERVAL(7-DAYOFWEEK(login_at)) DAY) AS end_of_week
    FROM log_user_logins lu
    WHERE lu.user_id IN (SELECT user_id FROM user_access WHERE role="STUDENT")
    GROUP BY week(login_at);
    
SELECT count(*) AS notetaker_total_login, 
        ROUND((COUNT(*) / (SELECT COUNT(*) AS totalNotetakers
            FROM log_user_logins lu
            WHERE lu.user_id IN (SELECT user_id FROM user_access WHERE role="NOTETAKER"))
        ) * 100, 2) AS percent_of_total_notetaker_logins, 
        ROUND((COUNT(*) / (SELECT COUNT(*) AS totalUsers
            FROM log_user_logins lu INNER JOIN users u ON lu.user_id=u.id
            WHERE privilege="USER")
        ) * 100, 2) AS percent_of_total_user_logins,
        DATE_ADD(login_at, INTERVAL(1-DAYOFWEEK(login_at)) DAY) AS start_of_week,
        DATE_ADD(login_at, INTERVAL(7-DAYOFWEEK(login_at)) DAY) AS end_of_week
    FROM log_user_logins lu
    WHERE lu.user_id IN (SELECT user_id FROM user_access WHERE role="NOTETAKER")
    GROUP BY week(login_at);

/*Notification of note upload per week*/
SELECT COUNT(*) AS note_upload_notifications_per_week, 
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent 
    WHERE notification_code=11 AND user_id NOT IN (SELECT id FROM users WHERE login_id LIKE 'Admin0%')
    GROUP BY week(sent_at);
   
/*Notification of note reminder per week*/
SELECT COUNT(*) AS note_upload_reminders_per_week, 
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent 
    WHERE notification_code=2 AND user_id NOT IN (SELECT id FROM users WHERE login_id LIKE 'Admin0%')
    GROUP BY week(sent_at);

/*NOTETAKER: Login of user within x hours of note reminder sent*/
SELECT COUNT(*) AS notetaker_logins_within_1_hour_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln INNER JOIN log_user_logins lu ON ln.user_id = lu.user_id
    WHERE notification_code=2 AND HOUR(TIMEDIFF(ln.sent_at, lu.login_at)) < 1
    GROUP BY week(sent_at);
    
SELECT COUNT(*) AS notetaker_logins_within_6_hour_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln INNER JOIN log_user_logins lu ON ln.user_id = lu.user_id
    WHERE notification_code=2 AND HOUR(TIMEDIFF(ln.sent_at, lu.login_at)) < 6
    GROUP BY week(sent_at);
    
SELECT COUNT(*) AS notetaker_logins_within_12_hour_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln INNER JOIN log_user_logins lu ON ln.user_id = lu.user_id
    WHERE notification_code=2 AND HOUR(TIMEDIFF(ln.sent_at, lu.login_at)) < 12
    GROUP BY week(sent_at);

SELECT COUNT(*) AS notetaker_logins_within_24_hour_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln INNER JOIN log_user_logins lu ON ln.user_id = lu.user_id
    WHERE notification_code=2 AND HOUR(TIMEDIFF(ln.sent_at, lu.login_at)) < 24
    GROUP BY week(sent_at);
    

/*STUDENT: Login of user within x hours of note notification sent*/
SELECT COUNT(*) AS student_logins_within_1_hour_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln INNER JOIN log_user_logins lu ON ln.user_id = lu.user_id
    WHERE notification_code=11 AND HOUR(TIMEDIFF(ln.sent_at, lu.login_at)) < 1
    GROUP BY week(sent_at);
    
SELECT COUNT(*) AS student_logins_within_6_hour_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln INNER JOIN log_user_logins lu ON ln.user_id = lu.user_id
    WHERE notification_code=11 AND HOUR(TIMEDIFF(ln.sent_at, lu.login_at)) < 6
    GROUP BY week(sent_at);
    
SELECT COUNT(*) AS student_logins_within_12_hour_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln INNER JOIN log_user_logins lu ON ln.user_id = lu.user_id
    WHERE notification_code=11 AND HOUR(TIMEDIFF(ln.sent_at, lu.login_at)) < 12
    GROUP BY week(sent_at);

SELECT COUNT(*) AS student_logins_within_24_hour_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln INNER JOIN log_user_logins lu ON ln.user_id = lu.user_id
    WHERE notification_code=11 AND HOUR(TIMEDIFF(ln.sent_at, lu.login_at)) < 24
    GROUP BY week(sent_at);
    
/*NOTETAKER: X notetakers Posting notes within X hours of being reminded*/
SELECT COUNT(*) AS notetakers_posting_within_1_hour_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=2 AND 
        (SELECT count(*) FROM notes n 
            WHERE n.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, created)) < 1
            GROUP BY n.user_id) >= 1
    GROUP BY week(sent_at);
            
SELECT COUNT(*) AS notetakers_posting_within_6_hours_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=2 AND 
        (SELECT count(*) FROM notes n 
            WHERE n.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, created)) < 6
            GROUP BY n.user_id) >= 1
    GROUP BY week(sent_at);
            
SELECT COUNT(*) AS notetakers_posting_within_12_hours_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=2 AND 
        (SELECT count(*) FROM notes n 
            WHERE n.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, created)) < 12
            GROUP BY n.user_id) >= 1
    GROUP BY week(sent_at);
            
SELECT COUNT(*) AS notetakers_posting_within_24_hours_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=2 AND 
        (SELECT count(*) FROM notes n 
            WHERE n.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, created)) < 24
            GROUP BY n.user_id) >= 1
    GROUP BY week(sent_at);
    
SELECT COUNT(*) AS notetakers_posting_within_48_hours_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=2 AND 
        (SELECT count(*) FROM notes n 
            WHERE n.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, created)) < 48
            GROUP BY n.user_id) >= 1
    GROUP BY week(sent_at);
    
SELECT COUNT(*) AS notetakers_posting_within_1_week_of_reminder,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=2 AND 
        (SELECT count(*) FROM notes n 
            WHERE n.user_id=ln.user_id 
                AND DATEDIFF(ln.sent_at, created) <= 7
            GROUP BY n.user_id) >= 1
    GROUP BY week(sent_at);
    
/*Notes downloaded after x hours of being notified*/
SELECT COUNT(*) AS student_download_within_1_hour_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=11 AND 
        (SELECT count(*) FROM notefile_downloads nfd
            WHERE nfd.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, nfd.downloaded_at)) < 1
            GROUP BY nfd.user_id) >= 1
    GROUP BY week(sent_at);
    
SELECT COUNT(*) AS student_download_within_6_hours_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=11 AND 
        (SELECT count(*) FROM notefile_downloads nfd
            WHERE nfd.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, nfd.downloaded_at)) < 6
            GROUP BY nfd.user_id) >= 1
    GROUP BY week(sent_at);
    
SELECT COUNT(*) AS student_download_within_12_hours_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=11 AND 
        (SELECT count(*) FROM notefile_downloads nfd
            WHERE nfd.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, nfd.downloaded_at)) < 12
            GROUP BY nfd.user_id) >= 1
    GROUP BY week(sent_at);
    
SELECT COUNT(*) AS student_download_within_24_hours_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=11 AND 
        (SELECT count(*) FROM notefile_downloads nfd
            WHERE nfd.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, nfd.downloaded_at)) < 24
            GROUP BY nfd.user_id) >= 1
    GROUP BY week(sent_at);
    
    
SELECT COUNT(*) AS student_download_within_48_hours_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=11 AND 
        (SELECT count(*) FROM notefile_downloads nfd
            WHERE nfd.user_id=ln.user_id 
                AND HOUR(TIMEDIFF(ln.sent_at, nfd.downloaded_at)) < 48
            GROUP BY nfd.user_id) >= 1
    GROUP BY week(sent_at);

SELECT COUNT(*) AS student_download_within_1_week_of_notification,
        DATE_ADD(sent_at, INTERVAL(1-DAYOFWEEK(sent_at)) DAY) AS start_of_week,
        DATE_ADD(sent_at, INTERVAL(7-DAYOFWEEK(sent_at)) DAY) AS end_of_week
    FROM log_notifications_sent ln
    WHERE notification_code=11 AND 
        (SELECT count(*) FROM notefile_downloads nfd
            WHERE nfd.user_id=ln.user_id 
                AND DATEDIFF(ln.sent_at, nfd.downloaded_at) <= 7
            GROUP BY nfd.user_id) >= 1
    GROUP BY week(sent_at);
