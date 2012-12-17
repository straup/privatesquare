<?php

	include("include/init.php");

	# THIS MAY OR MAY NOT STAY (20121217/straup)
	error_404();

	features_ensure_enabled("appcache");

	header("Content-type: text/cache-manifest");

	$template = "page_appcache_FIXME.txt";

	$GLOBALS['smarty']->display($template);
	exit();
