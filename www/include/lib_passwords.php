<?php

	# hey look! running code!!

	loadlib("passwords_utils");
	
	#################################################################
	
	switch ($GLOBALS["cfg"]["passwords_use_module"]){

		case "bcrypt":
			loadlib("passwords_bcrypt");
			break;
		case "mcrypt":
			loadlib("passwords_hmac");
			break;
		default:
			die("You must specify a password module in cfg.passwords_use_module");
	}

	#################################################################
	
	if (! strlen($GLOBALS['cfg']['crypto_password_secret'])){
		die("You must set cfg.crypto_password_secret");
	}

	#################################################################

	# the end