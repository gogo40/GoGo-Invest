#!/bin/bash

echo $1

echo "PATH=$1"

cd $1

TIMESTAMP=$(date -d "today" +"%Y%m%d%H%M")

php $1/mk_index.php

mv $1/fundamentus.json $1/fundamentus_$TIMESTAMP.json
mv $1/fundamentus_mk.json $1/fundamentus_mk_$TIMESTAMP.json
mv $1/fundamentus_raw.json $1/fundamentus_raw_$TIMESTAMP.json
