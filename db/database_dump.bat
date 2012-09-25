cd c:\xampp\htdocs\m\rest.4\db\

@echo dumping all tables to database_dump.sql
@c:\xampp\mysql\bin\mysqldump -uroot -proot match_server interest location match message queue_matches queue_messages thread user  > database_dump.sql
@pause
