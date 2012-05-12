<?php

$username = "Ellen Ripley";
$root = dirname (dirname (__FILE__));
ini_set ("include_path", "{$root}/www:{$root}/www/include");

include ("include/init.php");

$log = array ();
$dbcfg = $GLOBALS['cfg']['db_main'];
$link = mysql_connect ($dbcfg['host'], $dbcfg['user'], $dbcfg['pass']);
if (!$link) {
	$log[] = 'Couldn\'t connect to MySQL at ' . $dbcfg['host'] . ' (' . mysql_error () . ')';
}

if (empty ($log)) {
	$db = mysql_select_db ($dbcfg['name'], $link);
	if (!$db) {
		$log[] = 'Couldn\'t use ' . $dbcfg['name'] . ' (' . mysql_error () . ')';
}
}

if (empty ($log)) {
	$sql = "SELECT * FROM users WHERE username='{$username}'";
	$res = mysql_query ($sql, $link);
	if (!$res) {
		$log[] = 'Bad query; maybe ' . $username . ' is invalid?';
	}
	else {
		$user = mysql_fetch_assoc ($res);
		$expires = ($GLOBALS['cfg']['enable_feature_persistent_login']) ? strtotime('now +10 years') : 0;
		$auth_cookie = login_generate_auth_cookie ($user);
		login_set_cookie($GLOBALS['cfg']['auth_cookie_name'], $auth_cookie, $expires);
	}
}
if ($link) {
	mysql_close ($link);
}
?>

<html>
<head>
	<title>privatesquare is spoofing your login cookie</title>
</head>
<html>
<body>
<?php
if (empty ($log)) {
?>
<p>All done; <a href="<?php echo $GLOBALS['cfg']['abs_root_url']; ?>">now click here.</p>
<?php
}
else {
	foreach ($log as $msg) {
		echo "<p>{$msg}</p>";
	}
}
?>
</body>
</html>