<?php

	function login_ensure_loggedin($redir=null){
		$provider = login_get_provider();		
		$func = "{$provider}_login_ensure_loggedin";
		return call_user_func($func, $redir);
	}

	function login_ensure_loggedout($redir="", $force_logout=false){
		$provider = login_get_provider();
		$func = "{$provider}_login_ensure_loggedout";
		return call_user_func($func, $redir, $force_logout);
	}

	function login_check_login(){
		$provider = login_get_provider();	
		$func = "{$provider}_login_check_login";
		return call_user_func($func);
	}

	function login_do_login($user, $redir=''){
		$provider = login_get_provider();	
		$func = "{$provider}_login_do_login";
		return call_user_func($func, $user, $redir);
	}

	function login_do_logout(){
		$provider = login_get_provider();	
		$func = "{$provider}_login_do_logout";
		return call_user_func($func);
	}

	function login_get_provider(){

		$provider = $GLOBALS['cfg']['login_use_module'];
		
		if (! preg_match("/^[a-z_]+$/", $provider)){
			die("Missing or invalid login provider.");
		}

		$provider_lib = "{$provider}_login";
		loadlib($provider_lib);

		return $provider;
	}
	
	# the end