all: js templates

js:
	java -Xmx64m -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/privatesquare.js > www/javascript/privatesquare.min.js
	java -Xmx64m -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/privatesquare.deferred.js > www/javascript/privatesquare.deferred.min.js

templates:
	php -q ./bin/compile-templates.php

secret:
	php -q ./bin/generate_secret.php