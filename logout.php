<?php
session_start();
unset($_SESSION['uid']);
unset($_SESSION['username']);
unset($_SESSION['email']);
unset($_SESSION['password']);
session_destroy();
header("Location: index.php");
?>