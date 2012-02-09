<?php
define('DB_HOST', 'internal-db.s124943.gridserver.com');
define('DB_USER', 'db124943');
define('DB_PASSWORD', 'Grow$T3M');
define('DB_DATABASE', 'db124943_lecturebank');

//Connect to mysql server
$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if(!$link) {
	die('Failed to connect to server: ' . mysql_error());
}

//Select database
$db = mysql_select_db(DB_DATABASE);
if(!$db) {
	die("Could not select database");
}

//Function to sanitize values received from the form. Prevents SQL injection
function clean($str) {
	$str = @trim($str);
	if(get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	return mysql_real_escape_string($str);
}
?>