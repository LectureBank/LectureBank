<?php
session_start();

if ($_SESSION['logged_in'] == true){
$username = $_SESSION['username'];
header('Content-type:image/png;');
header('Content-Disposition: attachment;filename="'.$username.'_lecturebankqr.png"');

$gquery_url = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=lecturebank.org/".$username;
	
$ch = curl_init();
$timeout = 5; // set to zero for no timeout
curl_setopt ($ch, CURLOPT_URL, $gquery_url);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
$google_response = curl_exec($ch);
curl_close($ch);

echo $google_response;

// $p = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=lecturebank.org/".$username;
// $a = file_get_contents($p);
// echo $a
} else {
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
}
?>