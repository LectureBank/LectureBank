<?php
	session_start();
	function strleft($s1, $s2) { 
		return substr($s1, 0, strpos($s1, $s2));
	}   
			  
	function selfURL() { 
		if(!isset($_SERVER['REQUEST_URI'])) { 
		   $serverrequri = $_SERVER['PHP_SELF']; 
		}
		else { 
		   $serverrequri = $_SERVER['REQUEST_URI']; 
		} 
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
		$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
		$_SESSION['ref'] = $protocol."://".$_SERVER['SERVER_NAME'].$port.$serverrequri; 
	}	
	
	// Check if logged in
	
	if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
		$logged_in = true;
	} else {
		$logged_in = false;
	}
	
	// Check if protect flag set
	
	if(isset($_SESSION['protect_flag']) && $_SESSION['protect_flag']) {
		$protect_flag = true;
	} else {
		$protect_flag = false;
	}

	// If protected and not logged in, kick back to referrer or index
	
	if($protected && !$logged_in) {
		$_SESSION['protect_flag'] = true;
		// If we have a referrer and it wasn't protected, kick back to it
		if(isset($_SESSION['ref'])) {
			$_SESSION['protected_page'] = $_SERVER['PHP_SELF'];
			header("Location: " . $_SESSION['ref']);
		// If we have no referrer, coming in cold so kick to index
		} else {
			$_SESSION['protected_page'] = $_SERVER['PHP_SELF'];
			header("Location: index.php");
		}
	} else {

	// Unset that we were coming from a protected page
	unset($_SESSION['protect_flag']);
	
	// Clear protected page flag if logged in and on a protected page
	if($protected) unset($_SESSION['protected_page']);
	
	// Set referrer		
	selfURL();

	}
?>
<!DOCTYPE html>
<html lang="en">
<?php 
if($search){
	echo('<head profile="http://a9.com/-/spec/opensearch/1.1/">');
} else {
	echo('<head>');
}
?>

<meta charset="utf-8" />
<meta name="description" content="LectureBank is a networking tool designed to connect researchers and event organizers in related scientific fields. Create and discover opportunities to speak and explore new talent for lectures, seminars, conferences, and symposia." />
<?php if($keywords) echo('<meta name="keywords" content="'.$keywords.'" />'); ?>
<?php if($author) echo('<meta name="author" content="'.$author.'" />'); ?>
<meta name="google-site-verification" content="4wiL81U5wsXJ0uhXk-LK-EA0NE9sokF2T_ehY53R9Os" />
<title><?php echo $title; ?> | LectureBank</title>
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
<link rel="stylesheet" href="/css/site.css" />
<?php if($search) echo('<link rel="alternate" type="application/rss+xml" title="RSS" href="/searchrss.php?query='.$input.'">'); ?>
<link rel="search" type="application/opensearchdescription+xml" href="/opensearchdescription.xml" title="LectureBank Site Search" />
<link rel="P3Pv1" href="/w3c/p3p.xml">

<!--[if lt IE 9]>
<style type="text/css">
#theform #pt4 {
	padding: 2em 1em 1em 1em;
	}
</style>
<script>
  var e = ("article,footer,header,hgroup,menu,nav,section,time").split(',');
  for (var i = 0; i < e.length; i++) {
    document.createElement(e[i]);
  }
</script>
<![endif]-->

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<?php if($uservalidation) include('includes/validationscripts.php'); ?>
<?php if($datepicker) include('includes/datepickscripts.php'); ?>
<?php if($tokeninput) include('includes/tokeninputscripts.php'); ?>
<?php if($profile) include('includes/profilescripts.php'); ?>
<?php if($protect_flag) include('includes/popoverscripts.php'); ?>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-22753773-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body>
<div id="container">
  <header id="sitewide">
  		<div id="skip">
			<a href="#mainContent">Skip to Main Content</a>
		</div>
 		<a href="index.php"><img src="/images/lblogo_beta.png" width="504" height="144" alt="LectureBank, The Meta-Researcher" style="float:left;" /></a>
<div style="float: right;">
<?php if($logged_in) {
	include('loggedinpanel.php');
} else {
	include('loginform.php');
}
?>
</div>
<nav id="main">
	<ul>
    <?php if($logged_in) {
		echo('<li><a href="/profile.php">Home</a></li>');
	} else {
		echo('<li><a href="/">Home</a></li>');
	}
	?>
	  
			<li><a href="/events">Events</a></li>
			<li><a href="#">Planners</a></li>
			<li><a href="#">Speakers</a></li>
			<li><a href="/about.php">About Us</a></li>
	</ul>
</nav>
        <br />
  </header>
  <div id="mainContent">
  <br />