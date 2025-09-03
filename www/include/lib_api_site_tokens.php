<?php

	loadlib("random");

	#################################################################

	function api_site_tokens_api_key_role(){
		return 1;
	}

	#################################################################

	function api_site_tokens_generate_api_key(){
		$key = md5(random_string(100) . time());
		return "st-{$key}";
	}

	#################################################################

	function api_site_tokens_generate_access_token(){
		$token = md5(random_string(100) . time());
		return "st-{$token}";
	}

	#################################################################

	function api_site_tokens_permissions_map($string_keys=0){

		$map = array(
			'0' => 'login',
			'1' => 'read',
			'2' => 'write',
			'3' => 'delete',
		);

		if ($string_keys){
			$map = array_flip($map);
		}

		return $map;
	}

	#################################################################

	function api_site_tokens_fetch_api_key(){

		$ttl = $GLOBALS['cfg']['api_site_keys_ttl'];

		$key = api_site_tokens_get_api_key();

		$now = time();

		# TO DO: error handling/reporting...

		if (! $key){
			$rsp = api_site_tokens_create_api_key();
			$key = ($rsp['ok']) ? $rsp['key'] : null;
		}

		else if (! $key['expires']){
			$delete_rsp = api_site_tokens_delete_api_key($key);
			$create_rsp = api_site_tokens_create_api_key();

			$key = ($create_rsp['ok']) ? $create_rsp['key'] : null;
		}

		else if ($key['expires'] <= $now){
			$delete_rsp = api_site_tokens_delete_api_key($key);
			$create_rsp = api_site_tokens_create_api_key();

			$key = ($create_rsp['ok']) ? $create_rsp['key'] : null;
		}

		else {}

		return $key;
	}

	#################################################################

	function api_site_tokens_get_api_key(){

		$cache_key = "api_site_tokens_api_key";
		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return $cache['data'];
		}

		$role = api_site_tokens_api_key_role();
		$enc_role = AddSlashes($role);

		# Note the LIMIT 1 - this is a big and should not be necessary...
		# (20130911/straup)

		$sql = "SELECT * FROM ApiKeysSite WHERE role_id='{$enc_role}' AND deleted=0 ORDER BY CREATED DESC LIMIT 1";
		$rsp = db_fetch_api($sql);

		$row = db_single($rsp);

		if (($rsp['ok']) && ($row)){
			cache_set($cache_key, $row);
		}

		return $row;
	}

	#################################################################

	function api_site_tokens_create_api_key(){

		$user_id = 0;
		$id = dbtickets_create(64);

		$role_id = api_site_tokens_api_key_role();

		$key = api_site_tokens_generate_api_key();
		$secret = random_string(64);

		$now = time();

		$expires = (isset($GLOBALS['cfg']['api_site_keys_ttl'])) ? ($now + $GLOBALS['cfg']['api_site_keys_ttl']) : 0;

		$key_row = array(
			'id' => $id,
			'user_id' => $user_id,
			'role_id' => $role_id,
			'api_key' => $key,
			'app_secret' => $secret,
			'created' => $now,
			'expires' => $expires,
			'last_modified' => $now,
		);

		$insert = array();

		foreach ($key_row as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert_api('ApiKeysSite', $insert);

		if ($rsp['ok']){
			$rsp['key'] = $key_row;
		}

		return $rsp;
	}

	#################################################################

	function api_site_tokens_delete_api_key(&$key, $reason='expired'){

		$enc_key = AddSlashes($key["id"]);
		$sql = "DELETE FROM ApiKeysSite WHERE id = '{$enc_key}'";

		$rsp = db_write_api($sql);

		if ($rsp['ok']){
			cache_unset("api_site_tokens_api_key");
			cache_unset("api_site_tokens_api_key_{$key["id"]}");
		}

		return $rsp;
	}

	#################################################################

	function api_site_tokens_get_api_key_by_id($id){

		$cache_key = "site_tokens_api_key_id_{$id}";
		$cache = cache_get($cache_key);

		if ($cache['ok']){
		 	return $cache['data'];
		}

		$enc_id = AddSlashes($id);

		$sql = "SELECT * FROM ApiKeysSite WHERE id='{$enc_id}'";
		$rsp = db_fetch_api($sql);

		$row = db_single($rsp);

		if ($rsp['ok']){
			cache_set($cache_key, $row);
		}

		return $row;
	}

	#################################################################

	function api_site_tokens_fetch_access_token($user=null){

		$now = time();

		$site_token = api_site_tokens_get_access_token_for_user($user);

		if ($site_token){

			$valid_key = 1;
			$valid_token = 1;

			$key = api_site_tokens_get_api_key_by_id($site_token['api_key_id']);

			if (! $key){
				$valid_key = 0;
			}

			else if ($key['deleted']){
				$valid_key = 0;
			}

			else if (($key['expires']) && ($key['expires'] <= $now)){
				$valid_key = 0;
			}

			else if ($site_token['expires'] <= $now){
				$valid_token = 0;
			}

			# Now we check to see if either the key or the token will
			# expire in <some unknown amount of time that a user will
			# stay on the page...> and just create new ones if so.

			else {

				$ttl_key = $key['expires'] - $now;
				$ttl_token = $site_token['expires'] - $now;

				if ($ttl_key < 300){
					$valid_key = 0;
				}  

				if ($ttl_token < 300){
					$valid_token = 0;
				}  
			}

			if ((! $valid_key) || (! $valid_token)){

				$rsp = api_site_tokens_delete_access_token($site_token);

				$user_id = ($user) ? $user['id'] : 0;
				$cache_key = "api_site_tokens_user_{$user_id}";
				cache_unset($cache_key);

				$site_token = null;
			}
		}

		# TO DO: error handling / reporting

		if (! $site_token){
			$rsp = api_site_tokens_create_access_token($user);
			$site_token = $rsp['token'];
		}

		return $site_token;
	}

	#################################################################

	function api_site_tokens_get_access_token_for_token($token){

		$site_key = api_site_tokens_fetch_api_key();

		$enc_token = AddSlashes($token);

		$enc_user = AddSlashes($user_id);
		$enc_key = AddSlashes($site_key['id']);

		$sql = "SELECT * FROM OAuth2AccessTokensSite WHERE access_token='{$enc_token}'";

		$rsp = db_fetch_api($sql);
		$row = db_single($rsp);

		return $row;
	}

	#################################################################

	function api_site_tokens_get_access_token_for_user($user=null){

		$user_id = ($user) ? $user['id'] : 0;
	
		$cache_key = "api_site_tokens_user_{$user_id}";
		$cache = cache_get($cache_key);

		if (($cache['ok']) && ($cache['data'])){
			return $cache['data'];
		}

		$site_key = api_site_tokens_fetch_api_key();

		$enc_user = AddSlashes($user_id);
		$enc_key = AddSlashes($site_key['id']);

		$sql = "SELECT * FROM OAuth2AccessTokensSite WHERE user_id='{$enc_user}' AND api_key_id='{$enc_key}'  AND (expires=0 OR expires > UNIX_TIMESTAMP(NOW()))";

		$rsp = db_fetch_api($sql);
		$row = db_single($rsp);

		if ($rsp['ok']){
			cache_set($cache_key, $row);
		}

		return $row;
	}

	#################################################################

	function api_site_tokens_create_access_token($user=null){

		$site_key = api_site_tokens_fetch_api_key();

		$id = dbtickets_create(64);

		$user_id = ($user) ? $user['id'] : 0;

		$token = api_site_tokens_generate_access_token();

		$ttl = ($user) ? $GLOBALS['cfg']['api_site_tokens_user_ttl'] : $GLOBALS['cfg']['api_site_tokens_ttl'];
		$now = time();

		$expires = $now + $ttl;

		$perms_map = api_site_tokens_permissions_map('string keys');
		$perms = ($user_id == 0) ? $perms_map['login'] : $perms_map['delete'];

		$row = array(
			'id' => $id,
			'user_id' => $user_id,
			'api_key_id' => $site_key['id'],
			'api_key_role_id' => $site_key['role_id'],
			'access_token' => $token,
			'perms' => $perms,
			'created' => $now,
			'last_modified' => $now,
			'expires' => $expires,
		);

		$insert = array();

		foreach ($row as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert_api('OAuth2AccessTokensSite', $insert);

		if ($rsp['ok']){
			$rsp['token'] = $row;
		}

		return $rsp;
	}

	#################################################################

	function api_site_tokens_delete_access_token(&$token){

		$enc_id = AddSlashes($token['id']);
		$sql = "DELETE FROM OAuth2AccessTokensSite WHERE id='{$enc_id}'";

		$rsp = db_write_api($sql);

		if ($rsp['ok']){
			$user_id = $token["user_id"];
			cache_unset("api_site_tokens_user_{$user_id}");
		}

		return $rsp;
	}

	#################################################################

	# the end
