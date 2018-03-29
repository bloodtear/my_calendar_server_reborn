#! /bin/bash

target=$1
target_file=db_"$target".class.php

cp template.class.php $target_file

sed -i -e 's/template/'$target'/g' $target_file

