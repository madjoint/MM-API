#!/bin/bash
cd /webroot/api/m/rest.4/db/
echo dumping all tables to database_dump.sql
/usr/bin/mysqldump -urest_4 -pmismatch rest_4 interest location match message queue_matches queue_messages thread user mem_sql_profile > database_dump.sql
