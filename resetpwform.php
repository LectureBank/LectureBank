<h1>Reset Password</h1>
<h2>Enter a new password for your account</h2>
<form action="/forgot-password.php" id="resetpw" method="post">
<div class="help">Passwords must be 6-32 characters in length.
<div id="pwstatus"><span class="error">
<?php
	if($password_errors) {
		foreach($password_errors as $msg) {
			echo $msg;
			echo "<br />";
		}
		unset($form_errors);
	}
?></span></div></div><br />
<label for="password">Password</label>
<input type="password" id="password" name="password" /><br />
<label for="passwordconfirm">Repeat Password</label>
<input type="password" id="passwordconfirm" name="passwordconfirm" />
<input type="hidden" name="form_submitted" value="2" />
<input type="hidden" name="reset_key" value="<?php echo($queryString) ?>" /><br /><br />
<input type="submit" value="Submit" />
</form>
<br />