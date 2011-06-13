#!/bin/sh
if [ "$1" = "" -o "$2" = "" ]; then
  echo "Create symlinked directory for www version to given MediaMosa repository directory. This allows you to develop on MediaMosa while your MediaMosa repository and www are used differently and keeped clean. Will only link to required files, not to *.txt files etc."
  echo "usage: $0 MEDIAMOSA_DIR TARGET_DIR"
  exit
fi

if [ ! -f "$1/crossdomain.xml" ]; then
  echo "MediaMosa directory $1 must exist."
  exit 1;
fi

target=$2
mkdir $target

if [ ! -d "$target" ]; then
  echo "Target directory $target must exist."
  exit 1;
fi

ln -s $1/authorize.php $target/authorize.php
ln -s $1/cron.php $target/cron.php
ln -s $1/crossdomain.xml $target/crossdomain.xml
ln -s $1/.htaccess $target/.htaccess
ln -s $1/includes/ $target/includes
ln -s $1/index.php $target/index.php
ln -s $1/misc/ $target/misc
ln -s $1/install.php $target/install.php
ln -s $1/mmplayer/ $target/mmplayer
ln -s $1/modules/ $target/modules
ln -s $1/profiles/ $target/profiles
ln -s $1/robots.txt $target/robots.txt
ln -s $1/scripts/ $target/scripts
ln -s $1/still/ $target/still
ln -s $1/themes/ $target/themes
ln -s $1/update.php $target/update.php
ln -s $1/web.config $target/web.config
ln -s $1/xmlrpc.php $target/xmlrpc.php

mkdir $target/sites

mkdir $target/sites/default
ln -s $1/sites/all/ $target/sites/all
ln -s $1/sites/default/default.settings.php $target/sites/default/default.settings.php
cp $1/sites/default/default.settings.php $target/sites/default/settings.php

mkdir $target/sites/job1.mediamosa.local
ln -s $1/sites/job1.mediamosa.local/example.settings.php $target/sites/job1.mediamosa.local/settings.php
mkdir $target/sites/job2.mediamosa.local
ln -s $1/sites/job2.mediamosa.local/example.settings.php $target/sites/job2.mediamosa.local/settings.php
mkdir $target/sites/app1.mediamosa.local
ln -s $1/sites/app1.mediamosa.local/example.settings.php $target/sites/app1.mediamosa.local/settings.php
mkdir $target/sites/app2.mediamosa.local
ln -s $1/sites/app2.mediamosa.local/example.settings.php $target/sites/app2.mediamosa.local/settings.php
mkdir $target/sites/download.mediamosa.local
ln -s $1/sites/download.mediamosa.local/example.settings.php $target/sites/download.mediamosa.local/settings.php
mkdir $target/sites/upload.mediamosa.local
ln -s $1/sites/upload.mediamosa.local/example.settings.php $target/sites/upload.mediamosa.local/settings.php
mkdir $target/sites/openapi.mediamosa.local
ln -s $1/sites/openapi.mediamosa.local/example.settings.php $target/sites/openapi.mediamosa.local/settings.php

mkdir $target/sites/default/files
chmod +w $target/sites/default/files
mkdir $target/sites/app1.mediamosa.local/files
chmod +w $target/sites/app1.mediamosa.local/files
mkdir $target/sites/app2.mediamosa.local/files
chmod +w $target/sites/app2.mediamosa.local/files
mkdir $target/sites/job1.mediamosa.local/files
chmod +w $target/sites/job1.mediamosa.local/files
mkdir $target/sites/job2.mediamosa.local/files
chmod +w $target/sites/job2.mediamosa.local/files
mkdir $target/sites/download.mediamosa.local/files
chmod +w $target/sites/download.mediamosa.local/files
mkdir $target/sites/upload.mediamosa.local/files
chmod +w $target/sites/upload.mediamosa.local/files
mkdir $target/sites/openapi.mediamosa.local/files
chmod +w $target/sites/openapi.mediamosa.local/files


echo "Created symbolic links MediaMosa installation at $2 linking to $1."

echo "Edit the default settings file."
pico $target/sites/default/settings.php

echo "MediaMosa www directory for development has been setup."
