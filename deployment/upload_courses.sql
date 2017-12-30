--source /home/ubuntu/workspace/deployment/upload_courses.sql

use nexchange;

LOAD DATA INFILE '/var/lib/mysql-files/upload_courses.csv' 
    IGNORE 
    INTO TABLE courses 
    FIELDS TERMINATED BY ';' ;