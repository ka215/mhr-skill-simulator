#!/bin/bash

set -u

PHP8=/usr/local/bin/php80cli

$PHP8 bulk.php weapons bulk_data/weapons.csv
$PHP8 bulk.php armors bulk_data/armors.csv
$PHP8 bulk.php decorations bulk_data/decorations.csv
$PHP8 bulk.php skills bulk_data/skills.csv
