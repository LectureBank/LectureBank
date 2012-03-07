<h1>Forgot Password</h1>
<h2>Enter your email address and we'll help you out</h2>
<div class="error">
<?php
	if($form_errors) {
		foreach($form_errors as $msg) {
			echo $msg;
			echo "<br />";
		}
		unset($form_errors);
	}
?></div>
<form action="/forgot-password.php" method="post">
<label for="email">Email</label>
<input type="text" size="80" name="email" <?php if($_POST['form_submitted'] == '1' && $email) echo('value="'.$email.'"'); ?> /><br /><br />
<label for="recaptcha_response_field">Confirm you're a person</label><br />
<?php
$publickey = "6LfWWc4SAAAAAEowLGUTTFycOd0vTzn3_j7v3pMD";
echo recaptcha_get_html($publickey);
?><br />
<input type="hidden" name="form_submitted" value="1" />
<input type="submit" value="Submit" />
</form>
<br />