<?php
session_start();

require_once('config/database-connect.php');

$email = clean($_POST['loginemail']); 
$password = md5(clean($_POST['loginpassword']));
$login_errors = array();
$_SESSION['LOGIN_ERRORS']=NULL;


/**
 * Checks whether or not the given email is in the
 * database, if so it checks if the given password is
 * the same password in the database for that user.
 * If the user doesn't exist or if the passwords don't
 * match up, it returns an error code (1 or 2). 
 * On success it returns 0.
 */
function confirmUser($email, $password){

   /* Verify that user is in database */
   $qry = "select password, status from users where email = '$email'";
   $result = mysql_query($qry);
   if(!$result || (mysql_numrows($result) < 1)){
      return 1; //Indicates email failure
   }

   /* Retrieve password from result, strip slashes */
   $dbarray = mysql_fetch_array($result);
   $dbarray['password']  = stripslashes($dbarray['password']);
   $dbarray['status']  = stripslashes($dbarray['status']);
   
   @mysql_free_result($result);
   
   /* Validate that password is correct */
   if(strcmp($password,$dbarray['password']) == 0){
	   if(strcmp($dbarray['status'],'activated') == 0){
      		return 0; //Success! email and password confirmed, account active
	   } elseif(strcmp($dbarray['status'],'forgotpw') == 0) {
		    $rememberpwqry = "UPDATE users SET activationkey = NULL, status = 'activated' WHERE email = '$email'";
			mysql_query($rememberpwqry);
			return 0; //User forgot but then remembered password, so we'll change the status back-still successful
	   } else {
		    return 3; //Indicates inactive account
	   }
   }
   else{
      return 2; //Indicates password failure
   }
}

/**
 * checkLogin - Checks if the user has already previously
 * logged in, and a session with the user has already been
 * established. Also checks to see if user has been remembered.
 * If so, the database is queried to make sure of the user's 
 * authenticity. Returns true if the user has logged in.
 */
function checkLogin(){
   /* Check if user has been remembered */
   if(isset($_COOKIE['cookemail']) && isset($_COOKIE['cookpass'])){
      $_SESSION['email'] = $_COOKIE['cookemail'];
      $_SESSION['password'] = $_COOKIE['cookpass'];
   }

   /* email and password have been set */
   if(isset($_SESSION['email']) && isset($_SESSION['password'])){
      /* Confirm that email and password are valid */
      if(confirmUser($_SESSION['email'], $_SESSION['password']) != 0){
         /* Variables are incorrect, user not logged in */
         unset($_SESSION['email']);
         unset($_SESSION['password']);
         return false;
      }
      return true;
   }
   /* User not logged in */
   else{
      return false;
   }
}

/**
 * Checks to see if the user has submitted his
 * email and password through the login form,
 * if so, checks authenticity in database and
 * creates session.
 */
if($_POST['login_submitted'] == '1'){

   $result = confirmUser($email, $password);

   /* Check error codes */
   if($result != 0){
   if($result == 1){
      $login_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;No account with that email.';
   }
   else if($result == 2){
      $login_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;Incorrect password.';
   }
   else if($result == 3){
      $login_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;Account must be activated.';
   }
   $_SESSION['LOGIN_ERRORS'] = $login_errors;
   session_write_close();
   header("Location: " . $_SESSION['ref']);
   exit;
   }

   /* email and password correct, register session variables */
   
   $qry = "SELECT id_user, username FROM users WHERE email = '$email'";
   $result = mysql_query($qry);
   $row = mysql_fetch_array($result);
   $uid  = $row['id_user'];
   $username = $row['username'];
   $_SESSION['logged_in'] = true;
   $_SESSION['uid'] = $uid;
   $_SESSION['username'] = $username;
   $_SESSION['email'] = $email;

   /**
    * This is the cool part: the user has requested that we remember that
    * he's logged in, so we set two cookies. One to hold his email,
    * and one to hold his md5 encrypted password. We set them both to
    * expire in 100 days. Now, next time he comes to our site, we will
    * log him in automatically.
    */
   if(isset($_POST['remember'])){
      setcookie("cookemail", $_SESSION['email'], time()+60*60*24*100, "/");
      setcookie("cookpass", $_SESSION['password'], time()+60*60*24*100, "/");
   }
   if(isset($_SESSION['protected_page'])){
	   session_write_close();
	   header("Location: ".$_SESSION['protected_page']);
   } else {
	   session_write_close();
	   header("Location: /".$username);
   }
   return;
}

?>