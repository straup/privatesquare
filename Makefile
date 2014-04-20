all: prod templates php-ini

js:
	java -Xmx64m -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/privatesquare.js --js www/javascript/privatesquare.venues.js --js www/javascript/privatesquare.foursquare.js --js www/javascript/privatesquare.nypl.js --js www/javascript/privatesquare.stateofmind.js --js www/javascript/privatesquare.api.js > www/javascript/privatesquare.core.min.js

	java -Xmx64m -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/privatesquare.pending.js --js www/javascript/privatesquare.deferred.js > www/javascript/privatesquare.deferred.min.js

	java -Xmx64m -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/privatesquare.trips.js --js www/javascript/privatesquare.trips.calendars.js > www/javascript/privatesquare.trips.min.js

	# Need to sort out warnings in both select2.js and (20140118/straup)	
	# java -Xmx64m -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/select2.js > www/javascript/select2.min.js

	cat www/javascript/jquery-1.8.2.min.js www/javascript/bootstrap.min.js www/javascript/htmlspecialchars.min.js > www/javascript/privatesquare.dependencies.core.min.js

	cat www/javascript/jquery-1.8.2.min.js www/javascript/htmapl-standalone.min.js www/javascript/store.min.js > www/javascript/privatesquare.dependencies.app.js

	cat www/javascript/select2.js www/javascript/brick-calendar.min.js > www/javascript/privatesquare.dependencies.trips.min.js

css:

	cat www/css/bootstrap.min.css www/css/bootstrap.privatesquare.css www/css/privatesquare.htmapl.css > www/css/privatesquare.core.min.css

	cat www/css/select2.css www/css/bootstrap.select2.css www/css/brick-calendar.min.css > www/css/privatesquare.trips.min.css

prod: js css

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

prune:
	git gc --aggressive --prune
