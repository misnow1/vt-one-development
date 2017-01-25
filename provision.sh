yum install -y http://rpms.famillecollet.com/enterprise/remi-release-6.rpm

yum install --enablerepo=remi -y httpd mysql mysql-server php-mbstring php-cli php php-pear php-pspell php-mysql php-xml php-process php-xmlrpc php-common php-pdo php-pecl-memcache php-gd php-devel php-fpm

cp -f /vagrant/vt-one.org.conf /etc/httpd/conf.d/
cp -f /usr/share/doc/php-common-*/php.ini-development /etc/php.ini

sed -r -e 's/^;?date.timezone(.*)$/date.timezone=America\/New_York/' -i /etc/php.ini
service httpd restart
service mysqld start
chkconfig httpd on
chkconfig mysqld on

DB_NAME=$(grep DB_NAME /var/www/vhosts/vt-one.org/htdocs/wp/wp-config.php | cut -d\' -f4)
DB_USER=$(grep DB_USER /var/www/vhosts/vt-one.org/htdocs/wp/wp-config.php | cut -d\' -f4)
DB_PASSWORD=$(grep DB_PASSWORD /var/www/vhosts/vt-one.org/htdocs/wp/wp-config.php | cut -d\' -f4)

echo "Database name: $DB_NAME"
echo "Database user: $DB_USER"
echo "Database password: $DB_PASSWORD"

mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME"
mysql -e "GRANT ALL ON $DB_NAME.* TO $DB_USER@localhost IDENTIFIED BY '$DB_PASSWORD'"

echo "127.0.0.1 mysql.vt-one.org" >> /etc/hosts

cp /vagrant/.htaccess /var/www/vhosts/vt-one.org/htdocs/
cp /var/www/vhosts/vt-one.org/htdocs/wp/index.php /var/www/vhosts/vt-one.org/htdocs/
sed -e "s_'/wp-blog-header.php'_'/wp/wp-blog-header.php'_" -i /var/www/vhosts/vt-one.org/htdocs/index.php

