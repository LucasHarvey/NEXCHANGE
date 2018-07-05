use nexchange;

-- DROP EVERYTHING
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS semesters;
DROP TABLE IF EXISTS log_notifications_sent;
DROP TABLE IF EXISTS login_attempts;
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

DROP FUNCTION IF EXISTS getLastClassForgotten;

-- Create the tables
CREATE TABLE semesters (
    semester_code VARCHAR(5) NOT NULL,
    semester_start DATE NOT NULL,
    semester_end DATE NOT NULL,
    march_break_start DATE DEFAULT NULL,
    march_break_end DATE DEFAULT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

-- Create the tables
CREATE TABLE log_notifications_sent (
    user_id CHAR(36) NOT NULL,
    notification_code INT(2), -- 1: notify students; 2: notify notetakers (reminder); 3: reset password; 4: temporary password, +10 for maybe (async)
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE login_attempts (
    user_id CHAR(36) NOT NULL,
    ip_address VARCHAR (45),
    attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE log_user_logins (
    user_id CHAR(36) NOT NULL,
    ip_address VARCHAR(45),
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE courses (
    id CHAR(36) NOT NULL,
    teacher_fullname VARCHAR(255) NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    course_number VARCHAR(10) NOT NULL,
    section VARCHAR(255),
    semester VARCHAR(5) NOT NULL,
    
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT UC_Course UNIQUE (teacher_fullname,course_name,course_number,section,semester)
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
    extension VARCHAR(4) NOT NULL,
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

DELIMITER $$
CREATE FUNCTION getLastClassForgotten(courseId CHAR(36), userId CHAR(36), dateDiffAllowed INT(2))
    RETURNS DATE
    READS SQL DATA
    NOT DETERMINISTIC
BEGIN
    DECLARE lastNote DATE;
    DECLARE courseDaysOfWeek CHAR(7);
    DECLARE loop_data_dateCode CHAR(1);
    DECLARE loop_date DATE;
    DECLARE day VARCHAR(2);
    DECLARE month VARCHAR(2);
    DECLARE year VARCHAR (4);
    DECLARE season CHAR(1);
    DECLARE semesterCode VARCHAR(5);
    DECLARE semesterStart DATE;
    DECLARE semesterEnd DATE;
    DECLARE marchBreakStart DATE;
    DECLARE marchBreakEnd DATE;
    
    SELECT semester_code INTO semesterCode FROM semesters WHERE NOW() BETWEEN semester_start AND semester_end ORDER BY created DESC LIMIT 1;
    
    /*Constants: 
        Get the date of the last note, 
        Get the all days of classes
    */
    SELECT DATE(created) INTO lastNote FROM notes WHERE course_id = courseId AND user_id = userId ORDER BY created DESC limit 1;
    SELECT GROUP_CONCAT(days_of_week SEPARATOR '') INTO courseDaysOfWeek FROM course_times GROUP BY course_id HAVING course_id = courseId;
    
    SELECT semester_start INTO semesterStart FROM semesters WHERE semester_code=semesterCode;
    SELECT semester_end INTO semesterEnd FROM semesters WHERE semester_code=semesterCode;
    
    IF semester_start = NULL THEN 
        SIGNAL SQLSTATE '22004' SET MESSAGE_TEXT = "The semester start date cannot be null.";
    END IF;
    
    IF semester_end = NULL THEN 
        SIGNAL SQLSTATE '22004' SET MESSAGE_TEXT = "The semester end date cannot be null.";
    END IF;
    
    SELECT march_break_start INTO marchBreakStart FROM semesters WHERE semester_code=semesterCode;
    SELECT march_break_end INTO marchBreakEnd FROM semesters WHERE semester_code=semesterCode;
    
    /*Did the user never upload a note? If so set it to date user access was created*/
    IF (lastNote IS NULL) THEN
        SELECT DATE(created) INTO lastNote FROM user_access WHERE course_id = courseId AND user_id = userId;
    END IF;

    /*Start counting from the next day of the last note.*/
    SET loop_date = DATE_ADD(lastNote, INTERVAL 1 DAY);
    lookForClass: WHILE DATEDIFF(DATE(NOW()), loop_date) > dateDiffAllowed DO
        SELECT ELT(DAYOFWEEK(loop_date), "U", "M", "T", "W", "R", "F", "S") INTO loop_data_dateCode;
        
        /*Is the loop date before the semester started?*/
        IF(loop_date < semesterStart) THEN 
            ITERATE lookForClass;
        END IF;
        
        /*Is the current date at least one week before the semester ends?*/
        IF(NOW() > DATE_SUB(semesterEnd, INTERVAL 7 DAY)) THEN
            ITERATE lookForClass;
        END IF;
        
        /*Is the loop date during the March break?*/
        IF (marchBreakStart != NULL && marchBreakEnd != NULL) THEN
            IF(loop_date BETWEEN marchBreakStart AND marchBreakEnd) THEN
                ITERATE lookForClass;
            END IF;
        END IF;
        
        IF (LOCATE(loop_data_dateCode, courseDaysOfWeek) > 0) THEN
            RETURN loop_date;
        END IF;
        
        SET loop_date = DATE_ADD(loop_date, INTERVAL 1 DAY);
    END WHILE lookForClass;
    
    RETURN NULL;
END$$
DELIMITER ;
