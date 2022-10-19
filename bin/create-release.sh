#!/bin/bash
VERSION='0.0.4'
TMP_DIRECTORY='tmp'
TMP_RELEASE_DIRECTORY='tmp/clp-varnish-cache/'
rm -rf $TMP_DIRECTORY
mkdir -p $TMP_RELEASE_DIRECTORY
cp -R ../* $TMP_RELEASE_DIRECTORY
rm -rf $TMP_RELEASE_DIRECTORY/bin/
cd $TMP_DIRECTORY
zip -r "clp-varnish-cache-$VERSION.zip" clp-varnish-cache
rm -rf clp-varnish-cache