#!/bin/bash
cd /webroot/api/m/rest.4/db/
echo importing all tables from database_dump.sql
/usr/bin/mysql -urest_4 -pmismatch rest_4 < database_dump.sql
