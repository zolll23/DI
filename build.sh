#!/bin/bash

docker build -f Dockerfile -t vpa/event_sourcing:1.0 .
docker run -v $(pwd)/code:/var/event_sourcing vpa/event_sourcing:1.0 composer install