#!/usr/bin/env bash

cd $(dirname $0)
ls -t *.wav *.au | tail -n+41 | xargs -r rm --
date > lastdelete.txt
