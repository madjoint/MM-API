#!/bin/bash
if [ -z "$1" ]; then
   echo "Usage: mysql.rm <username>"
   exit
fi

echo -n "Enter MySQL root pass:"
read MYSQL_PASS

mysql -uroot -p${MYSQL_PASS} -e "drop database ${1}" mysql
mysql -uroot -p${MYSQL_PASS} -e "delete from user where user='${1}'" mysql
mysql -uroot -p${MYSQL_PASS} -e "delete from db where user='${1}'" mysql
mysql -uroot -p${MYSQL_PASS} -e "flush privileges" mysql
