#!/bin/bash

CONFIG=$1
SITE=$2
BACKUP=$3

if [ $# -ne 3 ]; then
	echo "Usage: $0 CONFIG SITE BACKUP_FILE"
	exit 1
fi

if [ -z "$SITE" ]; then
	echo "Please provide a site name"
	exit 1
fi

if [ -z "$BACKUP" ]; then
	echo "Please provide a backup file name"
	exit 2
fi

if [ ! -r "$CONFIG" ]; then
	echo "Unable to read config file $CONFIG"
	exit 1
fi

# Make the umask sane
umask 0027

DB_NAME=$(awk -F\' '/DB_NAME/ {print $4}' $CONFIG)
DB_USER=$(awk -F\' '/DB_USER/ {print $4}' $CONFIG)
DB_PASSWORD=$(awk -F\' '/DB_PASSWORD/ {print $4}' $CONFIG)
DB_HOST=$(awk -F\' '/DB_HOST/ {print $4}' $CONFIG)

echo "Reading database from $BACKUP into $DB_NAME"
BACKUPSQL=$(basename $BACKUP .gz)
gunzip -c $BACKUP > $BACKUPSQL
mysql5 -u $DB_USER -p$DB_PASSWORD -h $DB_HOST $DB_NAME < $BACKUPSQL

if [ $? -ne 0 ]; then
	echo "Backup failed."
	exit 1
fi
rm -f $BACKUPSQL

echo "Correcting site URL"
mysql -u $DB_USER -p$DB_PASSWORD -h $DB_HOST $DB_NAME -e "UPDATE wp_options SET option_value='http://$SITE/wp' WHERE option_name='siteurl';"
mysql -u $DB_USER -p$DB_PASSWORD -h $DB_HOST $DB_NAME -e "UPDATE wp_options SET option_value='http://$SITE' WHERE option_name='home';"
