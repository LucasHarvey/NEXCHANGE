source deployment/database/schema.sql

-- ADD THE ADMIN USER
-- Password is: adminpass
INSERT INTO users (login_id, privilege, first_name, last_name, passwordhash, last_login) 
    VALUES ("Admin01", "ADMIN", "Administrator", "Administrator", "$2y$10$O27XHDCMtLTGDebeNSG2M.LghLnAtlB1FKc4oEAipPJeEWORpX/S.", NOW());
    
INSERT INTO semester_dates (semester_start, semester_end, march_break_start, march_break_end) 
    VALUES (null, null, null, null);
