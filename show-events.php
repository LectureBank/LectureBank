<?php
	session_start();
	
	/* Connect to the database */
	require_once('config/database-connect.php');
	
	$today = date("Y-m-d");
	
	$qry = "SELECT lectures.id, lectures.title, lectures.start, lectures.eventname, lectures.abstract, lectures.link, lectures.timezone, institutions.name AS inst, users.name AS speaker, users.username FROM lectures, institutions, users WHERE lectures.start >= '$today' AND institutions.id = lectures.loc_id AND users.id_user = lectures.creator ORDER BY lectures.start ASC";
	$result = mysql_query($qry);
	while($row = mysql_fetch_array($result)) {
		$eventlist[] = array(id => $row['id'], title => $row['title'], month => date('m',strtotime($row['start'])), day => date('d',strtotime($row['start'])), starttime => date('H:i',strtotime($row['start'])), datetime => date('Y-m-d\TH:i:s'.sprintf("%+03d",$row['timezone']).':00',strtotime($row['start'])), eventname => $row['eventname'], abst => $row['abstract'], link => $row['link'], inst => $row['inst'], speaker => $row['speaker'], username => $row['username']);
	}
	@mysql_free_result($result);
	
	$title = "Events";
	include('header.php');
	
?>

<header>
<hgroup>
<h1>Events</h1>
<h2>All that's happening around you</h2>
</hgroup>
</header>

<?php
	if (!empty($eventlist)) {
	$thismonth = date("m");
	
	echo("<section>");
	echo("<header>");
	echo('<h3 style="text-align:left;background:#EEEEFF;">This month</h3>');
	echo("</header>");
	foreach($eventlist as $event) {
		if($event['month'] == $thismonth) {
		echo ('<article class="event">');
		echo ("<header>");
		echo ("<time datetime=".$event['datetime'].">");
		echo ("<hgroup>");
		echo ("<h1>".$event['day']."</h1>");
		echo ("<h2>".$event['starttime']."</h2>");
		echo ("</hgroup>");
		echo ("</time>");
		echo ("</header>");
		echo ("<div>");
		echo (!empty($event['link'])) ? "<h4><a href=\"".$event['link']."\">".$event['title']."</a></h4>" : "<h4>".$event['title']."</h4>";
		echo (!empty($event['speaker'])) ? '<span class="speaker">Speaker: <a href="/'.$event['username'].'">'.$event['speaker'].'</a></span>' : '<span class="speaker">Speaker: <a href="/'.$event['username'].'">'.$event['username'].'</a></span>';
		echo (!empty($event['locdetail']) || !empty($event['eventname']) || !empty($event['inst'])) ? '<br /><span class="inforow">' : "";
		echo (!empty($event['locdetail'])) ? $event['locdetail'] : "";
		echo ((!empty($event['locdetail']) && !empty($event['eventname'])) || (!empty($event['locdetail']) && !empty($event['inst']))) ? "&bull;" : "";
		echo (!empty($event['eventname'])) ? $event['eventname'] : "";
		echo (!empty($event['eventname']) && !empty($event['inst'])) ? "&bull;" : "";
		echo (!empty($event['inst'])) ? $event['inst'] : "";
		echo (!empty($event['locdetail']) || !empty($event['eventname']) || !empty($event['inst'])) ? '</span>' : "";
		echo (!empty($event['abst'])) ? "<p>".$event['abst']."</p>" : "";
		
		$qry = "SELECT tags.tag FROM tags, lecturetags WHERE lecturetags.lecture = ".$event['id']." AND tags.id = lecturetags.tag";
		$result = mysql_query($qry);
		while($row = mysql_fetch_array($result)) {
			$event['tags'][] = '<a href="/search/'.str_replace(" ","+",$row['tag']).'">'.$row['tag'].'</a>';
		}
		@mysql_free_result($result);
		$tagstring = implode(", ", $event['tags']);
		if (!empty($tagstring)) {
			echo('<span class="tags">');
			echo($tagstring);
			echo('</span>');
		}
		
		echo ("</div>");
		echo ("</article>");
		} 
	}
	echo ("</section>");
	
	$thismonth = $thismonth+1;
	
	echo("<section>");
	echo("<header>");
	echo('<h3 style="text-align:left;background:#EEEEFF;">Next month</h3>');
	echo("</header>");
	foreach($eventlist as $event) {
		if($event['month'] == $thismonth) {
		echo ('<article class="event">');
		echo ("<header>");
		echo ("<time datetime=".$event['datetime'].">");
		echo ("<hgroup>");
		echo ("<h1>".$event['day']."</h1>");
		echo ("<h2>".$event['starttime']."</h2>");
		echo ("</hgroup>");
		echo ("</time>");
		echo ("</header>");
		echo ("<div>");
		echo (!empty($event['link'])) ? "<h4><a href=\"".$event['link']."\">".$event['title']."</a></h4>" : "<h4>".$event['title']."</h4>";
		echo (!empty($event['speaker'])) ? '<span class="speaker">Speaker: <a href="/'.$event['username'].'">'.$event['speaker'].'</a></span>' : '<span class="speaker">Speaker: <a href="/'.$event['username'].'">'.$event['username'].'</a></span>';
		echo (!empty($event['locdetail']) || !empty($event['eventname']) || !empty($event['inst'])) ? '<br /><span class="inforow">' : "";
		echo (!empty($event['locdetail'])) ? $event['locdetail'] : "";
		echo ((!empty($event['locdetail']) && !empty($event['eventname'])) || (!empty($event['locdetail']) && !empty($event['inst']))) ? "&bull;" : "";
		echo (!empty($event['eventname'])) ? $event['eventname'] : "";
		echo (!empty($event['eventname']) && !empty($event['inst'])) ? "&bull;" : "";
		echo (!empty($event['inst'])) ? $event['inst'] : "";
		echo (!empty($event['locdetail']) || !empty($event['eventname']) || !empty($event['inst'])) ? '</span>' : "";
		echo (!empty($event['abst'])) ? "<p>".$event['abst']."</p>" : "";
		
		$qry = "SELECT tags.tag FROM tags, lecturetags WHERE lecturetags.lecture = ".$event['id']." AND tags.id = lecturetags.tag";
		$result = mysql_query($qry);
		while($row = mysql_fetch_array($result)) {
			$event['tags'][] = '<a href="/search/'.str_replace(" ","+",$row['tag']).'">'.$row['tag'].'</a>';
		}
		@mysql_free_result($result);
		$tagstring = implode(", ", $event['tags']);
		if (!empty($tagstring)) {
			echo('<span class="tags">');
			echo($tagstring);
			echo('</span>');
		}
		
		echo ("</div>");
		echo ("</article>");
		} 
	}
	echo ("</section>");
	
	}
	include('footer.php');
?>