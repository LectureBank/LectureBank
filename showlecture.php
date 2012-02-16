<?php
	require_once('config/database-connect.php');
	require_once('includes/wordhelper.php');
	
	$username = clean($_GET["user"]);
	$lecturetok = clean($_GET["lecture"]);
	
	$lecturesqry = "SELECT lectures.id AS id, lectures.title AS title FROM lectures, users WHERE users.username = '$username' && lectures.creator = users.id_user";
	$lecturesresult = mysql_query($lecturesqry);
	while($lecturecheck = mysql_fetch_array($lecturesresult)) {
		if (strcmp(cleanSlug($lecturecheck['title']),$lecturetok) == 0) {
			$lectureid = $lecturecheck['id'];
			break;
		}
	}
	@mysql_free_result($lecturesresult);
	
	$title = "Event Details";
	if($lectureid) {
		$lectureqry = "SELECT lectures.title AS title, lectures.eventname AS eventname, lectures.abstract AS abst, lectures.link AS link, lectures.locdetail AS locdetail, lectures.start AS start, lectures.end AS end, lectures.sequence AS sequence, institutions.name AS inst, institutions.address AS addr, institutions.city AS city, institutions.state AS state, institutions.zip AS zip, institutions.lat AS lat, institutions.lon AS lon, users.name AS speaker FROM lectures, institutions, users WHERE lectures.id=$lectureid && institutions.id=lectures.loc_id && users.id_user=lectures.creator";
		$lectureresult = mysql_query($lectureqry);
		$lecture = mysql_fetch_array($lectureresult); 
		$lecture['address'] = $lecture['city'].', '.$lecture['state'].' '.$lecture['zip'];
		$infostring = '<div style=\"font-size:x-small\">';
		$infostring .= '<strong>'.$lecture['inst'].'</strong><br />';
		if(!empty($lecture['addr'])) {
			$infostring .= $lecture['addr'].'<br />';
		}
		$infostring .= $lecture['address'];
		$infostring .= "</div>";
		$lecture['machinedate'] = date('Y-m-d',strtotime($lecture['start']));
		$lecture['humanstart'] = date('g:iA',strtotime($lecture['start']));
		if($lecture['end']){
			$lecture['humanend'] = date('g:iA',strtotime($lecture['end']));
			$lecture['microend'] = date('c',strtotime($lecture['start']));
		}
		$lecture['humandate'] = date('m/d/Y',strtotime($lecture['start']));
		$lecture['microstart'] = date('c',strtotime($lecture['start']));
		
		
		@mysql_free_result($lectureresult);
		
		$lecturetagqry = "SELECT tags.id, tags.tag FROM tags, lecturetags WHERE lecturetags.lecture = '$talkid' && tags.id = lecturetags.tag";
		$lecturetagresult = mysql_query($lecturetagqry);
		while($lecturetag = mysql_fetch_array($lecturetagresult)) {
			$lecturetags[] = $lecturetag['tag'];
		}
		@mysql_free_result($lecturetagresult);
		
		$title = $lecture['title'].' '.$title;
		$author = $lecture['speaker'];
		if(!empty($lecturetags)){
			$metakeywords = implode(",", $lecturetags);
		}
		if(!empty($lecture['abst'])){
			$metadescription = $lecture['abst'];
		}
		include('header.php');
		
		echo ('<article itemscope itemtype="http://schema.org/EducationEvent">');
		echo ("<header>");
		echo ("<hgroup>");
		echo ('<h1 itemprop="name">'.$lecture['title'].'</h1>');
		echo('<h3><time datetime="'.$lecture['machinedate'].'">'.$lecture['humandate'].'</time>, Starts at: <time itemprop="startDate" content="'.$lecture['microstart'].'" datetime="'.$lecture['microstart'].'">'.$lecture['humanstart'].'</time>');
		if($lecture['end']) {
			echo(', Ends at: <time itemprop="endDate" content="'.$lecture['microend'].'" datetime="'.$lecture['microend'].'">'.$lecture['humanend'].'</time></h3>');
		} else {
			echo('</h3>');
		}
		echo('<h3><span itemprop="performers" itemscope itemtype="http://schema.org/Person"><a itemprop="url" href="/'.$username.'" rel="author"><span itemprop="name">'.$lecture['speaker'].'</span></a></span>, ');
		echo('<span itemprop="location" itemscope itemtype="http://schema.org/Place">');
		if(!empty($lecture['locdetail'])) echo('<span itemprop="description">'.$lecture['locdetail'].'</span>, ');
		echo('<span itemprop="name">'.$lecture['inst'].'</span></span></h3>');
		echo ("</hgroup>");
		echo ("</header>");
		echo ('<p itemprop="description">'.$lecture['abst'].'</p>');
		if(!empty($lecture['link'])) echo ('<a itemprop="url" href="'.$lecture['link'].'" target="_blank">'.$lecture['link'].'</a> <img alt="External link" src="/images/external-link-icon.gif"><br />');
		echo('<span class="tags">');
		foreach($lecturetags as $tag) {
			echo('<a href="/search/'.str_replace(" ","+",$tag).'" rel="tag">'.$tag.'</a> ');
		}
		echo('</span><br />');
		echo('<div class="g-plusone" data-size="small" data-href="http://www.lecturebank.org/'.$lecture['username'].'/talks/'.cleanSlug($lecture['title']).'"></div>');
		echo('<a href="/calendarevent.php?event='.$lectureid.'"><img alt="Download iCal" src="/images/icalbutton.gif"></a>');
		echo('<script type="text/javascript">
		function initialize() {
		var myLatLng = new google.maps.LatLng('.$lecture['lat'].', '.$lecture['lon'].');
		var myOptions = {
			zoom: 14,
			center: myLatLng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		  }
		var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		
		var infowindow = new google.maps.InfoWindow({
    		content: "'.$infostring.'"
		});
		  
		var marker = new google.maps.Marker({
			  position: myLatLng,
			  map: map,
			  title:"'.$lecture['inst'].'"
		});

		google.maps.event.addListener(marker, \'click\', function() {
			infowindow.open(map,marker);
		});
		}
		
		function loadScript() {
		  var script = document.createElement("script");
		  script.type = "text/javascript";
		  script.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyACikEMtW-OfFaYCS-C-0cPyTPgeT3VpPw&sensor=false&callback=initialize";
		  document.body.appendChild(script);
		}
		
		window.onload = loadScript;
		</script>');
		echo('<div id="map_canvas" style="width:650px;height:300px;margin-left:auto;margin-right:auto;"></div>');
		echo ("</article>");
	}
	include('footer.php');
?>