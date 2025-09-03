<?php
	include('include/init.php');

	error_log("[404] " . $_SERVER["REQUEST_URI"]);
	$GLOBALS["smarty"]->display("page_error_404.txt");
	exit();
