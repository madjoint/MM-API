#!/bin/bash
cd /Applications/MAMP/htdocs/m/rest.4/db/
echo importing all tables from database_dump.sql
/Applications/MAMP/Library/bin/mysql -uroot -proot match_server < database_dump.sql
