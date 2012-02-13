<?php
$input = clean($_GET["event"]);
	
	$qry = "SELECT lectures.title, lectures.date, lectures.eventname, lectures.abstract, lectures.link, institutions.name AS loc, users.name AS creator FROM lectures, institutions, users WHERE lectures.id='$input' AND users.id_user=lectures.creator AND institutions.id=lectures.loc_id";
$result = mysql_query($qry);
$row = mysql_fetch_array($result);

if(!empty($row)) {
	
$title = $row['title'];
$date = date('m/d/Y',strtotime($row['date']));
$name = $row['eventname'];
$abst = $row['abstract'];
$link = $row['link'];
$loc = $row['loc'];
$creator = $row['creator'];

	//This is the most important coding.
	header("Content-Type: text/Calendar");
	header("Content-Disposition: inline; filename=lecturebank_$id.ics");

	echo "BEGIN:VCALENDAR\r\n";
	echo "VERSION:2.0\r\n";
	echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
	echo "BEGIN:VEVENT\r\n";
	echo "DESCRIPTION:Speaker: $creator";
	if(!empty($abst)){
		echo "\\n\\nAbstract: $abst\r\n";
	} else {
		echo "\r\n";
	}
	
	echo "DTEND:20100208T040000Z\n";
	echo "DTSTAMP:20100109T093305Z\n";
	echo "DTSTART:20100208T003000Z\n";
	echo "LAST-MODIFIED:20091109T101015Z\n";
	echo "LOCATION:$event_query_row[location]\n";
	echo "PRIORITY:5\n";
	echo "SEQUENCE:0\r\n";
	echo "SUMMARY;LANGUAGE=en-us: $title\r\n";
	echo "END:VEVENT\n";
	echo "END:VCALENDAR\n";
	
	}
	
	@mysql_free_result($result);
?>