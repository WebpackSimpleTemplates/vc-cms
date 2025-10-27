#!/bin/bash

php bin/console cache:clear

php bin/console d:m:m --no-interaction

php bin/console lexik:jwt:generate-keypair --skip-if-exists

mkdir public/uploads
chmod 777 public/uploads

apache2ctl -D FOREGROUND
