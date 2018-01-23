use nexchange;

LOAD DATA LOCAL INFILE "/home/ubuntu/workspace/deployment/database/latest_courses.csv"
    IGNORE 
    INTO TABLE courses
    FIELDS TERMINATED BY ';'
    (id, teacher_fullname, course_name, course_number, section_start, section_end, semester);