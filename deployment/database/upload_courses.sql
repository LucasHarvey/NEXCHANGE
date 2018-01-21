use nexchange;

LOAD DATA LOCAL INFILE "/home/ubuntu/workspace/deployment/database/latest_courses.csv"
    IGNORE 
    INTO TABLE courses
    FIELDS TERMINATED BY ';' ;