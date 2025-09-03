<?php
	include('include/init.php');

	error_log("[500] " . $_SERVER["REQUEST_URI"]);
	$GLOBALS["smarty"]->display("page_error_500.txt");
	exit();
