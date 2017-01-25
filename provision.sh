DOCUMENT_ROOT="/var/www/vhosts/vt-one.org/htdocs"
WP_CONFIG="${DOCUMENT_ROOT}/wp/wp-config.php"

echo "Installing packages"
yum install -y http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
yum install --enablerepo=remi -y httpd mysql mysql-server php-mbstring php-cli php php-pear php-pspell php-mysql php-xml php-process php-xmlrpc php-common php-pdo php-pecl-memcache php-gd php-devel php-fpm

# Configure PHP for development and set the default time zone to America/New York
echo "Configuring PHP..."
cp -f /usr/share/doc/php-common-*/php.ini-development /etc/php.ini
sed -r -e 's/^;?date.timezone(.*)$/date.timezone=America\/New_York/' -i /etc/php.ini

# Disable Sendfile support in httpd (it is broken with vboxfs)
echo "Configuring httpd..."
sed 's/#EnableSendfile off/EnableSendfile off/' -i /etc/httpd/conf/httpd.conf

cat >/etc/httpd/conf.d/vt-one.org.conf <<EOF
EnableSendfile Off
<VirtualHost _default_:80>
      ServerName www-master1.vt-one.org
      ServerAlias vt-one.com
      ServerAlias vt-one.net
      ServerAlias vt-one.info
      ServerAlias vt-one.org

      ServerAdmin webmaster@vt-one.org

      DocumentRoot /var/www/vhosts/vt-one.org/htdocs

      ErrorLog /var/log/httpd/vt-one.org-error.log
      CustomLog /var/log/httpd/vt-one.org-access.log common

      <Directory /var/www/vhosts/vt-one.org/htdocs>
        AllowOverride All
        Options -Indexes

        Order allow,deny
        Allow from all
      </Directory>

      RewriteEngine On
      RewriteCond %{HTTP_HOST} ^(www)?\.vt-one\.(com|net|info)$
      RewriteRule ^(.*)$ http://vt-one.org\$1 [L,R=301]

    </VirtualHost>
EOF

# Restart all the things!
echo "Restarting services..."
service httpd restart
service mysqld start
chkconfig httpd on
chkconfig mysqld on

echo "Configuring database..."
DB_NAME=$(grep DB_NAME ${WP_CONFIG} | cut -d\' -f4)
DB_USER=$(grep DB_USER ${WP_CONFIG} | cut -d\' -f4)
DB_PASSWORD=$(grep DB_PASSWORD ${WP_CONFIG} | cut -d\' -f4)

echo "  Database: ${DB_NAME}"
echo "  User:     ${DB_USER}"
echo "  Password: ${DB_PASSWORD}"

mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME}"
mysql -e "GRANT ALL ON ${DB_NAME}.* TO ${DB_USER}@localhost IDENTIFIED BY '${DB_PASSWORD}'"

echo "127.0.0.1 mysql.vt-one.org" >> /etc/hosts

cp -f ${DOCUMENT_ROOT}/wp/index.php ${DOCUMENT_ROOT}/index.php
sed -e "s_'/wp-blog-header.php'_'/wp/wp-blog-header.php'_" -i ${DOCUMENT_ROOT}/index.php

# Import the most recent database backup found in the sql Directory
BACKUP=$(ls -1t /vagrant/sql/*.bz2 | head -n1)
if [ ! -z "$BACKUP" ]; then
    echo "Sleeping while SQL server starts up..."
    sleep 2

    echo "Restoring SQL backup from ${BACKUP}..."
    bzip2 -dc ${BACKUP} | sed 's_http://vt-one.org_http://localhost:8080_' | mysql ${DB_NAME}

    echo "Correcting site URL..."
    mysql ${DB_NAME} -e "UPDATE wp_options SET option_value='http://localhost:8080/wp' WHERE option_name='siteurl';"
    mysql ${DB_NAME} -e "UPDATE wp_options SET option_value='http://localhost:8080' WHERE option_name='home';"
fi
