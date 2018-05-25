use nexchange;

-- DROP EVERYTHING
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS log_notifications_sent;
DROP TABLE IF EXISTS log_user_logins;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS notefiles;
DROP TABLE IF EXISTS user_access;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS course_times;
DROP TABLE IF EXISTS notefile_downloads;
SET FOREIGN_KEY_CHECKS = 1;

DROP TRIGGER IF EXISTS before_insert_on_users_id;
DROP TRIGGER IF EXISTS before_insert_on_notefiles_id;
DROP TRIGGER IF EXISTS before_insert_on_courses_time_id;
DROP TRIGGER IF EXISTS before_insert_on_user_access;
DROP TRIGGER IF EXISTS before_insert_on_notes;

-- Create the tables
CREATE TABLE log_notifications_sent (
    user_id CHAR(36) NOT NULL,
    notification_code INT(2), -- 1: notify students; 2: notify notetakers (reminder); 3: reset password; 4: temporary password, +10 for maybe (async)
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE log_user_logins (
    user_id CHAR(36) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
    most_recent_token_IAT int(10) DEFAULT NULL,
    
    passresetcode CHAR(40) UNIQUE,
    passresetcreated TIMESTAMP,
    
    PRIMARY KEY (id)
);

CREATE TABLE courses (
    id CHAR(36) NOT NULL,
    teacher_fullname VARCHAR(255) NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    course_number VARCHAR(10) NOT NULL,
    section_start int(5),
    section_end int(5),
    semester VARCHAR(5) NOT NULL,
    
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT UC_Course UNIQUE (teacher_fullname,course_name,course_number,section_start,section_end,semester)
);

CREATE TABLE course_times (
    id CHAR(36) NOT NULL,
    course_id CHAR(36) NOT NULL,
    
    days_of_week CHAR(5) NOT NULL,
    time_start CHAR(4) NOT NULL,
    time_end CHAR(4) NOT NULL,
    
    PRIMARY KEY (id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
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
    type VARCHAR(350),
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

CREATE TRIGGER before_insert_on_courses_time_id
    BEFORE INSERT ON course_times 
    FOR EACH ROW SET new.id = uuid();

DELIMITER $$
CREATE TRIGGER before_insert_on_user_access
    BEFORE INSERT ON user_access
    FOR EACH ROW
    BEGIN
        IF new.role='NOTETAKER' THEN
            SET new.notifications = FALSE;
        END IF;
        
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
