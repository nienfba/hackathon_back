#!/bin/sh

curdir=`dirname $0`

git pull origin master

#echo 'use db752266947'      > $curdir/1and1-import.sql
#echo ''                     >> $curdir/1and1-import.sql

#cat $curdir/database.sql    >>  $curdir/1and1-import.sql

# FIXME: IL FAUDRAIT EXTRAIRE LES INFOS DU FICHIER .env
mysql -h db752266947.db.1and1.com -u dbo752266947 --password=weekend2018 db752266947  < $curdir/z-database.sql

#rm $curdir/1and1-import.sql

rm -rf $curdir/public/assets/upload
rm -rf $curdir/public/assets/hd-upload
rm -rf $curdir/public/assets/mini-upload

# DELETE var/cache FOR PROD
rm -rf $curdir/var/cache

