#!/bin/bash
cd /var/www/m/rest/db/
echo importing all tables from database_dump.sql
/usr/bin/mysql -urest -pmismatch rest < database_dump.sql
