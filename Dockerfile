FROM php-host

COPY . .

RUN echo "APP_ENV=prod" > .env

CMD ["./bin/run.sh"]
