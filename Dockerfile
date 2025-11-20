FROM php-host

COPY vendor vendor

COPY translations translations
COPY composer.json composer.json
COPY composer.lock composer.lock
COPY importmap.php importmap.php
COPY phpunit.dist.xml phpunit.dist.xml
COPY symfony.lock symfony.lock
COPY public public
COPY bin bin
COPY config config
COPY migrations migrations
COPY templates templates
COPY src src

RUN echo "APP_ENV=prod" > .env

CMD ["./bin/run.sh"]
