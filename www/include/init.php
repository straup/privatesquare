<?php

	#
	# some startup tasks which come before anything else:
	#  * set up the timezone
	#  * record the time
	#  * set the mbstring encoding
	#

	# Also: there is running code at the bottom of this file

	# error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);
	error_reporting((E_ALL) ^ E_NOTICE);	

	putenv('TZ=EST5EDT');
	date_default_timezone_set('America/New_York');

	$GLOBALS['timings'] = array();
	$GLOBALS['timings']['execution_start'] = microtime_ms();
	$GLOBALS['timing_keys'] = array();

	$GLOBALS['timing_keys']['config'] = 'Config files';
	$GLOBALS['timings']['config_count'] = 0;
	$GLOBALS['timings']['config_time'] = 0;

	$GLOBALS['timing_keys']['loadlib'] = 'Libraries';
	$GLOBALS['timings']['loadlib_count'] = 0;
	$GLOBALS['timings']['loadlib_time'] = 0;

	$GLOBALS['timing_keys']['loadlib_default'] = 'Libraries (default)';
	$GLOBALS['timings']['loadlib_default_count'] = 0;
	$GLOBALS['timings']['loadlib_default_time'] = 0;

	$GLOBALS['timing_keys']['loadlib_auto'] = 'Libraries (auto)';
	$GLOBALS['timings']['loadlib_auto_count'] = 0;
	$GLOBALS['timings']['loadlib_auto_time'] = 0;

	$GLOBALS['timing_keys']['db_init'] = 'DB init';
	$GLOBALS['timings']['db_init_count'] = 1;
	$GLOBALS['timings']['db_init_time'] = 0;

	mb_internal_encoding('UTF-8');
	#
	# the module loading code.
	#
	# we track which modules we've loaded ourselves instead of
	# using include_once(). we do this so that we can avoid the
	# stat() overhead involved in figuring out the canonical path
	# to a file. so long as we always load modules via this
	# method, we save some filesystem overhead.
	#
	# we can also ensure that modules don't pollute the global
	# namespace accidentally, since they are always loaded in a
	# function's private scope.
	#

	$GLOBALS['loaded_libs'] = array();

	define('FLAMEWORK_INCLUDE_DIR', dirname(__FILE__).'/');

	function loadlib($name){

		if (isset($GLOBALS['loaded_libs'][$name])){
			return;
		}

		$GLOBALS['loaded_libs'][$name] = 1;

		$start = microtime_ms();

		$fq_name = _loadlib_enpathify("lib_{$name}.php");
		include($fq_name);

		$end = microtime_ms();
		$time = $end - $start;

		$GLOBALS['timings']['loadlib_count'] += 1;
		$GLOBALS['timings']['loadlib_time'] += $time;

		# $GLOBALS['timing_keys']["loadlib_{$name}"] = "lib_{$name}";
		# $GLOBALS['timings']["loadlib_{$name}_count"] = 1;
		# $GLOBALS['timings']["loadlib_{$name}_time"] = $time;
	}

	function loadpear($name){

		if ($GLOBALS['loaded_libs']['PEAR:'.$name]){
			return;
		}

		$GLOBALS['loaded_libs']['PEAR:'.$name] = 1;

		$fq_name = _loadlib_enpathify("pear/{$name}.php");
		include($fq_name);
	}

	function _loadlib_enpathify($lib){

		# see also: http://www.php.net/manual/en/ini.core.php#ini.include-path

		$inc_path = ini_get('include_path');

		if (preg_match("/\/flamework\//", $inc_path)){
			return $lib;
		}

		return FLAMEWORK_INCLUDE_DIR . $lib;
	}

	#
	# general utility functions
	#

	# This is necessary to account for traffic being load-balanced by nginx.

	function remote_addr(){

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){

			// X-Forwarded-For is a list of forwarded IP addresses, representing
			// each hop of a proxy chain. It comes in as a comma-separated list.
			//
			// At which position in the list the client's public IP appears
			// depends on the specific proxy setup. We default to "first in the
			// list," but it needs to be configured according to the network
			// stack. This is controlled by a 'remote_addr_index' config, which
			// defaults to 0.
			//
			// Note that IPs get prepended to the _front_ of the list, so the
			// index config is really counting from the end. If X-Fowarded-For
			// comes in as 1.1.1.1, 2.2.2.2, 3.3.3.3, and
			// $GLOBALS['cfg']['remote_addr_index'] is set to 0, then
			// that means 3.3.3.3 is chosen as the remote_addr() return value.
			//
			// This is important because getting it wrong means an attacker
			// could spoof somebody's IP, simply by making a request like this:
			// curl -H "X-Forwarded-For: 1.2.3.4" ... and that value would get
			// prepended to the list of IPs of our proxies, potentially tricking
			// us into accepting a forged crumb.
			//
			// See also: http://nginx.org/en/docs/http/ngx_http_proxy_module.html
			// (20171024/dphiffer)

			$ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$ip_list = array_reverse($ip_list); // remember that IPs are prepended, not appended
			$ip_index = 0;
			if ($GLOBALS['cfg']['remote_addr_index']){
				$ip_index = intval($GLOBALS['cfg']['remote_addr_index']);
			}

			if (! $ip_list[$ip_index]){
				// We cannot trust crumbs at this point, so just bail out
				// with an error message. This is may be overly-conservative,
				// but we'll stick with that for now.
				die("remote_addr() error: X-Forwarded-For does not have an index $ip_index.");
			}

			return trim($ip_list[$ip_index]);
		}

		return $_SERVER['REMOTE_ADDR'];
	}

	function remote_addr_as_int(){
		$addr = remote_addr();
		return ip2long($addr);
	}

	function dumper($foo){
		echo "<pre style=\"text-align: left;\">";
		# echo HtmlSpecialChars(var_export($foo, 1));
		echo HtmlSpecialChars(json_encode($foo, JSON_PRETTY_PRINT));
		echo "</pre>\n";
	}

	function caller(){
		$trace = debug_backtrace();
		$caller = $trace[2];	# the thing calling the thing that is invoking caller()
		$func = $caller['function'];
		return $func;
	}

	function intval_range($in, $lo, $hi){
		return min(max(intval($in), $lo), $hi);
	}

	function microtime_ms(){
		list($usec, $sec) = explode(" ", microtime());
		return intval(1000 * ((float)$usec + (float)$sec));
	}

	function filter_strict($str){

		$filter = new lib_filter();
		$filter->allowed = array();
		return $filter->go($str);
	}

	function filter_strict_quot($str){

		$str = filter_strict($str);

		$str = str_replace("&quot;", "\"", $str);
		return $str;
	}

	function get_current_host(){

		$host = gethostname();
		$host = explode(".", $host);
		$host = $host[0];
		return $host;
	}

	# load config file(s)

	$config_php = FLAMEWORK_INCLUDE_DIR . "config.php";

	$start = microtime_ms();
	include($config_php);

	$end = microtime_ms();
	$time = $end - $start;

	$GLOBALS['timings']['config_time'] += $time;

	# Fucking search engines...
	# (20141025/straup)

	$whoami = (isset($_SERVER['X_HTTP_REAL_IP'])) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
	$disallow = 0;

	if (preg_match("/bingbot/", $_SERVER['HTTP_USER_AGENT'])){
		# error_log("[DISALLOW] {$whoami} because {$_SERVER['HTTP_USER_AGENT']}");
		$disallow = 1;
	}

	if ($disallow){
		$status = "503 Service Unavailable";
		header("HTTP/1.1 {$status}");
		header("Status: {$status}");
		exit();
	}

	# First, ensure that 'abs_root_url' is both assigned and properly
	# set up to run out of user's public_html directory (if need be).

	$server_url = $GLOBALS['cfg']['abs_root_url'];
	$scheme = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https' : 'http';

	if ($GLOBALS['cfg']['server_force_https']){
		$scheme = "https";
	}

	if (($_SERVER['SERVER_PORT']) && (! $GLOBALS['cfg']['server_ignore_port'])){
		$server_port = null;

		if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
			$server_port = $_SERVER['SERVER_PORT'];
		}

		if ($server_port) {
			$server_url = "{$scheme}://{$_SERVER['SERVER_NAME']}:{$server_port}";
		}

		else {
			$server_url = "{$scheme}://{$_SERVER['SERVER_NAME']}";
		}
	}

	if (! $server_url){
		$server_url = "{$scheme}://{$_SERVER['SERVER_NAME']}";
	}

	# See this? We expect that abs_root_url always have a trailing slash.
	# Really it's just about being consistent. It doesn't really matter which
	# one you choose because either way it's going to be pain or a nuisance
	# at some point or another. So we choose trailing slashes.

	$GLOBALS['cfg']['abs_root_url'] = rtrim($server_url, '/') . "/";

	# Because sometimes you can't run your Flamework project off of the root path
	# of a domain and need to do stuff like this in your httpd.conf file
	#
	# DocumentRoot /usr/local/mapzen/whosonfirst-www-boundaryissues/www
	# Alias /boundaryissues/ca /usr/local/mapzen/whosonfirst-www-boundaryissues/www
	#
	# Because you're doing stuff like this:
	#
	# location /boundaryissues/ca {
	#    proxy_pass https://upstream_bi_ca;
	#    proxy_set_header Host $http_host;
	#    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
	#    proxy_set_header X-Proxy-Path "/boundaryissues/ca";
	# }
	#
	# Which isn't awesome but then again all of this stuff was designed around running
	# out of root so this isn't really that big a deal in the end. Or you could do this
	# in either your httpd.conf or .htaccess file, like this:
	#
	# SetEnv FLAMEWORK_SUFFIX "/boundaryissues/ca"
	#
	# The problem with doing that is if you're just _actually_ running your Flamework project
	# on / but serving it up on a nested path (and probably a different domain) via something
	# like nginx then by setting the environment locally then there is no way to introspect the
	# host that hosting this code without the URLs getting all fubar-ed. Good times...
	#
	# $GLOBALS['cfg']['enable_feature_abs_root_suffix'] = 1;
	# $GLOBALS['cfg']['abs_root_suffix'] = "/api";
	# $GLOBALS['cfg']['abs_root_suffix_env'] = 'HTTP_X_PROXY_PATH';
	#
	# (20160603/thisisaaronland)

	if ($GLOBALS['cfg']['enable_feature_abs_root_suffix']){

		$suffix = $GLOBALS['cfg']['abs_root_suffix'];
		$env = $GLOBALS['cfg']['abs_root_suffix_env'];

		if ((! $suffix) && (isset($_SERVER[ $env ]))){

			$suffix = $_SERVER[ $env ];
			$suffix = trim($suffix, '/');

			$parts = array();
			$ok = 1;

			foreach (explode("/", $suffix) as $chunk){

				if (chunk == ".."){
					$ok = 0;
					break;
				}

				$parts[] = urlencode($chunk);
			}

			if ($ok){
				$GLOBALS['cfg']['abs_root_suffix'] = implode("/", $parts);
			}
		}

		if ($suffix = $GLOBALS['cfg']['abs_root_suffix']){
			$GLOBALS['cfg']['abs_root_url'] .= trim($suffix, '/') . "/";
		}

	}

	# $GLOBALS['cfg']['auth_cookie_domain'] = parse_url($GLOBALS['cfg']['abs_root_url'], 1);

	#
	# install an error handler to check for dubious notices?
	# we do this because we only care about one of the notices
	# that gets generated. we only want to run this code in
	# devel environments. we also want to run it before any
	# libraries get loaded so that we get to check their syntax.
	#

	if ($cfg['check_notices']){
		set_error_handler('handle_error_notices', E_NOTICE);
		# error_reporting(E_ALL | E_STRICT);
		error_reporting(E_ALL);		
	}

	function handle_error_notices($errno, $errstr){
		if (preg_match('!^Use of undefined constant!', $errstr)) return false;
		return true;
	}

	#
	# figure out some global flags
	#

	$this_is_apache		= strlen($_SERVER['REQUEST_URI']) ? 1 : 0;
	$this_is_api 		= 0;
	$this_is_shell		= isset($_SERVER['SHELL']) ? 1 : 0;
	$this_is_webpage	= $this_is_apache && !$this_is_api ? 1 : 0;

	$cfg['admin_flags_no_db']		= isset($_GET['no_db']) ? 1 : 0;
	$cfg['admin_flags_show_notices']	= (isset($GLOBALS['cfg']['enable_feature_admin_notices']) && $_GET['debug']) ? 1 : 0;

	#
	# load some libraries which we will 'always' need
	#

	$start = microtime_ms();

	loadlib('features');
	loadlib('passwords');
	loadlib('auth');
	loadlib('log');		# logging comes first, so that other modules can log during startup
	loadlib('smarty');	# smarty comes next, since other libs register smarty modules
	loadlib('error');
	loadlib('sanitize');
	loadlib('filter');
	loadlib('db');
	loadlib('dbtickets');
	loadlib('crypto');
	loadlib('crumb');
	loadlib('login');
	loadlib('email');
	loadlib('utf8');
	loadlib('http');
	loadlib('paginate');

	# START OF put me... somewhere

	# Core PHP
	
	$GLOBALS['smarty']->registerPlugin('modifier', 'abs', 'abs');			
	$GLOBALS['smarty']->registerPlugin('modifier', 'array_keys', 'array_keys');
	$GLOBALS['smarty']->registerPlugin('modifier', 'base64_encode', 'base64_encode');	
	$GLOBALS['smarty']->registerPlugin('modifier', 'count', 'count');	
	$GLOBALS['smarty']->registerPlugin('modifier', 'floor', 'floor');	
	$GLOBALS['smarty']->registerPlugin('modifier', 'header', 'header');
	$GLOBALS['smarty']->registerPlugin('modifier', 'implode', 'implode');
	$GLOBALS['smarty']->registerPlugin('modifier', 'in_array', 'in_array');	
	$GLOBALS['smarty']->registerPlugin('modifier', 'strpos', 'strpos');
	$GLOBALS['smarty']->registerPlugin('modifier', 'strtoupper', 'strtoupper');	
	$GLOBALS['smarty']->registerPlugin('modifier', 'range', 'range');					
	$GLOBALS['smarty']->registerPlugin('modifier', 'ucfirst', 'ucfirst');

	# Flamework

	$GLOBALS["smarty"]->registerPlugin("modifier", "auth_has_role", "auth_has_role");
	$GLOBALS['smarty']->registerPlugin('modifier', 'dumper', 'dumper');		
	$GLOBALS['smarty']->registerPlugin('modifier', 'features_is_enabled', 'features_is_enabled');
	$GLOBALS['smarty']->registerPlugin('modifier', 'get_current_host', 'get_current_host');

	# END OF put me... somewhere
	
	$end = microtime_ms();
	$time = $end - $start;

	$GLOBALS['timings']['loadlib_default_count'] = 17;
	$GLOBALS['timings']['loadlib_default_time'] = $time;

	$start = microtime_ms();

	if (isset($GLOBALS['cfg']['autoload_libs']) && is_array($GLOBALS['cfg']['autoload_libs'])){
		foreach ($GLOBALS['cfg']['autoload_libs'] as $lib){
			$GLOBALS['timings']['loadlib_auto_count'] += 1;
			loadlib($lib);
		}
	}

	if (isset($GLOBALS['cfg']['autoload_libs_if_enabled']) && is_array($GLOBALS['cfg']['autoload_libs_if_enabled'])){
		foreach ($GLOBALS['cfg']['autoload_libs_if_enabled'] as $feature => $libs){

			if (features_is_enabled($feature)){

				if (! is_array($libs)){
					$libs = array($libs);
				}

				foreach($libs as $lib){
					$GLOBALS['timings']['loadlib_auto_count'] += 1;
					loadlib($lib);
				}
			}
		}
	}

	$end = microtime_ms();
	$time = $end - $start;

	$GLOBALS['timings']['loadlib_auto_time'] = $time;

	if (($GLOBALS['cfg']['host_disabled']) && (! $this_is_shell)){

		loadlib("http_codes");
		$codes = http_codes();

		$code = 503;
		$status = "{$code} {$codes[$code]}";

		if ($this_is_api){
			loadlib("api");
			api_config_freakout_and_die($code, "Service temporarily unavailable");
		}

		header("HTTP/1.1 {$status}");
		header("Status: {$status}");

		if ($retry = intval($GLOBALS['cfg']['site_disabled_retry_after'])){
			header("Retry-After: {$retry}");
		}

		exit();
	}

	if (($GLOBALS['cfg']['site_disabled']) && (! $this_is_shell)){

		loadlib("http_codes");
		$codes = http_codes();

		$code = 420;
		$status = "{$code} {$codes[$code]}";

		if ($this_is_api){
			loadlib("api");
			api_config_freakout_and_die($code, "Service temporarily unavailable");
		}

		header("HTTP/1.1 {$status}");
		header("Status: {$status}");

		if ($retry = intval($GLOBALS['cfg']['site_disabled_retry_after'])){
			header("Retry-After: {$retry}");
		}

		$smarty->display("page_site_disabled.txt");
		exit();
	}

	# Unavailable is distinct from disabled in that it's used to account
	# for nginx's lack of a decent health check plugin that both checks
	# health and removes nodes from its list of upstreams. And to make matters
	# worse 501 (Not Implemented) errors are not supported by nginx's
	# 'proxy_next_upstream' directive. So we're just going to use 502 and
	# carry on... (20130913/straup)

	if (($GLOBALS['cfg']['site_unavailable']) && (! $this_is_shell)){

		loadlib("http_codes");
		$codes = http_codes();

		$code = 502;
		$status = "{$code} {$codes[$code]}";

		if ($this_is_api){
			loadlib("api");
			api_config_freakout_and_die($code, "Not implemented");
		}

		header("HTTP/1.1 {$status}");
		header("Status: {$status}");

		$smarty->display("page_site_unavailable.txt");
		exit();
	}

	#
	# Smarty stuff
	#

	$GLOBALS['error'] = array();
	$GLOBALS['smarty']->assign('error', $error);

	#
	# Hey look! Running code! Note that db_init will try
	# to automatically connect to the db_main database
	# (unless you've disable the 'auto_connect' flag) and
	# will blow its brains out if there's a problem.
	#

	$start = microtime_ms();

	db_init();

	$end = microtime_ms();
	$time = $end - $start;

	$GLOBALS['timings']['db_init_time'] = $time;

	if ($this_is_webpage){
		login_check_login();
	}

	if (isset($_SERVER['HTTP_X_MOZ']) && (strtolower($_SERVER['HTTP_X_MOZ']) == 'prefetch')){

		if (! $GLOBALS['cfg']['allow_precache']){
			error_403();
		}
	}

	#
	# this timer stores the end of core library loading
	#

	$GLOBALS['timings']['init_end'] = microtime_ms();

	# the end

	# start of flamework-api stuff for API site keys

	if (($this_is_webpage) && (! $GLOBALS['cfg']['host_disabled']) && (! $GLOBALS['cfg']['site_disabled'])){

		# we assume login_check_login has been called above already
		# (20160121/thisisaaronland)

		# API site key/token stuff

		# E_EXCESSIVE_IF_STATEMENTS_INDENTING_ALL_THINGS
		# please make me less-bad (20190411/thisisaaronland)

		if (features_is_enabled("api", "api_site_keys", "api_site_tokens")){

			loadlib("api_site_tokens");

			$token = api_site_tokens_fetch_access_token($GLOBALS['cfg']['user']);
			$GLOBALS['smarty']->assign("site_token", $token['access_token']);
			$GLOBALS['smarty']->assign("site_token_expires", $token['expires']);
		}
	}

	# end of flamework-api stuff for API site key stuff

	$GLOBALS["smarty"]->assign("cfg", $GLOBALS["cfg"]);