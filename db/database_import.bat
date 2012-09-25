cd c:\xampp\htdocs\m\rest.4\db\

@echo importing all tables from database_dump.sql
@c:\xampp\mysql\bin\mysql -uroot -proot match_server < database_dump.sql
@pause
