#!/bin/bash

#docker run -ti -v $(pwd)/code:/var/event_sourcing vpa/event_sourcing:1.0 bash
docker run -v $(pwd)/code:/var/event_sourcing vpa/event_sourcing:1.0