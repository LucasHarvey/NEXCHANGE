use nexchange;

LOAD DATA INFILE '/home/ubuntu/workspace/deployment/upload.csv' 
    IGNORE 
    INTO TABLE courses 
    FIELDS TERMINATED BY ';' ;