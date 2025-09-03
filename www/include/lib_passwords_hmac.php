<?php

	if (! strlen($GLOBALS['cfg']['crypto_password_secret'])){
		die("You must set cfg.crypto_password_secret");
	}

	#################################################################

	function passwords_encrypt_password($password){

		return hash_hmac("sha256", $password, $GLOBALS['cfg']['crypto_password_secret']);
	}

	#################################################################
	
	function passwords_validate_password($password, $enc_password){

		$test = passwords_encrypt_password($password);
		return $test == $enc_password;
	}

	#################################################################
	# the end