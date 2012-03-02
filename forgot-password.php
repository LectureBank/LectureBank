<?php
	$title = "Forgot Password";
	$uservalidation = true;
	
	require_once('config/database-connect.php');
	require_once('includes/recaptchalib.php');
	require_once('phpgmailer/class.phpgmailer.php');
	
	include('header.php');
	
if ($_POST['form_submitted'] == '1') {
	$privatekey = "6LfWWc4SAAAAAJwoC0-OJhujMkAvs7k5RUnyNIHS";
	
	//Sanitize the POST values
	$email = clean($_POST['email']);
	$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

  if (!$resp->is_valid) {
    // What happens when the CAPTCHA was entered incorrectly
	
	$errors = true;
	$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;The reCAPTCHA wasn\'t entered correctly. Go back and try it again. (reCAPTCHA said: ' . $resp->error . ')';	 
	include('forgotpwform.php');
  } else { 
    // Your code here to handle a successful verification
	
	$checkemailqry = "SELECT * FROM users WHERE email='$email'";
	$checkemailresult = mysql_query($checkemailqry);
	if($checkemailresult && (mysql_num_rows($checkemailresult) > 0)) {
		$status = 'forgotpw';
		$activationkey = mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand();
		$activationkey = sha1($activationkey);
		$updateqry = "UPDATE users SET status='$status', activationkey='$activationkey' WHERE email='$email'";
		mysql_query($updateqry);
		
		$forgotpwemail = "Hello from LectureBank!

We recieved a request to reset the password for the account associated with this email address. If you made this request, please follow the instructions below.

If you did not request to have your password reset, you can safely ignore this email. We assure you that your account is safe. If you have any questions or concerns, or if you believe this request may be the result of fraudulent activity, you can email our support team at admin@lecturebank.org. 

You can reset your password by clicking the following link: http://www.lecturebank.org/forgot-password.php?{$activationkey}

If clicking the link doesn't work, you can copy and paste it into the address bar in your browser, or retype it there. Once you return to LectureBank, we will give you instructions for resetting your password.

Regards,
The LectureBank.org Team";

		$mail = new PHPGMailer();
		$mail->Username = 'admin@lecturebank.org'; 
		$mail->Password = 'Grow$T3M';
		$mail->From = 'admin@lecturebank.org'; 
		$mail->FromName = 'LectureBank Administrator';
		$mail->Subject = 'LectureBank.org Password Reset';
		$mail->AddAddress($email);
		$mail->Body = $forgotpwemail;
		$mail->Send();
		
		echo('<h1>Forgot Password</h1>');
		echo('<h2>Request Successfully Submitted</h2>');
		echo('<p>You will soon be recieving an email at the indicated address that includes instructions and a link to reset your password.</p>');
	} else {
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;That email address was not found in our records. Please try again.';
		include('forgotpwform.php');
	}
	@mysql_free_result($checkemailresult);
  }
} elseif ($_POST['form_submitted'] == '2') {
	//they changed their password
	//Sanitize the POST values
	$password = clean($_POST['password']);
	$passwordconfirm = clean($_POST['passwordconfirm']);
	$resetkey = clean($_POST['reset_key']);
	
	$resetchkqry = "SELECT * FROM users WHERE status = 'forgotpw' AND activationkey = '$resetkey'";

	$resetchkresult = mysql_query($resetchkqry);
	if($resetchkresult && (mysql_num_rows($resetchkresult) > 0)) {
		if(strlen($password)<6){
			$pwerrors = true;
			$password_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;Must be at least <strong>6</strong> characters.';
		}
		if(strlen($password)>32){
			$pwerrors = true;
			$password_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;Must be less than <strong>32</strong> characters.';
		}
		if(strcmp($password, $passwordconfirm) != 0) {
			$pwerrors = true;
			$password_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;Passwords must match.';
		}
		
		if($pwerrors) {
			$queryString = $resetkey;
			include('resetpwform.php');
		} else {
			$hashedpw = md5($password);
			$resetqry = "UPDATE users SET activationkey = NULL, status = 'activated', password = '$hashedpw' WHERE activationkey = '$resetkey'";
			mysql_query($resetqry);
			echo('<h1>Password Changed</h1>');
			echo('<h2>You can now login and resume using your LectureBank account.</h2>');
		}
	} else {
		echo "<h1>There was a problem resetting your password.</h1>";
		echo "<h2>Either your password has already been reset, we're experiencing difficulties, or you reached this page by mistake.</h2>";
		echo "<p>Try logging in, or try clicking the link you used to reach this page again. If you copied and pasted this address into your web browser, or if you typed it manually, try that one more time-you may have missed a part of it. Otherwise, wait and try again later, or email us at <a href='mailto:admin@lecturebank.org'>admin@lecturebank.org</a> to notify us that there was a problem. Thanks for your patience!</p>";
	}
	
} elseif ($_SERVER['QUERY_STRING']) {
	//activate and change the password
	$queryString = $_SERVER['QUERY_STRING'];
	
	$changechkqry = "SELECT * FROM users WHERE status = 'forgotpw' AND activationkey = '$queryString'";

	$changechkresult = mysql_query($changechkqry);
	if($changechkresult && (mysql_num_rows($changechkresult) > 0)) {
		include('resetpwform.php');
	} else {
		echo "<h1>There was a problem resetting your password.</h1>";
		echo "<h2>Either your password has already been reset, we're experiencing difficulties, or you reached this page by mistake.</h2>";
		echo "<p>Try logging in, or try clicking the link you used to reach this page again. If you copied and pasted this address into your web browser, or if you typed it manually, try that one more time-you may have missed a part of it. Otherwise, wait and try again later, or email us at <a href='mailto:admin@lecturebank.org'>admin@lecturebank.org</a> to notify us that there was a problem. Thanks for your patience!</p>";
	}
	@mysql_free_result($changechkresult);
} else {
	//display normal forgot password form
	include('forgotpwform.php');
}

	include('footer.php');
?>