<?php

	# hey look! running code!!
	
	#################################################################

	switch ($GLOBALS["cfg"]["crypto_use_module"]){

		case "libsodium":
			loadlib("crypto_libsodium");
			break;
		default:
			die("You must specify a crypto module in cfg.crypto_use_module");
	}

	#################################################################

	# the end