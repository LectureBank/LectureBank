<?php

require_once('config/database-connect.php');

function domain_exists($email,$record = 'MX'){
	list($user,$domain) = split('@',$email);
	if(strlen($domain) >= 1)
		{
		return checkdnsrr($domain,$record);
		}
	else
		{
		return false;
		}
}

if(isSet($_POST['email']))
{
$email = $_POST['email'];

if(domain_exists($email)) {

	$sql_check = mysql_query("select id_user from users where email='".$email."'")
 		or die(mysql_error());

	if(mysql_num_rows($sql_check))
		{
		echo '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Address in use.</font>';
		}
	else
		{
		echo 'OK';
		}
	}
else {
  	echo '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Address invalid.</font>';
}
}
?>