<?php

	# This is not a general purpose Redis library. Or at least it isn't yet.
	# It is a thin wrapper on top of https://github.com/nrk/predis to make
	# doing things in Redis a little more Flamework-like (20160408/thisisaaronland)

	# https://github.com/nrk/predis

	include 'Predis/Autoloader.php';

	if (class_exists('Predis\Autoloader')) {
		Predis\Autoloader::register();
	}

	$GLOBALS['redis_conns'] = array();

	########################################################################

	function redis_client($args=array()){

		$defaults = array(
			'scheme' => $GLOBALS['cfg']['redis_scheme'],
			'host' => $GLOBALS['cfg']['redis_host'],
			'port' => $GLOBALS['cfg']['redis_port'],
		);

		$args = array_merge($defaults, $args);

		$uri = "{$args['scheme']}://{$args['host']}:{$args['port']}";

		if (! isset($GLOBALS['redis_conns'][$uri])){

			$client = new Predis\Client($uri);

			try {
				$now = time();
				$rsp = $client->echo($now);

				if ($rsp != $now){
					return array('ok' => 0, 'error' => 'Connection established but is full of weird');
				}
			}

			catch (Exception $e) {
				return array('ok' => 0, 'error' => $e->getMessage());
			}

			$GLOBALS['redis_conns'][$uri] = $client;
		}

		$client = $GLOBALS['redis_conns'][$uri];
		return array('ok' => 1, 'client' => $client);
	}

	########################################################################

	# See what's going on here? It's basically what Go does. Because it turns
	# out that it's not a bad way to do things... (20160408/thisisaaronland)

	function redis_connect($more=array()){

		$rsp = redis_client($more);

		if (! $rsp['ok']){
			return array(null, $rsp);
		}

		return array($rsp['client'], null);
	}

	########################################################################

	function redis_get($key, $more=array()){

		list($client, $err) = redis_connect($more);

		if ($err){
			return $err;
		}

		$value = $client->get($key);

		return array('ok' => 1, 'value' => $value);
	}

	########################################################################

	function redis_set($key, $value, $more=array()){

		list($client, $err) = redis_connect($more);

		if ($err){
			return $err;
		}

		# How best to test for errors?

		$client->set($key, $value);

		return array('ok' => 1);
	}

	########################################################################

	function redis_publish($channel, $msg, $more=array()){

		list($client, $err) = redis_connect($more);

		if ($err){
			return $err;
		}

		$ok = $client->publish($channel, $msg);

		# get last error?

		return array('ok' => $ok);
	}

	########################################################################

	function redis_subscribe($channel, $callback_func, $more=array()){

		list($client, $err) = redis_connect($more);

		if ($err){
			return $err;
		}

		if (! function_exists($callback_func)){
                	return array("ok" => 0, "error" => "callback function does not exist");
		}

		$ps = $client->pubSubLoop();
		$ps->subscribe($channel);

		foreach ($ps as $message){

			if ($message->kind != "message"){
				continue;
			}

			call_user_func($callback_func, $message->payload);
		}

		unset($ps);

		return array("ok" => 1);
	}

	########################################################################

	# the end