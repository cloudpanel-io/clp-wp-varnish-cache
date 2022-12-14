#!/bin/bash
VERSION='1.0.0'
TMP_DIRECTORY='tmp'
TMP_RELEASE_DIRECTORY='tmp/clp-varnish-cache/'
rm -rf $TMP_DIRECTORY
mkdir -p $TMP_RELEASE_DIRECTORY
cp -R ../* $TMP_RELEASE_DIRECTORY
rm -rf $TMP_RELEASE_DIRECTORY/bin/
rm -rf $TMP_RELEASE_DIRECTORY/release/
rm $TMP_RELEASE_DIRECTORY/languages/*.po~
cd $TMP_DIRECTORY
zip -r "clp-varnish-cache-$VERSION.zip" clp-varnish-cache
rm -rf clp-varnish-cache
