<?php

require_once('config/database-connect.php');

if(isSet($_POST['username']))
{
$username = $_POST['username'];

if (!preg_match('/^[a-z0-9_]+$/', $username)){
echo '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Use only a-z, 0-9, & underscore</font>';
}
else
{

$sql_check = mysql_query("select id_user from users where username='".$username."'")
 or die(mysql_error());

if(mysql_num_rows($sql_check))
{
echo '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">&quot;'.$username.'&quot;</strong>'.
' is in use.</font>';
}
else
{
echo 'OK';
}
}
}
?>