privatesquare
--

privatesquare is a simple web application to record and manage a private database of foursquare check-ins.

It uses the foursquare API as a single-sign-on provider, for user accounts, and
to query for nearby locations (using your web browser's built-in geolocation
support).

Check-ins can be sent on to foursquare (and again re-broadcast to Twitter,
etc. or to your followers or just "off the grid") but the important part is:
They don't have to be.

**Currently there is no export or even any historical views on your
  check-ins. It all just goes in to the database and there's no way to see it
  again without sticking your hands in there and getting dirty.**

Meanwhile, [here's a blog post](http://nearfuturelaboratory.com/2012/01/22/privatesquare/).

Installation
--

privatesquare is built on top of [Flamework](https://github.com/exflickr/flamework) which means it's nothing more
than a vanilla Apache + PHP + MySQL application. You can run it as a dedicated
virtual host or as a subdirectory of an existing host.

You will need to make a copy of the [config.php.example](https://github.com/straup/privatesquare/blob/master/www/include/config.php.example) file and name it
`config.php`. You will need to update this new file and add the various
specifics for databases and third-party APIs.

	# You will need valid foursquare OAuth credentials
	# See also: https://foursquare.com/oauth/register

	$GLOBALS['cfg']['foursquare_oauth_key'] = '';
	$GLOBALS['cfg']['foursquare_oauth_secret'] = '';
	
	# Don't change this. If you do update the main .htaccess
	# file accordingly.
	
	$GLOBALS['cfg']['foursquare_oauth_callback'] = 'auth/';

	# You will need a valid Flickr API key *or* access to a running
	# instance of the 'reverse-geoplanet' web service. By default
	# all the code that runs the reverse geocoder is included with
	# privatesquare (hence the requirement for an API key)
	# See also: https://github.com/straup/reverse-geoplanet
	# See also: http://www.flickr.com/services/apps/create/apply/

	$GLOBALS['cfg']['reverse_geoplanet_remote_endpoint'] = '';
	$GLOBALS['cfg']['flickr_api_key'] = '';

	# You will need to setup a MySQL database and plug in the specifics
	# here: https://github.com/straup/privatesquare/blob/master/schema
	# See also: https://github.com/straup/flamework-tools/blob/master/bin/setup-db.sh

	$GLOBALS['cfg']['db_main'] = array(
		'host' => 'localhost',
		'name' => 'privatesquare',
		'user' => 'privatesquare',
		'pass' => '',
		'auto_connect' => 1,
	);

	# You will need to set up secrets for the various parts of the site
	# that need to be encrypted. Don't leave these empty. Really.
	# You can create new secrets by typing `make secret`.
	# See also: https://github.com/straup/privatesquare/blob/master/bin/generate_secret.php

	$GLOBALS['cfg']['crypto_cookie_secret'] = '';
	$GLOBALS['cfg']['crypto_crumb_secret'] = '';
	$GLOBALS['cfg']['crypto_password_secret'] = '';

	# If you don't have memcache installed (or don't even know what that means)
	# just leave this blank. Otherwise change the 'cache_remote_engine' to
	# 'memcache'.

	$GLOBALS['cfg']['cache_remote_engine'] = '';
	$GLOBALS['cfg']['memcache_host'] = 'localhost';
	$GLOBALS['cfg']['memcache_port'] = '11211';

	# This is only relevant if are running privatesquare on a machine where you
	# can not make the www/templates_c folder writeable by the web server. If that's
	# the case set this to 0 but understand that you'll need to pre-compile all
	# of your templates before they can be used by the site.
	# See also: https://github.com/straup/privatesquare/blob/master/bin/compile-templates.php

	$GLOBALS['cfg']['smarty_compile'] = 1;

That's it. Or should be. If I've forgotten something please let me know or
submit a pull request.

See also
--

* [flamework](https://github.com/exflickr/flamework)

* [flamework-foursquareapp](https://github.com/straup/flamework-foursquareapp)
