source deployment/database/schema.sql

-- ADD THE ADMIN USER
INSERT INTO users (login_id, privilege, first_name, last_name, passwordhash, last_login) 
    VALUES ("Admin01", "ADMIN", "Administrator", "Administrator", "$2y$10$O27XHDCMtLTGDebeNSG2M.LghLnAtlB1FKc4oEAipPJeEWORpX/S.", NOW());
