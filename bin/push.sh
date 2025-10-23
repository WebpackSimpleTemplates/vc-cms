#!/bin/bash

php bin/console cache:clear

docker build . -t vc-cms:v1
docker image tag vc-cms:v1 harbor.a-7.tech/weebee/vc-cms
docker push harbor.a-7.tech/weebee/vc-cms
