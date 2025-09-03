<?php

	# https://jwt.io/introduction
	# https://datatracker.ietf.org/doc/html/rfc7519

	# This is NOT a general-purpose (feature complete) JWT library.
	# It is the bare minimum for sending JWT header to the sfomuseum/go-http-auth.JWTAuthenticator
	# package.

	/*

	$secret = random_bytes(256);
	$ttl = 60;
	
	$header = jwt_default_header();
	$payload = jwt_default_payload($ttl);
	
	$custom = array(
		'account_id' => 123,
		'account_name' => 'bob',
	);

	$payload = array_merge($payload, $custom);
	
	$rsp = jwt_encode($header, $payload, $secret);
	$rsp2 = jwt_decode($rsp['token'], $secret);
	dumper($rsp2);

	*/
	
	########################################################################

	function jwt_encode($header, $payload, $secret){
		
		$enc_header = jwt_base64_encode(json_encode($header));
		$enc_payload = jwt_base64_encode(json_encode($payload));		

		$sig_rsp = jwt_sign($enc_header, $enc_payload, $secret);

		if (! $sig_rsp['ok']){
			return array('ok' => 0, 'error' => $sig_rsp['error']);
		}
		
		$enc_sig = jwt_base64_encode($sig_rsp['signature']);
		
		$token = $enc_header . "." . $enc_payload . "." .$enc_sig;
		return array('ok' => 1, 'token' => $token);
	}

	########################################################################

	function jwt_decode($token, $secret){

		list($enc_header, $enc_payload, $enc_sig) = explode(".", $token, 3);

		$sig_rsp = jwt_sign($enc_header, $enc_payload, $secret);

		if (! $sig_rsp['ok']){
			return array('ok' => 0, 'error' => $sig_rsp['error']);
		}
		
		$this_sig = $sig_rsp['signature'];
		$that_sig = jwt_base64_decode($enc_sig);
		
		if (! hash_equals($this_sig, $that_sig)){
			return array('ok' => 0, 'error' => 'Invalid signature');		   
		}

		$header = json_decode(jwt_base64_decode($enc_header), true);
		$payload = json_decode(jwt_base64_decode($enc_payload), true);

		if (array_key_exists('nbf', $payload)){

			$nbf = intval($payload['nbf']);

			if (! $nbf){
				return array('ok' => 0, 'error' => 'Invalid nbf claim');
			}
			
			$now = time();

			if ($now < $nbf){
				return array('ok' => 0, 'error' => 'Inactive token');
			}
		}

		if (array_key_exists('exp', $payload)){

			$exp = intval($payload['exp']);

			if (! $exp){
				return array('ok' => 0, 'error' => 'Invalid exp claim');
			}
			
			$now = time();

			if ($now >  $exp){
				return array('ok' => 0, 'error' => 'Expired token');
			}
		}

		return array(
			'ok' => 1,
			'header' => $header,			     
			'payload' => $payload,
		);
	}

	########################################################################

	function jwt_sign($enc_header, $enc_payload, $secret){

		if (strlen($secret) < 256){
			return array('ok' => 0, 'error' => 'Invalid secret');
		}

		$str_body = $enc_header . "." . $enc_payload;
		$sig = hash_hmac('sha256', $str_body, $secret, true);

		if (! $sig){
			return array('ok' => 0, 'error' => 'Failed to sign message');
		}
		
		return array('ok' => 1, 'signature' => $sig);
	}
	
	########################################################################

	function jwt_default_header() {

		$header = array(
			'alg' => 'HS256',
			'typ' => 'JWT',
		);
		
		return $header;
	}

	########################################################################

	function jwt_default_payload($ttl=0) {

		$now = time();
		
		$payload = array(
			'iss' => $GLOBALS['cfg']['site_name'],
			'iat' => $now,
			'nbf' => $now,
		);

		if ($ttl > 0){
			$payload['exp'] = $now + $ttl;
		}

		return $payload;
	}

	########################################################################
	
	function jwt_base64_encode($data){
		 return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	########################################################################

	function jwt_base64_decode($data){
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}
	
	########################################################################
	
	# the end