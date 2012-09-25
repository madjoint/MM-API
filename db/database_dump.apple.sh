#!/bin/bash
cd /Applications/MAMP/htdocs/m/rest.4/db/
echo dumping all tables to database_dump.sql
/Applications/MAMP/Library/bin/mysqldump -uroot -proot match_server interest location match message push queue_matches queue_messages thread user  > database_dump.sql
