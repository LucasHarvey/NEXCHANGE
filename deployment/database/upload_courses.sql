use nexchange;

LOAD DATA LOCAL INFILE '/var/lib/mysql-files/upload_courses.csv' 
    IGNORE 
    INTO TABLE courses 
    FIELDS TERMINATED BY ';' ;