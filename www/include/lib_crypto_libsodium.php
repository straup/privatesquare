<?php

	# requires 7.2 or higher

	# https://paragonie.com/blog/2015/05/using-encryption-and-authentication-correctly
	# https://paragonie.com/blog/2015/05/if-you-re-typing-word-mcrypt-into-your-code-you-re-doing-it-wrong
	# https://paragonie.com/blog/2017/02/cryptographically-secure-php-development
	
	# https://paragonie.com/blog/2017/06/libsodium-quick-reference-quick-comparison-similar-functions-and-which-one-use
	# https://paragonie.com/book/pecl-libsodium/read/09-recipes.md#encrypted-cookies
	# https://github.com/defuse/php-encryption/blob/master/docs/Tutorial.md

	if (strlen($GLOBALS['cfg']['crypto_libsodium_nonce']) != SODIUM_CRYPTO_SECRETBOX_NONCEBYTES){
		die("Invalid libsodium nonce");
	}

	function crypto_encrypt($plaintext, $secret){
		return sodium_crypto_secretbox($plaintext, $GLOBALS['cfg']['crypto_libsodium_nonce'], $secret);
	}

	function crypto_decrypt($ciphertext, $secret){
		return sodium_crypto_secretbox_open($ciphertext, $GLOBALS['cfg']['crypto_libsodium_nonce'], $secret);
	}

	function crypto_generate_key(){
		return random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
	}

	# the end
	