<?php

	loadlib("random");

	#################################################################

	function api_keys_roles_map($string_keys=0){

		$map = array(
			0 => 'general',
			1 => 'site',
			2 => 'api_explorer',
			3 => 'infrastructure',
		);

		if ($string_keys){
			$map = array_flip($map);
		}

		return $map;
	}

	#################################################################

	function api_keys_role_id_to_label($id){

		$map = api_keys_roles_map();
		return (isset($map[$id])) ? $map[$id] : null;
	}

	#################################################################

	function api_keys_role_label_to_id($label){

		$map = api_keys_roles_map("string keys");
		return (isset($map[$label])) ? $map[$label] : null;
	}

	#################################################################

	function api_keys_get_by_id($id){

		$cache_key = "api_key_id_{$id}";
		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return $cache['data'];
		}

		$enc_id = AddSlashes($id);

		$sql = "SELECT * FROM ApiKeys WHERE id='{$enc_id}'";

		$rsp = db_fetch_api($sql);
		$row = db_single($rsp);

		if ($rsp['ok']){
			cache_set($cache_key, $row);
		}

		return $row;
	}

	#################################################################

	function api_keys_get_by_key($key){

		$cache_key = "api_key_key_{$key}";
		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return $cache['data'];
		}

		$enc_key = AddSlashes($key);

		$sql = "SELECT * FROM ApiKeys WHERE api_key='{$enc_key}'";

		$rsp = db_fetch_api($sql);
		$row = db_single($rsp);

		if ($rsp['ok']){
			cache_set($cache_key, $row);
		}

		return $row;
	}

	#################################################################

	function api_keys_get_keys($args=array()){

		$sql = "SELECT * FROM ApiKeys FORCE INDEX (by_role_created) WHERE role_id=0 ORDER BY created DESC";
		$rsp = db_fetch_paginated_api($sql, $args);

		return $rsp;
	}

	#################################################################

	function api_keys_fetch_api_explorer_key(){

		$ttl = $GLOBALS['cfg']['api_explorer_keys_ttl'];

		$key = api_keys_get_api_explorer_key();

		$now = time();

		# TO DO: error handling/reporting...

		if (! $key){
			$rsp = api_keys_create_api_explorer_key();
			$key = ($rsp['ok']) ? $rsp['key'] : null;
		}

		else if ($now >= ($key['created'] + $ttl)){

			$delete_rsp = api_keys_delete_api_explorer_key($key);
			$create_rsp = api_keys_create_api_explorer_key();

			$key = ($create_rsp['ok']) ? $create_rsp['key'] : null;
		}

		else {}

		return $key;
	}

	#################################################################

	function api_keys_get_api_explorer_key(){

		$cache_key = "api_key_api_explorer_key";
		$cache = cache_get($cache_key);

		if ($cache['ok']){
			# return $cache['data'];
		}

		$map = api_keys_roles_map('string keys');
		$role = $map['api_explorer'];

		$enc_role = AddSlashes($role);

		# Note the LIMIT 1 - this is a bug and should not be necessary...
		# (20130911/straup)

		$sql = "SELECT * FROM ApiKeys WHERE role_id='{$enc_role}' AND deleted=0 ORDER BY CREATED DESC LIMIT 1";
		$rsp = db_fetch_api($sql);

		$row = db_single($rsp);

		if (($rsp['ok']) && ($row)){
			cache_set($cache_key, $row);
		}

		return $row;
	}

	#################################################################

	function api_keys_get_api_explorer_keys($more=array()){

		$defaults = array(
			'ensure_active' => 1,
		);

		$more = array_merge($defaults, $more);

		$map = api_keys_roles_map('string keys');
		$role = $map['api_explorer'];

		$enc_role = AddSlashes($role);

		$sql = "SELECT * FROM ApiKeys WHERE role_id='{$enc_role}'";

		if ($more['ensure_active']){
			$sql .= " AND deleted=0";
		}

		$sql .= " ORDER BY created DESC";

		$rsp = db_fetch_paginated_api($sql, $more);
		return $rsp;
	}

	#################################################################

	function api_keys_create_api_explorer_key(){

		$user_id = 0;
		$id = dbtickets_create(64);

		$role_map = api_keys_roles_map('string keys');
		$role_id = $role_map['api_explorer'];

		$key = api_keys_generate_key();
		$secret = random_string(64);

		$now = time();

		$key_row = array(
			'id' => $id,
			'user_id' => $user_id,
			'role_id' => $role_id,
			'api_key' => $key,
			'app_secret' => $secret,
			'created' => $now,
			'last_modified' => $now,
			'app_title' => "{$GLOBALS['cfg']['site_name']} API Explorer",
		);

		$insert = array();

		foreach ($key_row as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert_api('ApiKeys', $insert);

		if ($rsp['ok']){
			$rsp['key'] = $key_row;
		}

		return $rsp;
	}

	#################################################################

	function api_keys_delete_api_explorer_key(&$key, $reason='expired'){

		$rsp = api_keys_delete($key, $reason);

		if ($rsp['ok']){
			cache_unset('api_key_api_explorer_key');
		}

		return $rsp;
	}

	#################################################################

	function api_keys_create_infrastructure_key($label, $created_by){

		$user_id = 0;
		$id = dbtickets_create(64);

		$role_map = api_keys_roles_map('string keys');
		$role_id = $role_map['infrastructure'];

		$key = api_keys_generate_key();
		$secret = random_string(64);

		$now = time();

		$key_row = array(
			'id' => $id,
			'user_id' => $user_id,
			'role_id' => $role_id,
			'api_key' => $key,
			'app_secret' => $secret,
			'created' => $now,
			'last_modified' => $now,
			'app_title' => $label,
			'created_by' => $created_by,
		);

		$insert = array();

		foreach ($key_row as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert_api('ApiKeys', $insert);

		if ($rsp['ok']){
			$rsp['key'] = $key_row;
		}

		return $rsp;
	}

	#################################################################

	function api_keys_get_infrastructure_keys($more=array()){

		$defaults = array(
			'ensure_active' => 1,
		);

		$more = array_merge($defaults, $more);

		$map = api_keys_roles_map('string keys');
		$role = $map['infrastructure'];

		$enc_role = AddSlashes($role);

		$sql = "SELECT * FROM ApiKeys WHERE role_id='{$enc_role}'";

		if ($more['ensure_active']){
			$sql .= " AND deleted=0";
		}

		$sql .= " ORDER BY created DESC";

		$rsp = db_fetch_paginated_api($sql, $more);
		return $rsp;
	}

	#################################################################

	# See this. It's called '_fetch_site_key' while the function below
	# it is called '_get_site_key'. It's a (possibly annoying but) important
	# distinction. The former is the one that retrieves a row from the
	# database and performs checks and deletes/creates/rotates keys as
	# needed. (20130508/straup)

	function api_keys_fetch_site_key(){

		$ttl = $GLOBALS['cfg']['api_site_keys_ttl'];

		$key = api_keys_get_site_key();
		# $key['debug'] = 'FETCH';

		$now = time();

		# TO DO: error handling/reporting...

		if (! $key){
			$rsp = api_keys_create_site_key();
			$key = ($rsp['ok']) ? $rsp['key'] : null;
		}

		else if (! $key['expires']){
			$delete_rsp = api_keys_delete_site_key($key);
			$create_rsp = api_keys_create_site_key();

			$key = ($create_rsp['ok']) ? $create_rsp['key'] : null;
		}

		else if ($key['expires'] <= $now){
			$delete_rsp = api_keys_delete_site_key($key);
			$create_rsp = api_keys_create_site_key();

			$key = ($create_rsp['ok']) ? $create_rsp['key'] : null;
		}

		else {}

		return $key;
	}

	#################################################################

	function api_keys_get_site_key(){

		$cache_key = "api_key_site_key";
		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return $cache['data'];
		}

		$map = api_keys_roles_map('string keys');
		$role = $map['site'];

		$enc_role = AddSlashes($role);

		# Note the LIMIT 1 - this is a big and should not be necessary...
		# (20130911/straup)

		$sql = "SELECT * FROM ApiKeys WHERE role_id='{$enc_role}' AND deleted=0 ORDER BY CREATED DESC LIMIT 1";
		$rsp = db_fetch_api($sql);

		$row = db_single($rsp);

		if (($rsp['ok']) && ($row)){
			cache_set($cache_key, $row);
		}

		return $row;
	}

	#################################################################

	function api_keys_get_site_keys($more=array()){

		$defaults = array(
			'ensure_active' => 1,
		);

		$more = array_merge($defaults, $more);

		$map = api_keys_roles_map('string keys');
		$role = $map['site'];

		$enc_role = AddSlashes($role);

		$sql = "SELECT * FROM ApiKeys WHERE role_id='{$enc_role}'";

		if ($more['ensure_active']){
			$sql .= " AND deleted=0";
		}

		$sql .= " ORDER BY created DESC";

		$rsp = db_fetch_paginated_api($sql, $more);
		return $rsp;
	}

	#################################################################

	function api_keys_create_site_key(){

		$user_id = 0;
		$id = dbtickets_create(64);

		$role_map = api_keys_roles_map('string keys');
		$role_id = $role_map['site'];

		$key = api_keys_generate_key();
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
			'app_title' => "{$GLOBALS['cfg']['site_name']} site key",
		);

		$insert = array();

		foreach ($key_row as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert_api('ApiKeys', $insert);

		if ($rsp['ok']){
			$rsp['key'] = $key_row;
		}

		return $rsp;
	}

	#################################################################

	function api_keys_delete_site_key(&$key, $reason='expired'){

		$rsp = api_keys_delete($key, $reason);

		if ($rsp['ok']){
			cache_unset('api_key_site_key');
		}

		return $rsp;
	}

	#################################################################

	function api_keys_for_user(&$user, $more=array()){

		$enc_user = AddSlashes($user['id']);

		$sql = "SELECT * FROM ApiKeys WHERE user_id='{$enc_user}' AND deleted=0 ORDER BY created DESC";
		$rsp = db_fetch_paginated_api($sql, $more);

		return $rsp;
	}

	#################################################################

	function api_keys_create($user_id, $title, $description, $callback=''){

		$user = users_get_by_id($user_id);

		$id = dbtickets_create(64);

		$role_map = api_keys_roles_map('string keys');
		$role_id = $role_map['general'];

		$key = api_keys_generate_key();
		$secret = random_string(64);

		$now = time();

		$key_row = array(
			'id' => $id,
			'user_id' => $user['id'],
			# 'role_id' => $role_id,
			'api_key' => $key,
			'app_secret' => $secret,
			'created' => $now,
			'last_modified' => $now,
			'app_title' => $title,
			'app_description' => $description,
			'app_callback' => $callback,
		);

		# TO DO: callbacks and other stuff (what?)

		$insert = array();

		foreach ($key_row as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert_api('ApiKeys', $insert);

		if ($rsp['ok']){
			$rsp['key'] = $key_row;
		}

		return $rsp;
	}

	#################################################################

	function api_keys_update(&$key, $update){

		$update['last_modified'] = time();

		$insert = array();

		foreach ($update as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$enc_id = AddSlashes($key['id']);
		$where = "id='{$enc_id}'";

		$rsp = db_update_api('ApiKeys', $insert, $where);

		if ($rsp['ok']){

			api_keys_purge_cache($key);
		
			$key = array_merge($key, $update);
			$rsp['key'] = $key;
		}

		return $rsp;
	}

	#################################################################

	function api_keys_disable(&$key){
		$update = array('disabled' => time());
		return api_keys_update($key, $update);
	}

	#################################################################

	function api_keys_enable(&$key){
		$update = array('disabled' => 0);
		return api_keys_update($key, $update);
	}

	#################################################################

	function api_keys_delete(&$key, $reason=''){

		loadlib("api_oauth2_access_tokens");
		$rsp = api_oauth2_access_tokens_delete_for_key($key);

		if (! $rsp['ok']){
			return $rsp;
		}

		$update = array('deleted' => time());
		return api_keys_update($key, $update);
	}

	#################################################################

	function api_keys_undelete(&$key){
		$update = array('deleted' => 0);
		return api_keys_update($key, $update);
	}

	#################################################################

	function api_keys_purge_cache(&$key){

		$cache_keys = array(
			"api_key_id_{$key['id']}",
			"api_key_key_{$key['api_key']}",
		);

		foreach ($cache_keys as $cache_key){
			cache_unset($cache_key);
		}
	}

	#################################################################

	function api_keys_generate_key(){
		$key = md5(random_string(100) . time());
		return $key;
	}

	#################################################################

	function api_keys_is_site_key(&$key){

		$map = api_keys_roles_map('string keys');
		return ($key['role_id'] == $map['site']) ? 1 : 0;
	}

	#################################################################

	function api_keys_is_expired(&$key){

		$now = time();
		return (($key['expires']) && ($key['expires'] < $now)) ? 1 : 0;
	}

	#################################################################

	function api_keys_is_disabled(&$key){

		$now = time();
		return (($key['disabled']) && ($key['disabled'] <= $now)) ? 1 : 0;
	}

	#################################################################

	function api_keys_is_infrastructure_key(&$key){

		$map = api_keys_roles_map();
		$role = $key['role_id'];

		return ($map[$role] == "infrastructure") ? 1 : 0;
	}

	#################################################################

	# the end
