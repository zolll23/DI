#!/bin/sh

docker run -v $(pwd)/code:/var/di vpa/di:1.0 php vendor/bin/psalm
docker run -v $(pwd)/code:/var/di vpa/di:1.0 php vendor/bin/phpunit