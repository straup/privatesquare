<?php

	if (! CRYPT_BLOWFISH){
		die("CRYPT_BLOWFISH is required for using bcrypt");
	}

	if (! strlen($GLOBALS['cfg']['crypto_password_secret'])){
		die("You must set cfg.crypto_password_secret");
	}

	loadlib("bcrypt");

	#################################################################

	function passwords_encrypt_password($password){

		$h = new BCryptHasher();
		return $h->HashPassword($password);
	}

	#################################################################
	
	function passwords_validate_password($password, $enc_password){

		$h = new BCryptHasher();
		return $h->CheckPassword($password, $enc_password);
	}

	#################################################################
	# the end