<?php

	loadlib("geo_flickr");

	#################################################################

	function whereonearth_get_by_id($woeid){

		$enc_id = AddSlashes($woeid);
		$sql = "SELECT * FROM WhereOnEarth WHERE id='{$enc_id}'";

		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	#################################################################

	function whereonearth_fetch_woeid($woeid){

		if ($row = whereonearth_get_by_id($woeid)){
			$data = json_decode($row['flickr'], 'as hash');
			return array('ok' => 1, 'data' => $data);
		}

		$rsp = whereonearth_fetch_flickr_data($woeid);

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = $rsp['data'];
		$enc_data = json_encode($data);

		$record = array(
			'id' => $woeid,
			'flickr' => $enc_data,
		);

		$rsp = whereonearth_add_record($record);

		if (! $rsp){
			return $rsp;
		}

		return array('ok' => 1, 'data' => $data);
	}

	#################################################################

	function whereonearth_fetch_flickr_data($woeid){

		$args = array(
			'woe_id' => $woeid,
		);

		$rsp = flickr_api_call('flickr.places.getInfo', $args);

		if (! $rsp['ok']){
			return $rsp;
		}

		$loc = $rsp['rsp']['place'];
		return array('ok' => 1, 'data' => $loc);
	}

	#################################################################

	function whereonearth_add_record($record){

		$now = time();
		$record['created'] = $now;

		$insert = array();

		foreach ($record as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert('WhereOnEarth', $insert);

		if ($rsp['ok']){
			$rsp['record'] = $record;
		}

		return $rsp;
	}

	#################################################################

	# the end	
