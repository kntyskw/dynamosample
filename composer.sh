#!/bin/sh

if [ ! -f composer.phar ]; then 
	curl -s https://getcomposer.org/installer | php
fi

php composer.phar install
