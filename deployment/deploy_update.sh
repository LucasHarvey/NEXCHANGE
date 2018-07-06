#!/bin/bash
echo "Part 1 -- Cleaning New Files";
cd ../release;
cp login.html index.html
sed -i -e 's/getenv("IP")/"localhost"/g' v1/_database.php
sed -i -e 's/getenv("C9_USER")/"root"/g' v1/_database.php
sed -i -e 's/$password = "";/$password = "THE_PASSWORD";/g' v1/_database.php
rm -rf v1/Files;
rm -rf v1/CoursesCSV;

echo "Part 2 -- Deleting Previous Files";
mkdir /home/nexuser/_BACKUP
cp -r /var/www/html/v1/CoursesCSV /home/nexuser/_BACKUP/
cp -r /var/www/html/v1/Files /home/nexuser/_BACKUP/
rm -rf /var/www/html/*

echo "Part 3 -- Moving New Files";
cp * /var/www/html
cp -r /home/nexuser/_BACKUP/CoursesCSV /var/www/html/v1
cp -r /home/nexuser/_BACKUP/Files /var/www/html/v1

echo "Part 4 -- Changing Permissions";
cd /var/www/html
chown -R apache:apache *
restorecon -R *
chcon -R -t httpd_sys_rw_content_t v1/CoursesCSV
chcon -R -t httpd_sys_rw_content_t v1/Files
chcon -R -t httpd_sys_rw_content_t v1/Admin

echo "Part 5 -- Updating passwords";
echo "Manual update required. See change_passwords.sh";