#!/bin/bash
cd /var/www/m/rest/db/
echo dumping all tables to database_dump.sql
/usr/bin/mysqldump -urest -pmismatch rest interest location match message queue_matches queue_messages thread user mem_sql_profile > database_dump.sql
