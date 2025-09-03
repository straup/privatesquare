<?php
	$GLOBALS['cfg'] = array();

	# Flamework defaults - you can changes things here if you want
	# but you'll probably want to do that in config_local.php (which
	# is typically "production") or config_local_{HOSTNAME}.php (which
	# is typically a development environment).

	# Things you may want to change in a hurry

	$GLOBALS['cfg']['site_name'] = 'Flamework';
	$GLOBALS['cfg']['environment'] = 'dev';

	$GLOBALS['cfg']['host_disabled'] = 0;			# this host is out of rotation and not serving requests
	$GLOBALS['cfg']['site_disabled'] = 0;			# the site is disabled but we are still serving requests
	$GLOBALS['cfg']['site_disabled_retry_after'] = 0;	# seconds; if set will return HTTP Retry-After header

	# Message is displayed in the nav header in inc_head.txt

	$GLOBALS['cfg']['display_message'] = 0;
	$GLOBALS['cfg']['display_message_text'] = '';

	# Things you'll certainly need to tweak
	# See below for details about database password(s)

	$GLOBALS['cfg']['db_main'] = array(
		'host'	=> 'localhost',
		'name'	=> 'flamework',		# database name
		'user'	=> 'flamework',		# database username
		'auto_connect' => 0,
	);

	$GLOBALS['cfg']['db_users'] = array(

		'host' => array(
			1 => 'localhost',
			2 => 'localhost',
		),

		'user' => 'root',

		'name' => array(
			1 => 'user1',
			2 => 'user2',
		),
	);

	// See also: remote_addr() in init.php
	$GLOBALS['cfg']['remote_addr_index'] = 0;

	# hard coding this URL will ensure it works in cron mode too

	$GLOBALS['cfg']['server_scheme'] = 'https';                     # (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https' : 'http';
	$GLOBALS['cfg']['server_name'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'whosonfirst.mapzen.com';
	$GLOBALS['cfg']['server_force_https'] = 1;                      # for example, when you're running a Flamework app on port 80 behind a proxy on port 443; it happens...
	$GLOBALS['cfg']['server_ignore_port'] = 1;

        $GLOBALS['cfg']['abs_root_url']         = '';                   # "{$GLOBALS['cfg']['server_scheme']}://{$GLOBALS['cfg']['server_name']}/";
	$GLOBALS['cfg']['safe_abs_root_url']	= $GLOBALS['cfg']['abs_root_url'];

	$GLOBALS['cfg']['data_abs_root_url'] = '';			# if you need to point to something other than https://data.whosonfirst.org/

	# See notes in include/init.php

	$GLOBALS['cfg']['enable_feature_abs_root_suffix'] = 1;
	$GLOBALS['cfg']['abs_root_suffix'] = "";
	$GLOBALS['cfg']['abs_root_suffix_env'] = 'HTTP_X_PROXY_PATH';	# ignored if 'abs_root_suffix' is not empty

	# Hard-coding these paths will save some stat() ops

	$GLOBALS['cfg']['smarty_template_dir'] = realpath(dirname(__FILE__) . '/../templates/');
	$GLOBALS['cfg']['smarty_compile_dir'] = realpath(dirname(__FILE__) . '/../templates_c/');

	# These should be left as-is, unless you have an existing password database not using bcrypt and
	# you need to do auto-promotion on login.

	$GLOBALS['cfg']['passwords_use_module'] = 'bcrypt';

	$GLOBALS['cfg']['passwords_use_bcrypt'] = true;
	$GLOBALS['cfg']['passwords_allow_promotion'] = false;

	# Things you may need to tweak

	# Auth roles

	$GLOBALS['cfg']['enable_feature_auth_roles_autopromote_staff'] = 0;
	$GLOBALS['cfg']['enable_feature_auth_roles_autopromote_staff_dev'] = 0;
	$GLOBALS['cfg']['enable_feature_auth_roles_autopromote_staff_shell'] = 0;

	# Caching stuff

        $GLOBALS['cfg']['enable_feature_cache_prefixes'] = 1;
        $GLOBALS['cfg']['cache_prefix'] = $GLOBALS['cfg']['environment'];

	# Note: memcache stuff is not enabled by default but is
	# available in the 'extras' directory

	$GLOBALS['cfg']['auth_cookie_domain'] = parse_url($GLOBALS['cfg']['abs_root_url'], 1);
	$GLOBALS['cfg']['auth_cookie_name'] = 'ap';
	$GLOBALS['cfg']['auth_cookie_require_https'] = 1;

	$GLOBALS['cfg']['crumb_ttl_default'] = 300;	# seconds

	$GLOBALS['cfg']['rewrite_static_urls'] = array(
		# '/foo' => '/bar/',
	);

	$GLOBALS['cfg']['email_from_name']	= 'flamework app';
	$GLOBALS['cfg']['email_from_email']	= 'admin@ourapp.com';
	$GLOBALS['cfg']['auto_email_args']	= '-fadmin@ourapp.com';

	# Things you can probably not worry about

	$GLOBALS['cfg']['user'] = null;

	# If you are running Flamework on a host where you can not change the permissions
	# on the www/templates_c directory (to be owned by the web server) you'll need to
	# do a couple of things. The first is to set the 'smarty_compile' flag to 0. That
	# means you'll need to pre-compile all your templates by hand. You can do this with
	# 'compile-templates.php' script that is part of Flamework 'bin' directory. Obviously
	# this doesn't make much sense if you are actively developing a site but might be
	# useful if you've got something working and then just want to run it on a shared
	# hosting provider where you can't change the permissions on on files, like pair or
	# dreamhost. (20120110/straup)

	$GLOBALS['cfg']['smarty_compile'] = 1;

	# Do not always compile all the things all the time. Unless you know you're in to
	# that kind of thing. One important thing to note about this setting is that you
	# will need to reenabled it at least once (and load the template in question) if
	# you've got a template that calls a non-standard function. For example, something
	# like: {$foo|@bar_all_the_things}

	$GLOBALS['cfg']['smarty_force_compile'] = 0;

	$GLOBALS['cfg']['http_timeout'] = 3;

	$GLOBALS['cfg']['check_notices'] = 1;

	$GLOBALS['cfg']['db_profiling'] = 0;


	# This will assign $pagination automatically for Smarty but
	# you probably don't want to do this for anything resembling
	# a complex application...

	$GLOBALS['cfg']['pagination_assign_smarty_variable'] = 0;

	$GLOBALS['cfg']['pagination_per_page'] = 10;
	$GLOBALS['cfg']['pagination_spill'] = 2;
	$GLOBALS['cfg']['pagination_style'] = 'pretty';

	$GLOBALS['cfg']['pagination_keyboard_shortcuts'] = 1;
	$GLOBALS['cfg']['pagination_touch_shortcuts'] = 1;

	# Feature flags

	$GLOBALS['cfg']['enable_feature_signup'] = 1;
	$GLOBALS['cfg']['enable_feature_signin'] = 1;
	$GLOBALS['cfg']['enable_feature_persistent_login'] = 1;
	$GLOBALS['cfg']['enable_feature_account_delete'] = 1;
	$GLOBALS['cfg']['enable_feature_password_retrieval'] = 1;
	$GLOBALS['cfg']['enable_feature_contact_page'] = 1;

	$GLOBALS['cfg']['password_retrieval_ttl'] = 60 * 60 * 24;

	# Enable this flag to show a full call chain (instead of just the
	# immediate caller) in database query log messages and embedded in
	# the actual SQL sent to the server.

	$GLOBALS['cfg']['db_full_callstack'] = 0;
	$GLOBALS['cfg']['allow_prefetch'] = 0;

	# Load these libraries on every page

	$GLOBALS['cfg']['autoload_libs'] = array(
		'users',
		'users_roles',
		'users_acl',			
		'cache',
		#'cache_memcache',
	);

	# THINGS YOU SHOULD DEFINE IN YOUR secrets.php FILE WHICH IS NOT
	# MEANT TO BE CHECKED IN EVER. DON'T DO IT. AND DON'T DEFINE THESE
	# THINGS HERE. REALLY.

	$GLOBALS['cfg']['crypto_use_module'] = 'libsodium';

	# $GLOBALS['cfg']['crypto_cookie_secret'] = '';
	# $GLOBALS['cfg']['crypto_password_secret'] = '';
	# $GLOBALS['cfg']['crypto_crumb_secret'] = '';

	# $GLOBALS['cfg']['db_main']['pass'] = '';
	# $GLOBALS['cfg']['db_accounts']['pass'] = '';
	# $GLOBALS['cfg']['db_users']['pass'] = '';
	# $GLOBALS['cfg']['db_api']['pass'] = '';	
	# $GLOBALS['cfg']['db_tickets']['pass'] = '';

	# the end
	# API methods and "blessings" are defined at the bottom

	# API feature flags

	$GLOBALS['cfg']['enable_feature_api'] = 1;
	$GLOBALS['cfg']['api_require_loggedin'] = 0;

	$GLOBALS['cfg']['enable_feature_api_documentation'] = 1;
	$GLOBALS['cfg']['enable_feature_api_explorer'] = 1;
	$GLOBALS['cfg']['enable_feature_api_logging'] = 1;
	$GLOBALS['cfg']['enable_feature_api_throttling'] = 0;

	$GLOBALS['cfg']['enable_feature_api_require_keys'] = 0;		# because oauth2...
	$GLOBALS['cfg']['enable_feature_api_register_keys'] = 1;

	$GLOBALS['cfg']['enable_feature_api_delegated_auth'] = 1;
	$GLOBALS['cfg']['enable_feature_api_authenticate_self'] = 1;

        $GLOBALS['cfg']['api_log_use_module'] = "errorlog";

	# PLEASE DISCUSS OVERRIDES AND ALIASES HERE...

	$GLOBALS['cfg']['enable_feature_api_method_overrides'] = 0;
	$GLOBALS['cfg']['enable_feature_api_method_aliases'] = 0;

	$GLOBALS['cfg']['api']['method_aliases'] = array(
		'method_classes' => array(),
		'methods' => array(),
	);

	$GLOBALS['cfg']['api']['method_overrides'] = array(
		'method_classes' => array(),
		'methods' => array(),
	);

	# API URLs and endpoints

	$GLOBALS['cfg']['api_abs_root_url'] = '';	# leave blank - set in api_config_init()
	$GLOBALS['cfg']['site_abs_root_url'] = '';	# leave blank - set in api_config_init()

	$GLOBALS['cfg']['api_subdomain'] = '';
	$GLOBALS['cfg']['api_endpoint'] = 'rest/';

	$GLOBALS['cfg']['api_require_ssl'] = 1;

	$GLOBALS['cfg']['api_auth_type'] = 'oauth2';
	$GLOBALS['cfg']['api_oauth2_require_authentication_header'] = 0;
	$GLOBALS['cfg']['api_oauth2_check_authentication_header'] = 1;
	$GLOBALS['cfg']['api_oauth2_allow_get_parameters'] = 1;

	# API site keys (TTL is measured in seconds)

	$GLOBALS['cfg']['enable_feature_api_site_keys'] = 1;
	$GLOBALS['cfg']['enable_feature_api_site_tokens'] = 1;

	$GLOBALS['cfg']['api_site_keys_ttl'] = 28800;		# 8 hours
	$GLOBALS['cfg']['api_site_tokens_ttl'] = 28000;		# 8 hours
	$GLOBALS['cfg']['api_site_tokens_user_ttl'] = 3600;	# 1 hour

	$GLOBALS['cfg']['api_explorer_keys_ttl'] = 28800;		# 8 hours
	$GLOBALS['cfg']['api_explorer_tokens_ttl'] = 28000;		# 8 hours
	$GLOBALS['cfg']['api_explorer_tokens_user_ttl'] = 28000;	# 8 hours

	# We test this in lib_api_auth_oauth2.php to see whether or
	# not we need to throw an error - it's possible that we want
	# this to be computed in lib_api_config for example but right
	# now we're explicit about everything (20141114/straup)

	$GLOBALS['cfg']['enable_feature_api_oauth2_tokens_null_users'] = 1;

	# As in API key roles - see also: api_keys_roles_map()
	# (20141114/straup)

	$GLOBALS['cfg']['api_oauth2_tokens_null_users_allowed_roles'] = array(
		'site',
		'api_explorer',
		'infrastructure',
	);

	$GLOBALS['cfg']['enable_feature_api_cors'] = 1;
	$GLOBALS['cfg']['api_cors_allow_origin'] = '*';

	$GLOBALS['cfg']['enable_feature_api_extras'] = 1;

	$GLOBALS['cfg']['api_extras'] = array(
		'notes' => array(),
		'example' => '',
	);

	# API pagination

	$GLOBALS['cfg']['api_per_page_default'] = 100;
	$GLOBALS['cfg']['api_per_page_max'] = 500;

	# The actual API config

	$GLOBALS['cfg']['api'] = array(

		'formats' => array(
			'json' => array('enabled' => 1, 'documented' => 1),
			'jsonp' => array('enabled' => 1, 'documented' => 1),
		),

		'default_format' => 'json',

		# We're defining methods using the method_definitions
		# hooks defined below to minimize the clutter in the
		# main config file, aka this one (20130308/straup)
		'methods' => array(),

		'errors' => array(),

		# this is toggled on/off above with following config:
		# $GLOBALS['cfg']['enable_feature_api_method_aliases']
		#
		# and get slotted in to the general config with the
		# api_config_init_aliases() function which is in turn
		# invoked by the general api_config_init() function

		'method_aliases' => array(

			# these are applied first

			'method_classes' => array(
				# for example:
				# 'whosonfirst.concordances' => array(
				# 	'mapzen.places.concordances' => array(
				#		'documented' => 1
				#	),
				# ),
			),

			# then these...

			'methods' => array(
				# for example:
				# 'whosonfirst.concordances.getById' => array(
				#	'mapzen.places.concordances.getById' => array(
				#		'documented' => 0
				#	)
				# )
			),

		),

		# We are NOT doing the same for blessed API keys since
		# it's expected that their number will be small and
		# manageable (20130308/straup)

		'blessings' => array(
			'xxx-apikey' => array(
				'hosts' => array('127.0.0.1'),
				# 'tokens' => array(),
				# 'environments' => array(),
				'methods' => array(
					# 'foo.bar.baz' => array(
					# 	'environments' => array('sd-931')
					# )
				),
				'method_classes' => array(
					# 'foo.bar' => array(
					#	see above
					# )
				),
			),
		),
	);

	# Load api methods defined in separate PHP files whose naming
	# convention is FLAMEWORK_INCLUDE_DIR . "/config_api_{$definition}.php";
	#
	# IMPORTANT: This is syntactic sugar and helper code to keep the growing
	# number of API methods out of the main config. Stuff is loaded in to
	# memory in lib_api_config:api_config_init (20130308/straup)

	$GLOBALS['cfg']['api_method_definitions'] = array(
		'common',
	);

	$GLOBALS['cfg']['api_errors_definitions'] = array(
		'common',
	);
