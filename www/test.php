<?php

	include("include/init.php");
	loadlib("brooklyn_integers_api");
	loadlib("mission_integers_api");

	exit();

	$method = "next-int";

	# $rsp = mission_integers_api_call($method);
	# dumper($rsp);
	# exit();

	$method = "brooklyn.integers.create";
	$args = array('count' => 2);

	$rsp = brooklyn_integers_api_post($method, $args);
	dumper($rsp);

	exit();
?>
