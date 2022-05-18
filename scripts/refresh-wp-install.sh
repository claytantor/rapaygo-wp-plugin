
##!/bin/bash
TMP_DIR=/home/clay/tmp
PWD_DIR=$(pwd)
TS=$(date "+%s")

mkdir $TMP_DIR/wp1-$TS

mv db_data $TMP_DIR/wp1-$TS/.
mkdir $PWD_DIR/db_data
chmod -R guo+rwx db_data
docker volume create --driver local \
    --opt type=none \
    --opt device=$PWD_DIR/db_data \
    --opt o=bind db_data

mv wordpress_data $TMP_DIR/wp1-$TS/.
mkdir $PWD_DIR/wordpress_data
chmod -R guo+rwx $PWD_DIR/wordpress_data
docker volume create --driver local \
    --opt type=none \
    --opt device=$PWD_DIR/wordpress_data \
    --opt o=bind wordpress_data