<?php
	session_start();
	$title = "One Step Signup";
	$uservalidation = true;
    include('header.php');
?>
  <h1>One Step Signup</h1>
 
<h2>You're just seconds away from being registered.</h2>

<form id="theform" action="register.php" enctype="multipart/form-data" method="post">
	<fieldset id="pt1">
		<legend align="center"><span>Step </span>1. <span>: Username details</span></legend>
		<h3>Pick a LectureBank username.</h3>
		<div class="help">Your username will be part of your profile URL.
        <div id="usrstatus"><span class="error"><?php
		if($_SESSION['USERNAME_ERRORS']) {
			foreach($_SESSION['USERNAME_ERRORS'] as $msg) {
				echo $msg;
				echo "<br />";
			}
			unset($_SESSION['USERNAME_ERRORS']);
		}
		?></span></div></div>
		<label for="username">Username</label>
		<input type="text" name="username" id="username" tabindex="1" />
	</fieldset>    
	<fieldset id="pt2">
		<legend align="center"><span>Step </span>2. <span>: Email details</span></legend>
		<h3>Enter your email address.</h3>
		<div class="help">You must enter a valid email address to activate your account.
        <div id="emailstatus"><span class="error"><?php
		if($_SESSION['EMAIL_ERRORS']) {
			foreach($_SESSION['EMAIL_ERRORS'] as $msg) {
				echo $msg;
				echo "<br />";
			}
			unset($_SESSION['EMAIL_ERRORS']);
		}
		?></span></div></div>
		<label for="email">Email</label>
		<input type="email" id="email" name="email" tabindex="2" />
	</fieldset>
    <fieldset id="pt3">
		<legend align="center"><span>Step </span>3. <span>: Password</span></legend>
		<h3>Choose a password for your new account.</h3>
		<div class="help">Passwords must be 6-32 characters in length.
        <div id="pwstatus"><span class="error"><?php
		if($_SESSION['PASSWORD_ERRORS']) {
			foreach($_SESSION['PASSWORD_ERRORS'] as $msg) {
				echo $msg;
				echo "<br />";
			}
			unset($_SESSION['PASSWORD_ERRORS']);
		}
		?></span></div></div>
		<label for="password">Password</label>
		<input type="password" id="password" name="password" tabindex="3" />
		<label for="passwordconfirm">Repeat Password</label>
		<input type="password" id="passwordconfirm" name="passwordconfirm" tabindex="4" />
	</fieldset>
	<fieldset id="pt4">
		<legend>Step 4  : Submit form</legend>
		<h3>Terms of Service</h3>
		<div id="disclaimer">By clicking the &#8220;Complete Signup&#8221; button,
			I am attaching my electronic signature to and agreeing to the LectureBank <a href="/tos.php">Terms of Service Agreement</a> and <a href="/privacy.php">Privacy Policy</a>.</div>
        <input type="hidden" name="form_submitted" value="1" />
		<input type="submit" id="bigbutton" tabindex="5" value="Complete Signup &raquo;" />
	</fieldset>
	<div style="clear:both;"></div>
</form>
<br />
<?php
	include('footer.php');
?>