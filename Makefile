all: js templates php-ini

js:
	java -Xmx64m -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/privatesquare.js --js www/javascript/privatesquare.venues.js --js www/javascript/privatesquare.foursquare.js --js www/javascript/privatesquare.nypl.js --js www/javascript/privatesquare.stateofmind.js --js www/javascript/privatesquare.api.js > www/javascript/privatesquare.min.js

	java -Xmx64m -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/privatesquare.pending.js > www/javascript/privatesquare.pending.min.js

	java -Xmx64m -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/privatesquare.deferred.js > www/javascript/privatesquare.deferred.min.js

t: templates

templates:
	php -q ./bin/compile-templates.php

secret:
	php -q ./bin/generate_secret.php

php-ini:
	echo "; This file has been derived automagically from the www/.htaccess file" > www/php.ini
	echo "; using the 'php-ini' command in www/Makefile on " `date` >> www/php.ini
	echo "" >> www/php.ini
	echo "; php_value settings" >> www/php.ini
	/usr/bin/env grep php_value ./www/.htaccess | sed 's/^php_value \([a-z_]*\) \([a-z]*\)/\1 = \2/' >> www/php.ini
	echo "; php_flag settings" >> www/php.ini
	/usr/bin/env grep php_flag ./www/.htaccess | sed 's/^php_flag \([a-z_]*\) \([a-z]*\)/\1 = \2/' >> www/php.ini
