#!/bin/bash

mkdir db_data
docker volume create --driver local \
    --opt type=none \
    --opt device=$(pwd)/db_data \
    --opt o=bind db_data

mkdir wordpress_data
docker volume create --driver local \
    --opt type=none \
    --opt device=$(pwd)/wordpress_data \
    --opt o=bind wordpress_data
    