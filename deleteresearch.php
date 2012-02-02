<?php
	session_start();
	
	require_once('config/database-connect.php');
	
	$input = clean($_GET["item"]);
	$uid = $_SESSION['uid'];
	
	if(empty($uid)){
		$protected = true;
		include('header.php');
	} else {
		$qry = "SELECT id, uid FROM research WHERE id='$input' AND uid='$uid'";
		$result = mysql_query($qry);
		if($result && (mysql_num_rows($result) > 0)) {
			@mysql_free_result($result);
			$qry = "DELETE FROM research WHERE id='$input'";
			mysql_query($qry);
			header("Location: " . $_SESSION['ref']);
		} else {
			@mysql_free_result($result);
			$protected = true;
			include('header.php');
		}
	}
?>