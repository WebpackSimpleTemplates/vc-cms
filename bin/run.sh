#!/bin/bash

php bin/console cache:clear

php bin/console d:m:m --no-interaction

apache2ctl -D FOREGROUND
