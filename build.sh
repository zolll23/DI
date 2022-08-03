#!/bin/bash

docker build -f Dockerfile -t vpa/di:1.0 .
docker run -v $(pwd)/code:/var/di vpa/di:1.0 composer install