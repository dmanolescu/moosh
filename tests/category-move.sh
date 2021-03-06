#!/bin/bash
source functions.sh

install_db
install_data
cd $MOODLEDIR

moosh category-move 1 2 
if mysql -u "$DBUSER" -p"$DBPASSWORD" "$DBNAME" -e \
    "SELECT * FROM mdl_course_categories WHERE depth = 2"\
    | grep /2/1; then
  exit 0
else
  exit 1
fi
