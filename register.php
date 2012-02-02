<?php
	session_start();
	$title = "Welcome";
	
	require_once('config/database-connect.php');
	require_once('phpgmailer/class.phpgmailer.php');
	
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
	
if ($_POST['form_submitted'] == '1') {
	//Arrays to store validation errors
	$username_errors = array();
	$email_errors = array();
	$password_errors = array();
	$_SESSION['USERNAME_ERRORS']=NULL;
	$_SESSION['EMAIL_ERRORS']=NULL;
	$_SESSION['PASSWORD_ERRORS']=NULL;
	
	//Validation error flag
	$errflag = false;
	
	//Sanitize the POST values
	$username = clean($_POST['username']);
	$password = clean($_POST['password']);
	$passwordconfirm = clean($_POST['passwordconfirm']);
	$email = clean($_POST['email']);
	
	if(strlen($username)<5){
		$username_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Must be at least <strong>5</strong> characters.</font>';
		$errflag = true;
	}
	if(strlen($username)>20){
		$username_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Must be less than <strong>20</strong> characters.</font>';
		$errflag = true;
	}
	if(!preg_match('/^[a-z0-9_]+$/', $username)){
		$username_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Use only a-z, 0-9, & underscore</font>';
		$errflag = true;
	}
	if(strlen($password)<6){
		$password_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Must be at least <strong>6</strong> characters.</font>';
		$errflag = true;
	}
	if(strlen($password)>32){
		$password_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Must be less than <strong>32</strong> characters.</font>';
		$errflag = true;
	}
	if(strcmp($password, $passwordconfirm) != 0) {
		$password_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Passwords must match.</font>';
		$errflag = true;
	}
	if(strlen($email)<6){
		$email_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Too short to validate.</font>';
		$errflag = true;
	}
	else if(!domain_exists($email)) {
		$email_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Address invalid.</font>';
		$errflag = true;
	}

	//Check for duplicate username
	if($username != '') {
		$qry = "SELECT * FROM users WHERE username='$username'";
		$result = mysql_query($qry);
		if($result) {
			if(mysql_num_rows($result) > 0) {
				$username_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">&quot;'.$username.'&quot;</strong> is in use.</font>';
				$errflag = true;
			}
			@mysql_free_result($result);
		}
	}
	
	//Check for duplicate email
	if(domain_exists($email)) {
		$qry = "SELECT * FROM users WHERE email='$email'";
		$result = mysql_query($qry);
		if($result) {
			if(mysql_num_rows($result) > 0) {
				$email_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Address in use.</font>';
				$errflag = true;
			}
			@mysql_free_result($result);
		}
	}
	
	if($errflag) {
		$_SESSION['USERNAME_ERRORS'] = $username_errors;
		$_SESSION['EMAIL_ERRORS'] = $email_errors;
		$_SESSION['PASSWORD_ERRORS'] = $password_errors;
		session_write_close();
		header("Location: signup.php");
		exit;
	}
	else
	{
	$status = 'verify';
	$activationkey = mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand();
	$insert_query = 'insert into 	users (
					username,
					password,
					email,
					status,
					activationkey
					) 
					values
					(
					"' . $username . '",
					"' . md5($password) . '",
					"' . $email . '",
					"' . $status . '",
					"' . $activationkey . '"
					)';

	mysql_query($insert_query);

$activationemail = "Welcome to LectureBank!

You, or someone using your email address, has completed registration at LectureBank.org. You can complete registration by clicking the following link: http://www.lecturebank.org/activate.php?{$activationkey}

If this is an error, ignore this email and you will be removed from our mailing list.

Regards,
The LectureBank.org Team";

$mail = new PHPGMailer();
$mail->Username = 'admin@lecturebank.org'; 
$mail->Password = 'minoritynetworking';
$mail->From = 'admin@lecturebank.org'; 
$mail->FromName = 'LectureBank Administrator';
$mail->Subject = 'LectureBank.org Registration';
$mail->AddAddress($email);
$mail->Body = $activationemail;
$mail->Send();
	}
}
else
{
	$_SESSION['USERNAME_ERRORS']=NULL;
	$_SESSION['EMAIL_ERRORS']=NULL;
	$_SESSION['PASSWORD_ERRORS']=NULL;
	session_write_close();
	header("Location: signup.php");
	exit;
}

	include('header.php');
?>
  <h1>Thanks for Signing Up!</h1>
 
<h2>The account <?php echo $email ?> has been registered.</h2>
<p>Your new profile will be available at <strong>lecturebank.org/<?php echo $username; ?></strong> after you fill in a few short details about yourself. But first, you have to check your email for the activation link that's on its way.</p>
<?php
	include('footer.php');
?>