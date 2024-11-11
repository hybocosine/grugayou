#!/bin/bash
if [ $# -eq 0 ]
then
    echo usage: ./pusher.sh {commit message}
else
    echo commit message: $@
    git add *
    git commit -m "$@"
    git push origin master
fi