#!/bin/bash

cd /vagrant/src
find . -iname '*.php' | sort | grep -v ./vendor | xargs xgettext --from-code=UTF-8 -o /vagrant/locale/messages.po -L PHP
cd /vagrant/locale/
php extract-db-locale-terms.php
cd db-strings
find . -iname "*.php" | sort | xargs xgettext --join-existing --from-code=UTF-8 -o /vagrant/locale/messages.po
cd ..
rm db-strings/*
