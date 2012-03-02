<?php
	session_start();
	$title = "Activation";
	
	require_once('config/database-connect.php');
	
	$queryString = $_SERVER['QUERY_STRING'];

	$query = "SELECT * FROM users WHERE status = 'verify' AND activationkey = '$queryString'";

	$result = mysql_query($query);
	if($result && (mysql_num_rows($result) > 0)) {
		$success = true;
		$updateqry="UPDATE users SET activationkey = NULL, status = 'activated' WHERE (id_user = $row[id_user])";
		mysql_query($updateqry);
	}
	@mysql_free_result($result);

	include('header.php');
	
	if($success){
		echo "<h1>Your account has been activated!</h1>";
		echo "<h2>You can now login and start using your LectureBank account.</h2>";
	}
	else{
		echo "<h1>There was a problem activating your account.</h1>";
		echo "<h2>Either your account is already active, we're experiencing difficulties, or you reached this page by mistake.</h2>";
		echo "<p>Try logging in, or try clicking the link you used to reach this page again. If you copied and pasted this address into your web browser, or if you typed it manually, try that one more time-you may have missed a part of it. Otherwise, wait and try again later, or email us at <a href='mailto:admin@lecturebank.org'>admin@lecturebank.org</a> to notify us that there was a problem. Thanks for your patience!</p>";
	}

	include('footer.php');
?>