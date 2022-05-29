#!/bin/bash
BASE_DIR=$(pwd)
VERSION=$1
BASE_NAME='rapaygo-for-woocommerce'
rm $BASE_DIR/$BASE_NAME*.zip
zip -r $BASE_NAME-$VERSION.zip $BASE_DIR/rapaygo-for-woocommerce
cp $BASE_NAME-$VERSION.zip $BASE_NAME.zip