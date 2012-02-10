<?php //this file cannot contain carriage returns as part of the HTML ?><form id="loginForm" method="post" action="/login-check.php"><div class="error"><?php
		if($_SESSION['LOGIN_ERRORS']) {
			foreach($_SESSION['LOGIN_ERRORS'] as $msg) {
				echo $msg;
				echo "<br />";
			}
			unset($_SESSION['LOGIN_ERRORS']);
		}
		?></div><div class="loginRow"><label for="loginemail">Email&nbsp;</label><input name="loginemail" type="email" id="loginemail" /></div><div class="loginRow"><label for="loginpassword">Password&nbsp;</label><input name="loginpassword" type="password" id="loginpassword" /></div><div class="loginRow"><input type="submit" name="Submit" value="Sign In" onfocus="true"/><label for="remember"><input name="remember" type="checkbox" id="remember" value="1"/>Keep me logged in</label></div><span style="text-align: center;"><a  href="#">Forget password?</a>&nbsp;<a href="/signup.php">Sign Up!</a></span><input type="hidden" name="login_submitted" value="1" /></form><div class="clear"></div>