
//List of logins
select first_name, login_id, login_at from users u inner join log_user_logins lu on u.id=lu.user_id where privilege!='ADMIN';

select lu.user_id, lu.login_at, ln.notification_code, ln.sent_at 
    from log_user_logins lu 
        inner join log_notifications_sent ln 
        on lu.user_id=ln.user_id;