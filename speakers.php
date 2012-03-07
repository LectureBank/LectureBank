<?php
session_start();

require_once('config/database-connect.php');
require_once('includes/wordhelper.php');

$input = clean($_GET["query"]);

$uid = $_SESSION['uid'];
$email = $_SESSION['email'];
$logged_in = !!(isset($_SESSION['logged_in']) && $_SESSION['logged_in']);

if($logged_in){
	$zipqry = "SELECT ziplocation.lat AS lat, ziplocation.lon AS lon FROM users, ziplocation WHERE users.id_user = '$uid' AND ziplocation.zip = users.zip";
	$zipresult = mysql_query($zipqry);
	$userzip = mysql_fetch_array($zipresult);
	@mysql_free_result($zipresult);
	if (!empty($userzip['lat']) && !empty($userzip['lon'])) {
   		$lat  = $userzip['lat'];
		$lon  = $userzip['lon'];
	} else {
		$zipqry = "SELECT institutions.lat AS lat, institutions.lon AS lon FROM userinstitutions, institutions WHERE userinstitutions.uid = '$uid' AND institutions.id = userinstitutions.instid";
		$zipresult = mysql_query($zipqry);
		$instzip = mysql_fetch_array($zipresult);
		@mysql_free_result($zipresult);
		if (!empty($userzip['lat']) && !empty($userzip['lon'])) {
			$lat  = $instzip['lat'];
			$lon  = $instzip['lon'];
		}
	}
}

if(!empty($input)){
	//execute search for input
	
	if(isset($lat) && isset($lon)) {
	// incorporate location data into the query
		
	} else {
		// don't incorporate location
		
	}
	
	$title = 'Opportunity Search for "'.$input.'"';
	$search = true;
} else {
	if($logged_in) {
	// look for opportunities with tags like the speaker's
		
		$likemeqry = "SELECT opp.*, users.name AS creator, users.username AS username, institutions.name AS location FROM (SELECT id FROM (SELECT intid AS tag FROM userinterests WHERE uid = '$uid' UNION ALL SELECT researchtags.tag AS tag FROM researchtags, research WHERE research.uid = '$uid' AND researchtags.research = research.id UNION ALL SELECT lecturetags.tag AS tag FROM lecturetags, lectures WHERE lectures.creator = '$uid' AND lecturetags.lecture = lectures.id) AS usertags NATURAL JOIN (SELECT tag, opportunity AS id FROM opportunitytags) AS opptags GROUP BY id ORDER BY RAND() LIMIT 10) AS randopps NATURAL JOIN opportunities AS opp, users, institutions WHERE users.id_user = opp.creator AND institutions.id = opp.loc_id AND (opp.open <= CURDATE()) AND ((opp.close >= CURDATE()) OR (ISNULL(opp.close) AND (opp.start >= CURDATE())))";
		$likemeresult = mysql_query($likemeqry);
		while($likemerow = mysql_fetch_array($likemeresult)) {
			$likeme[] = array(id => $likemerow['id'], creator => $likemerow['creator'], username => $likemerow['username'], location => $likemerow['location'], locdetail => $likemerow['locdetail'], title => $likemerow['title'], descrip => $likemerow['descrip'], link => $likemerow['link']);
		}
	@mysql_free_result($likemeresult);
	}
	
	if(isset($lat) && isset($lon)) {
	// incorporate location data into the query
		
		$closestqry = "SELECT ((ACOS(SIN($lat * PI() / 180) * SIN(institutions.lat * PI() / 180) + COS($lat * PI() / 180) * COS(institutions.lat * PI() / 180) * COS(($lon - institutions.lon) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance, opportunities.id AS id, users.name AS creator, users.username AS username, institutions.name AS location, opportunities.locdetail AS locdetail, opportunities.title AS title, opportunities.descrip AS descrip, opportunities.link AS link FROM opportunities, users, institutions WHERE institutions.id = opportunities.loc_id AND users.id_user = opportunities.creator AND (opportunities.open <= CURDATE()) AND ((opportunities.close >= CURDATE()) OR (ISNULL(opportunities.close) AND (opportunities.start >= CURDATE()))) ORDER BY distance, opportunities.close, opportunities.start";
		$closestresult = mysql_query($closestqry);
		while($closestrow = mysql_fetch_array($closestresult)) {
			$closest[] = array(id => $closestrow['id'], creator => $closestrow['creator'], username => $closestrow['username'], distance => $closestrow['distance'], location => $closestrow['location'], locdetail => $closestrow['locdetail'], title => $closestrow['title'], descrip => $closestrow['descrip'], link => $closestrow['link']);
		}
		@mysql_free_result($closestresult);
	}
		
	$endingqry = "SELECT opportunities.id AS id, users.name AS creator, users.username AS username, institutions.name AS location, opportunities.locdetail AS locdetail, opportunities.title AS title, opportunities.descrip AS descrip, opportunities.link AS link FROM opportunities, users, institutions WHERE institutions.id = opportunities.loc_id AND users.id_user = opportunities.creator AND (open <= CURDATE()) AND ((close >= CURDATE()) OR (ISNULL(close) AND (start >= CURDATE()))) ORDER BY opportunities.close, opportunities.start LIMIT 10";
	$endingresult = mysql_query($endingqry);
		while($endingrow = mysql_fetch_array($endingresult)) {
			$ending[] = array(id => $endingrow['id'], creator => $endingrow['creator'], username => $endingrow['username'], location => $endingrow['location'], locdetail => $endingrow['locdetail'], title => $endingrow['title'], descrip => $endingrow['descrip'], link => $endingrow['link']);
		}
	@mysql_free_result($endingresult);
	
	$recentqry = "SELECT opportunities.id AS id, users.name AS creator, users.username AS username, institutions.name AS location, opportunities.locdetail AS locdetail, opportunities.title AS title, opportunities.descrip AS descrip, opportunities.link AS link FROM opportunities, users, institutions WHERE institutions.id = opportunities.loc_id AND users.id_user = opportunities.creator AND (open <= CURDATE()) AND ((close >= CURDATE()) OR (ISNULL(close) AND (start >= CURDATE()))) ORDER BY opportunities.open DESC, opportunities.timestamp DESC LIMIT 10";
	$recentresult = mysql_query($recentqry);
		while($recentrow = mysql_fetch_array($recentresult)) {
			$recent[] = array(id => $recentrow['id'], creator => $recentrow['creator'], username => $recentrow['username'], location => $recentrow['location'], locdetail => $recentrow['locdetail'], title => $recentrow['title'], descrip => $recentrow['descrip'], link => $recentrow['link']);
		}
	@mysql_free_result($recentresult);
	
	$title = "For Speakers";
}

$profile = true;
include('header.php');
?>
<?php
	if(!empty($input)){
	echo('<form action="/supersearch.php" method="get">');
	echo('<input type="text" size="80" name="query" value="'.$input.'" />');
	echo('<input type="submit" value="Search" />');
	echo('</form>');
	echo('You searched for <strong>"'.$input.'"</strong>');
	
	} else {
		echo('<h1>For Speakers</h1>');
		echo('<form action="/supersearch.php" method="get">');
		echo('<label for="query">Search Opportunities</label><br />');
		echo('<input type="text" size="100" name="query" /><br />');
		echo('<input type="submit" value="Search" />');
		echo('</form><br />');
	}
	
	if($logged_in){
		echo('<section id="leftcol-results" class="opp-results">');
		echo('<header><h1 style="text-align:left;background:#EEEEFF;font-size:small;">&nbsp;Like You</h1></header>');
		foreach($likeme as $opp) {
			$oppid = $opp['id'];
			
			echo('<section class="result"><header>');
			echo('<h1>'.$opp['title'].'</h1>');
			echo('<h2><a href="/'.$opp['username'].'">'.$opp['creator'].'</a>, '.$opp['location'].'</h2>');
			echo('</header>');
			echo('<div class="resdetails">');
			
			if(!empty($opp['descrip'])){
				echo('<section class="searchabst">');
				echo($opp['descrip']);
				echo('</section>');
			}
			
			$opptagqry = "SELECT tags.id, tags.tag FROM tags, opportunitytags WHERE opportunitytags.opportunity = '$oppid' && tags.id = opportunitytags.tag";
			$opptags = mysql_query($opptagqry);
			echo('<span class="tags">');
			while($opptag = mysql_fetch_array($opptags)) {
				echo('<a href="/search/'.str_replace(" ","+",$opptag['tag']).'">'.$opptag['tag'].'</a> ');
			}
			echo('</span>');
			@mysql_free_result($opptags);
			
			echo('</div>');
			echo('</section>');
		}
		echo('</section>');

	
		if(isset($lat) && isset($lon)){
			echo('<section id="rightcol-results" class="opp-results">');
			echo('<header><h1 style="text-align:left;background:#EEEEFF;font-size:small;">&nbsp;Nearby</h1></header>');
			foreach($closest as $opp) {
				$oppid = $opp['id'];
				
				echo('<section class="result"><header>');
				echo('<h1>'.$opp['title'].'</h1>');
				echo('<h2><a href="/'.$opp['username'].'">'.$opp['creator'].'</a>, '.$opp['location'].'</h2>');
				echo('</header>');
				echo('<div class="resdetails">');
				
				if(!empty($opp['descrip'])){
					echo('<section class="searchabst">');
					echo($opp['descrip']);
					echo('</section>');
				}
				
				$opptagqry = "SELECT tags.id, tags.tag FROM tags, opportunitytags WHERE opportunitytags.opportunity = '$oppid' && tags.id = opportunitytags.tag";
				$opptags = mysql_query($opptagqry);
				echo('<span class="tags">');
				while($opptag = mysql_fetch_array($opptags)) {
					echo('<a href="/search/'.str_replace(" ","+",$opptag['tag']).'">'.$opptag['tag'].'</a> ');
				}
				echo('</span>');
				@mysql_free_result($opptags);
				
				echo('</div>');
				echo('</section>');
			}
			echo('</section>');
		} else {
			echo('<section id="rightcol-results" class="opp-results" style="background:#FFEBEE;">');
			echo('<header><h1 style="text-align:left;background:darkred;color:white;font-size:small;">&nbsp;Nearby</h1></header>');
			echo('<p>We don\'t have your ZIP code.<br /><a href="updateprofile.php">Add it</a> so we can show you nearby results!</p>');
			echo('</section>');
			
		}
	}
	
	echo('<section id="leftcol-results" class="opp-results">');
	echo('<header><h1 style="text-align:left;background:#EEEEFF;">&nbsp;Closing Soon</h1></header>');
	foreach($ending as $opp) {
		$oppid = $opp['id'];
		
		echo('<section class="result"><header>');
		echo('<h1>'.$opp['title'].'</h1>');
		echo('<h2><a href="/'.$opp['username'].'">'.$opp['creator'].'</a>, '.$opp['location'].'</h2>');
		echo('</header>');
		echo('<div class="resdetails">');
		
		if(!empty($opp['descrip'])){
			echo('<section class="searchabst">');
			echo($opp['descrip']);
			echo('</section>');
		}
		
		$opptagqry = "SELECT tags.id, tags.tag FROM tags, opportunitytags WHERE opportunitytags.opportunity = '$oppid' && tags.id = opportunitytags.tag";
		$opptags = mysql_query($opptagqry);
		echo('<span class="tags">');
		while($opptag = mysql_fetch_array($opptags)) {
			echo('<a href="/search/'.str_replace(" ","+",$opptag['tag']).'">'.$opptag['tag'].'</a> ');
		}
		echo('</span>');
		@mysql_free_result($opptags);
		
		echo('</div>');
		echo('</section>');
	}
	echo('</section>');
	
	echo('<section id="rightcol-results" class="opp-results">');
	echo('<header><h1 style="text-align:left;background:#EEEEFF;">&nbsp;Recently Opened/Added</h1></header>');
	foreach($recent as $opp) {
		$oppid = $opp['id'];
		
		echo('<section class="result"><header>');
		echo('<h1>'.$opp['title'].'</h1>');
		echo('<h2><a href="/'.$opp['username'].'">'.$opp['creator'].'</a>, '.$opp['location'].'</h2>');
		echo('</header>');
		echo('<div class="resdetails">');
		
		if(!empty($opp['descrip'])){
			echo('<section class="searchabst">');
			echo($opp['descrip']);
			echo('</section>');
		}
		
		$opptagqry = "SELECT tags.id, tags.tag FROM tags, opportunitytags WHERE opportunitytags.opportunity = '$oppid' && tags.id = opportunitytags.tag";
		$opptags = mysql_query($opptagqry);
		echo('<span class="tags">');
		while($opptag = mysql_fetch_array($opptags)) {
			echo('<a href="/search/'.str_replace(" ","+",$opptag['tag']).'">'.$opptag['tag'].'</a> ');
		}
		echo('</span>');
		@mysql_free_result($opptags);
		
		echo('</div>');
		echo('</section>');
	}
	echo('</section>');
?>
<div style="clear:both;"></div>
<script type="text/javascript">
    $(document).ready(function() {
 
        $(".searchabst").shorten({
    		"showChars" : 250
		});
 
    });
</script>
<?php
	include('footer.php');
?>
