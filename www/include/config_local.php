<?php

	# At least for now I want to keep all the SFO specific configs in here
	# rather than config.php since I'd like to better understand what specifically
	# are the SFO -isms versus common Who's On First -isms
	# (20180606/thisisaaronland)

	$GLOBALS['cfg']['environment'] = 'prod';

	$GLOBALS['cfg']['site_unavailable'] = 0;
	
	$GLOBALS['cfg']['host_disabled'] = 0;		# this host is out of rotation and not serving requests
	$GLOBALS['cfg']['site_disabled'] = 0;		# the site is disabled but we are still serving requests
	$GLOBALS['cfg']['site_disabled_retry_after'] = 0;	# seconds; if set will return HTTP Retry-After header

	$GLOBALS['cfg']['enable_feature_signup'] = 0;
	$GLOBALS['cfg']['enable_feature_signin'] = 0;
	$GLOBALS['cfg']['enable_feature_persistent_login'] = 0;
	$GLOBALS['cfg']['enable_feature_account_delete'] = 0;
	$GLOBALS['cfg']['enable_feature_password_retrieval'] = 0;
	$GLOBALS['cfg']['enable_feature_contact_page'] = 0;

	$GLOBALS['cfg']['site_disabled_retry_after'] = 0;	# seconds; if set will return HTTP Retry-After header

	$GLOBALS['cfg']['site_name'] = 'Brooklyn Integers';

	#

	$GLOBALS['cfg']['brooklynts_sequence_increment'] = 2;
	$GLOBALS['cfg']['brooklynts_sequence_offset'] = 1;
	
	#

	$GLOBALS['cfg']['abs_root_url'] = "/";
	$GLOBALS['cfg']['api_abs_root_url'] = "/api/";

	# Elasticsearch (or OpenSearch)

	$GLOBALS['cfg']['elasticsearch_http_timeout'] = 10;
	
	$GLOBALS['cfg']['elasticsearch_host'] = 'http://localhost';
	$GLOBALS['cfg']['elasticsearch_port'] = '9200';
	$GLOBALS['cfg']['elasticsearch_index'] = 'flamework';	

	# MySQL

	$GLOBALS['cfg']['db_main'] = array(
		'host'	=> 'localhost',
		'name'	=> 'brooklynts',
		'user'	=> 'brooklynts',
		'auto_connect' => 0,
		'ssl_enable' => 0,
		'ssl_ca_path' => "",
	);

        $GLOBALS['cfg']['db_accounts'] = $GLOBALS['cfg']['db_main'];
        $GLOBALS['cfg']['db_users'] = $GLOBALS['cfg']['db_main'];	
        $GLOBALS['cfg']['db_api'] = $GLOBALS['cfg']['db_main'];
        $GLOBALS['cfg']['db_tickets'] = $GLOBALS['cfg']['db_main'];	

	# As in an instance of flamework that has no access to
	# its mysql config files and/or the ability to set up
	# a dedicated DB server for tickets.

	$GLOBALS['cfg']['db_enable_poormans_ticketing'] = 1;

	$GLOBALS['cfg']['api_method_definitions'] = array(
		'common',
		'brooklynintegers',
	);

	$GLOBALS['cfg']['enable_feature_api'] = 1;
	$GLOBALS['cfg']['enable_feature_api_www'] = 1;	
	$GLOBALS['cfg']['enable_feature_api_documentation'] = 1;
	$GLOBALS['cfg']['enable_feature_api_explorer'] = 0;

	$GLOBALS['cfg']['enable_feature_api_integers'] = 1;

	$GLOBALS['cfg']['enable_feature_api_delegated_auth'] = 0;
	$GLOBALS['cfg']['api_auth_type'] = 'none';
	
	# API (CORS)
	
	$GLOBALS['cfg']['enable_feature_api_cors'] = 1;
	
	$GLOBALS['cfg']['api_cors_allow_origin'] = array(
		"*"
	);
	
	$GLOBALS['cfg']['api_cors_allow_methods'] = array(
		"OPTIONS",
		"GET",
		"POST",
	);
	
	$GLOBALS['cfg']['api_cors_allow_headers'] = array(

	);

	$GLOBALS['cfg']['api_extras'] = array(
		# 'notes' => array(),
		# 'example' => '',
	);

	$GLOBALS['cfg']['users_use_module'] = 'flamework';
	$GLOBALS['cfg']['login_use_module'] = 'flamework';
	$GLOBALS['cfg']['crypto_use_module'] = 'libsodium';

	// if using lib_sodium secrets must be SODIUM_CRYPTO_SECRETBOX_KEYBYTES (32 bytes)
	// if using lib_sodium you must also set $GLOBALS['cfg']['crypto_libsodium_nonce']
	// and it must be SODIUM_CRYPTO_SECRETBOX_NONCEYTES (24 bytes)

	$GLOBALS['cfg']['pagination_per_page'] = 12;
	$GLOBALS['cfg']['pagination_spill'] = 0;