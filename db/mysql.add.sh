#!/bin/bash
if [ -z "$2" ]; then
   echo "Usage: mysql.add <username> <password>"
   exit
fi

echo -n "Enter MySQL root pass:"
read MYSQL_PASS

mysql -uroot -p${MYSQL_PASS} -e "create database ${1}" mysql
mysql -uroot -p${MYSQL_PASS} -e "grant select,insert,update,delete,create,drop,references,index,alter,create temporary tables, lock tables on ${1}.* to ${1}@localhost identified by '${2}'" mysql
mysql -uroot -p${MYSQL_PASS} -e "flush privileges" mysql
