use nexchange;

-- DROP EVERYTHING
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS notefiles;
DROP TABLE IF EXISTS user_access;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS notefile_downloads;
SET FOREIGN_KEY_CHECKS = 1;

-- Create the tables
CREATE TABLE users (
    id CHAR(36) NOT NULL,
    login_id CHAR(7) NOT NULL UNIQUE,
    first_name NVARCHAR(40) NOT NULL,
    last_name NVARCHAR(60) NOT NULL,
    passwordhash CHAR(60) NOT NULL,
    email NVARCHAR(255),
    privilege ENUM("USER", "ADMIN") NOT NULL DEFAULT "USER",
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    all_tokens_expire_on DATETIME DEFAULT NULL,
    
    passresetcode CHAR(40) UNIQUE,
    passresetcreated TIMESTAMP,
    
    PRIMARY KEY (id)
);

CREATE TABLE courses (
    id CHAR(36) NOT NULL,
    teacher_fullname VARCHAR(255) NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    course_number VARCHAR(10) NOT NULL,
    section int(5),
    semester VARCHAR(5) NOT NULL,
    
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
);

-- User access denotes the courses a student or a note taker is allowed to do stuff for
CREATE TABLE user_access (
    user_id CHAR(36) NOT NULL,
    course_id CHAR(36) NOT NULL,
    role ENUM("STUDENT", "NOTETAKER") DEFAULT "STUDENT",
    notifications BOOL DEFAULT TRUE,
    
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_on DATE,
    
    PRIMARY KEY (user_id, course_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notes (
    id CHAR(36) NOT NULL,
    user_id CHAR(36), -- can be null if a user deletes his account, we still want to have old notes.
    course_id CHAR(36), -- can be null if the admin deletes the course, we still want to have the notes for the course.
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    name VARCHAR(60) NOT NULL,
    description VARCHAR(500),
    taken_on DATE NOT NULL,
    
    PRIMARY KEY (id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE notefiles (
    id CHAR(36) NOT NULL,
    note_id CHAR(36) NOT NULL,
    file_name VARCHAR(100) NOT NULL,
    storage_name VARCHAR(100) NOT NULL,
    type VARCHAR(30),
    size int(11),
    md5 CHAR(32) NOT NULL, -- MD5s are always 32 chars.

    PRIMARY KEY (id),
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
);

CREATE TABLE notefile_downloads (
    notefile_id CHAR(36) NOT NULL,
    user_id CHAR(36),
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (notefile_id) REFERENCES notefiles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)  ON DELETE CASCADE
);

-- TRIGGERS TO AUTOMATICALLY CREATE IDS
CREATE TRIGGER before_insert_on_users_id
    BEFORE INSERT ON users 
    FOR EACH ROW SET new.id = uuid();
  
CREATE TRIGGER before_insert_on_notefiles_id
    BEFORE INSERT ON notefiles 
    FOR EACH ROW SET new.id = uuid();
  
CREATE TRIGGER before_insert_on_courses_id
    BEFORE INSERT ON courses 
    FOR EACH ROW SET new.id = uuid();

DELIMITER $$
CREATE TRIGGER before_insert_on_user_access
    BEFORE INSERT ON user_access
    FOR EACH ROW
    BEGIN
        IF EXISTS (SELECT * FROM user_access WHERE user_id = new.user_id AND course_id = new.course_id) THEN
            SIGNAL SQLSTATE '45002' SET MESSAGE_TEXT = "This user is already signed up to be a notetaker or a student in this course.";
        END IF;
    END$$
DELIMITER ;


-- TRIGGERS TO AUTOMATICALLY CHECK IF A USER IS ALLOWED TO CREATE NOTES + create uuid
DELIMITER $$
CREATE TRIGGER before_insert_on_notes
    BEFORE INSERT ON notes
    FOR EACH ROW
    BEGIN
        IF EXISTS (SELECT * FROM user_access WHERE user_id = new.user_id AND course_id = new.course_id AND role="NOTETAKER" AND expires_on >= NOW()) THEN
          SET new.id = uuid();
        ELSE
            SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = "This user has not been granted rights to upload notes for this course.";
        END IF;
    END$$
DELIMITER ;
    
-- ADD THE ADMIN USER
-- Password is: adminpass
INSERT INTO users (login_id, privilege, first_name, last_name, passwordhash, last_login) 
    VALUES ("Admin01", "ADMIN", "Administrator", "Administrator", "$2y$10$O27XHDCMtLTGDebeNSG2M.LghLnAtlB1FKc4oEAipPJeEWORpX/S.", NOW());
    
source /home/ubuntu/workspace/deployment/upload_courses.sql
