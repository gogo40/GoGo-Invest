#!/bin/bash

TIMESTAMP=$(date -d "today" +"%Y%m%d%H%M")

php mk_index.php

mv fundamentus.json fundamentus_$TIMESTAMP.json
mv fundamentus_mk.json fundamentus_mk_$TIMESTAMP.json
mv fundamentus_raw.json fundamentus_raw_$TIMESTAMP.json
