#!/bin/bash
source functions.sh

install_db
install_data
cd $MOODLEDIR

moosh category-create hyperion
if echo "SELECT * FROM mdl_course_categories WHERE name = 'hyperion'" \
	| mysql -u "$DBUSER" -p"$DBPASSWORD" "$DBNAME"; then
  exit 0
else
  exit 1
fi

