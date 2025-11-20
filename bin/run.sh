#!/bin/bash

php bin/console cache:clear

php bin/console d:m:m --no-interaction

php bin/console lexik:jwt:generate-keypair --skip-if-exists

mkdir public/uploads
chmod 777 public/uploads

sed -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 30M/g' /etc/php/8.4/apache2/php.ini

echo "post_max_size=30M" >> /etc/php/8.4/apache2/php.ini
echo "memory_limit=30M" >> /etc/php/8.4/apache2/php.ini

apache2ctl -D FOREGROUND
