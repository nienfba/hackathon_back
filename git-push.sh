#/bin/sh

curdir=`dirname $0`

# SCRIPT SIMPLE POUR ENVOYER LES DERNIERES MODIFS SUR LE REPO GITLAB
git pull origin master

mysqldump -u root --password= myprovence > $curdir/z-database.sql

message=`git status -s`

git add -A

git commit -a -m "$message"

git push

#  rsync -avz public/assets/upload  u87819096@home663482869.1and1-data.host:/kunden/homepages/37/d663482869/htdocs/myprovence.code4marseille.fr/public/assets/upload
