<?php

/* Connect to the database */
require_once('config/database-connect.php');
require_once('includes/wordhelper.php');
	
$input = clean($_GET["event"]);
	
	$qry = "SELECT lectures.title, lectures.start, lectures.end, lectures.timezone, lectures.eventname, lectures.locdetail, lectures.abstract, lectures.link, institutions.name AS loc, users.name AS creator, users.email AS creatoremail, users.username FROM lectures, institutions, users WHERE lectures.id='$input' AND users.id_user=lectures.creator AND institutions.id=lectures.loc_id";
$result = mysql_query($qry);
$row = mysql_fetch_array($result);

if(!empty($row)) {
	
$title = $row['title'];
$startdate = strtotime($row['start']);
date_timezone_set($startdate, timezone_open('Etc/GMT'.$row['timezone']));
$startdate = gmdate('Ymd\THis\Z', $startdate);
if(!empty($row['end'])) {
	$enddate = strtotime($row['end']);
	date_timezone_set($enddate, timezone_open('Etc/GMT'.$row['timezone']));
	$enddate = gmdate('Ymd\THis\Z', $enddate);
} else {
	$enddate = strtotime($row['start']);
	date_timezone_set($enddate, timezone_open('Etc/GMT'.$row['timezone']));
	$enddate = gmdate('Ymd\THis\Z', $enddate+3600);
}
$curdate = gmdate('Ymd\THis\Z');
$email = $row['creatoremail'];
$username = $row['username'];
$titleslug = cleanSlug($title);
$name = $row['eventname'];
$abst = $row['abstract'];
if(!empty($row['link'])){
	$link = $row['link'];
} else {
	$link = 'http://www.lecturebank.org/'.$username.'/talks/'.$titleslug;
}
if(!empty($row['locdetail'])){
	$newlines = array("\n", "\r\n", "\r");
    $detail = str_replace($newlines, ' ', $row['locdetail']);
	$loc = $detail.', '.$row['loc'];
} else {
	$loc = $row['loc'];
}
$creator = $row['creator'];

	$output .= "BEGIN:VCALENDAR\r\n";
	$output .= "VERSION:2.0\r\n";
	$output .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
	$output .= "BEGIN:VEVENT\r\n";
	$output .= "UID:".$username."-".$titleslug."@lecturebank.org\r\n";
	$output .= "ORGANIZER;CN=".$creator.":mailto:".$email."\r\n";
	$output .= "DESCRIPTION:Speaker: ".$creator;
	if(!empty($abst)){
		$output .= "\\n\\nAbstract: ".$abst."\r\n";
	} else {
		$output .= "\r\n";
	}
	
	$output .= "DTEND:".$enddate."\r\n";
	$output .= "DTSTAMP:".$curdate."\r\n";
	$output .= "DTSTART:".$startdate."\r\n";
	$output .= "LAST-MODIFIED:".$curdate."\r\n";
	$output .= "LOCATION:".$loc."\r\n";
	$output .= "URL:".$link."\r\n";
	$output .= "PRIORITY:5\r\n";
	$output .= "SEQUENCE:0\r\n";
	$output .= "SUMMARY;LANGUAGE=en-us:".$title."\r\n";
	$output .= "END:VEVENT\r\n";
	$output .= "END:VCALENDAR\r\n";

	//This is the most important coding.
	header("Content-Type: text/Calendar");
	header("Content-Disposition: inline; filename=".$titleslug.".ics");

	echo($output);
	
	}
	
	@mysql_free_result($result);
?>