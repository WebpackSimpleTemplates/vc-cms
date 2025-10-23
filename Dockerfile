FROM php-host

COPY vendor vendor

COPY . .

RUN echo "APP_ENV=prod" > .env

CMD ["./bin/run.sh"]
