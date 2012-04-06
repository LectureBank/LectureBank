<?php
session_start();

require_once('config/database-connect.php');
require_once('includes/wordhelper.php');

$input = clean($_GET["query"]);

$uid = $_SESSION['uid'];
$email = $_SESSION['email'];
$logged_in = !!(isset($_SESSION['logged_in']) && $_SESSION['logged_in']);

if($logged_in) {
	//get all of the user's opportunities
	$myoppqry = "SELECT opportunities.*, institutions.name AS location FROM opportunities, institutions WHERE opportunities.creator = '$uid' AND institutions.id = opportunities.loc_id";
	$myoppresult = mysql_query($myoppqry);
		while($myopprow = mysql_fetch_array($myoppresult)) {
			$myopps[] = array(id => $myopprow['id'], location => $myopprow['location'], locdetail => $myopprow['locdetail'], title => $myopprow['title'], descrip => $myopprow['descrip'], link => $myopprow['link']);
		}
	@mysql_free_result($myoppresult);
}

$activeqry = "SELECT COUNT(*) AS count, useractivity.uid AS uid, users.name, users.username, institutions.name AS inst FROM (SELECT uid AS uid FROM research UNION ALL SELECT creator AS uid FROM lectures) AS useractivity, users, userinstitutions, institutions WHERE useractivity.uid = users.id_user AND id_user != '$uid' AND users.status = 'activated' AND userinstitutions.uid = users.id_user AND institutions.id = userinstitutions.instid GROUP BY uid ORDER BY count DESC LIMIT 5";
$activeresult = mysql_query($activeqry);
	while($activerow = mysql_fetch_array($activeresult)) {
		$actives[] = array(id => $activerow['uid'], name => $activerow['name'], username => $activerow['username'], inst => $activerow['inst']);
	}
@mysql_free_result($activeresult);

$recentqry = "SELECT users.id_user, users.username, users.name, institutions.name AS inst, users.updated FROM (SELECT * FROM (SELECT id_user, updated FROM users WHERE status = 'activated' UNION ALL SELECT creator AS id_user, updated FROM lectures UNION ALL SELECT uid AS id_user, updated FROM research) AS unioned ORDER BY updated DESC) AS ordered NATURAL JOIN users, userinstitutions, institutions WHERE id_user != '$uid' AND userinstitutions.uid = users.id_user AND institutions.id = userinstitutions.instid GROUP BY id_user ORDER BY updated DESC LIMIT 5";
$recentresult = mysql_query($recentqry);
	while($recentrow = mysql_fetch_array($recentresult)) {
		$recents[] = array(id => $recentrow['id_user'], name => $recentrow['name'], username => $recentrow['username'], inst => $recentrow['inst']);
	}
@mysql_free_result($recentresult);


$title = "For Planners";
$profile = true;
include('header.php');
?>
<?php
	if(!empty($input)){
	echo('<form action="/supersearch.php" method="get">');
	echo('<input type="search" size="80" name="query" value="'.$input.'" />');
	echo('<input type="submit" value="Search" />');
	echo('</form>');
	echo('You searched for <strong>"'.$input.'"</strong>');
	if (!empty($instlist)){
		echo('<section id="institution-results">');
		echo('<header><h1 style="text-align:left;background:#EEEEFF;">Institutions</h1></header>');
		foreach($instlist as $inst) {
			echo('<section class="result"><header>');
			echo('<h1 style="text-align:left">'.$inst['name'].'</h1>');
			echo('<h2>'.$inst['address'].'</h2>');
			echo('</header>');
			echo('<div class="resdetails">');
			$instid = $inst['id'];
			
			$instpeopleqry = "SELECT users.name AS name, users.username AS username, fields.name AS field FROM users, userinstitutions, userfields, fields WHERE userinstitutions.instid = '$instid' AND users.id_user = userinstitutions.uid AND userfields.uid = users.id_user AND fields.code = userfields.fcode LIMIT 10";
			$instpeople = mysql_query($instpeopleqry);
			$numpeople = mysql_num_rows($instpeople);
			if($numpeople > 0){
				echo('<section>');
				echo('<h4>People</h4>');
				echo('<ul class="person">');
				while($person = mysql_fetch_array($instpeople)) {
					echo('<li><a href="/'.$person['username'].'">'.$person['name'].'</a> - '.$person['field'].'</li>');
				}
				echo('</ul>');
				echo('</section>');
			}
			@mysql_free_result($instpeople);
			
			$instlectureqry = "SELECT title, locdetail, start FROM lectures WHERE loc_id = '$instid' ORDER BY start DESC LIMIT 10";
			$instlectures = mysql_query($instlectureqry);
			$numlectures = mysql_num_rows($instlectures);
			if($numlectures > 0){
				echo('<section>');
				echo('<h4>Lectures</h4>');
				echo('<ul class="lecture">');
				while($lecture = mysql_fetch_array($instlectures)) {
					if(!empty($lecture['locdetail'])){
						echo('<li>'.$lecture['title'].', '.$lecture['locdetail'].', '.date('m/d/Y',strtotime($lecture['start'])).'</li>');
					} else {
						echo('<li>'.$lecture['title'].', '.date('m/d/Y',strtotime($lecture['start'])).'</li>');
					}
				}
				echo('</ul>');
				echo('</section>');
			}
			@mysql_free_result($instlectures);
			echo('</div>');
			echo('</section>');
		}
		echo('</section>');
		
	}
	
	if (!empty($peoplelist)){
		echo('<section id="people-results">');
		echo('<header><h1 style="text-align:left;background:#EEEEFF;">People</h1></header>');
		foreach($peoplelist as $person) {
			$persid = $person['id'];
			
			$persfieldqry = "SELECT fields.name AS fname FROM fields, userfields WHERE userfields.uid = '$persid' && fields.code = userfields.fcode";
			$persfield = mysql_query($persfieldqry);
			$pfield = mysql_fetch_row($persfield);
			@mysql_free_result($persfield);
			
			echo('<section class="result"><header>');
			echo('<h1><a href="/'.$person['username'].'">'.$person['name'].'</a></h1>');
			if(!empty($pfield)){
				echo('<h2>'.$pfield[0].', '.$person['inst'].'</h2>');
			} else {
				echo('<h2>'.$person['inst'].'</h2>');
			}
			
			$userintqry = "SELECT tags.id, tags.tag FROM tags, userinterests WHERE userinterests.uid = '$persid' && tags.id = userinterests.intid";
			$interests = mysql_query($userintqry);
			echo('<span class="tags">');
			while($interest = mysql_fetch_array($interests)) {
				echo('<a href="/search/'.str_replace(" ","+",$interest['tag']).'">'.$interest['tag'].'</a> ');
			}
			echo('</span>');
			@mysql_free_result($interests);
			echo('</header>');
			echo('<div class="resdetails">');
			
			$persresearchqry = "SELECT title, yr FROM research WHERE uid = '$persid' ORDER BY yr DESC LIMIT 10";
			$perspapers = mysql_query($persresearchqry);
			$numpapers = mysql_num_rows($perspapers);
			if($numpapers > 0){
				echo('<section>');
				echo('<h4>Research</h4>');
				echo('<ul class="research">');
				while($paper = mysql_fetch_array($perspapers)) {
					echo('<li>'.$paper['title'].' ('.$paper['yr'].')</li>');
				}
				echo('</ul>');
				echo('</section>');
			}
			@mysql_free_result($perspapers);
			
			$perslectureqry = "SELECT title, locdetail, start FROM lectures WHERE creator = '$persid' ORDER BY start DESC LIMIT 10";
			$perslectures = mysql_query($perslectureqry);
			$numlectures = mysql_num_rows($perslectures);
			if($numlectures > 0){
				echo('<section>');
				echo('<h4>Lectures</h4>');
				echo('<ul class="lecture">');
				while($lecture = mysql_fetch_array($perslectures)) {
					if(!empty($lecture['locdetail'])){
						echo('<li>'.$lecture['title'].', '.$lecture['locdetail'].', '.date('m/d/Y',strtotime($lecture['start'])).'</li>');
					} else {
						echo('<li>'.$lecture['title'].', '.date('m/d/Y',strtotime($lecture['start'])).'</li>');
					}
				}
				echo('</ul>');
				echo('</section>');
			}
			@mysql_free_result($perslectures);
			echo('</div>');
			echo('</section>');
		}
		echo('</section>');
		
	}
	
	if (!empty($researchlist)){
		echo('<section id="research-results">');
		echo('<header><h1 style="text-align:left;background:#EEEEFF;">Research</h1></header>');
		foreach($researchlist as $paper) {
			$paperid = $paper['id'];
			
			echo('<section class="result"><header>');
			echo('<h1><a href="/'.$paper['username'].'/research/'.cleanSlug($paper['title']).'">'.$paper['title'].'</a></h1>');
			echo('<h2><a href="/'.$paper['username'].'">'.$paper['author'].'</a>, '.$paper['authorinst'].' ('.$paper['year'].')</h2>');
			if(!empty($paper['journal'])){
				echo('<h2>'.$paper['journal']);
				if(!empty($paper['volume'])) echo(', vol. '.$paper['volume']);
				if(!empty($paper['issue'])) echo(', iss. '.$paper['issue']);
				if(!empty($paper['startpg'])) echo(', '.$paper['startpg']);
				if(!empty($paper['endpg'])) echo('-'.$paper['endpg']);
				echo('</h2>');
			}
			echo('</header>');
			echo('<div class="resdetails">');
			
			if(!empty($paper['abst'])){
				echo('<section class="searchabst">');
				echo($paper['abst']);
				echo('</section>');
			}
			
			$papertagqry = "SELECT tags.id, tags.tag FROM tags, researchtags WHERE researchtags.research = '$paperid' && tags.id = researchtags.tag";
			$papertags = mysql_query($papertagqry);
			echo('<span class="tags">');
			while($papertag = mysql_fetch_array($papertags)) {
				echo('<a href="/search/'.str_replace(" ","+",$papertag['tag']).'">'.$papertag['tag'].'</a> ');
			}
			echo('</span>');
			@mysql_free_result($papertags);
			
			echo('</div>');
			echo('</section>');
		}
		echo('</section>');
		
	}
	
	if (!empty($lecturelist)){
		echo('<section id="talk-results">');
		echo('<header><h1 style="text-align:left;background:#EEEEFF;">Talks</h1></header>');
		foreach($lecturelist as $talk) {
			$talkid = $talk['id'];
			
			echo('<section class="result"><header>');
			echo('<h1><a href="/'.$talk['username'].'/talks/'.cleanSlug($talk['title']).'">'.$talk['title'].'</a></h1>');
			echo('<h2><a href="/'.$talk['username'].'">'.$talk['speaker'].'</a>, ');
			if(!empty($talk['locdetail'])) echo($talk['locdetail'].', ');
			echo($talk['inst'].', '.$talk['date'].'</h2>');
			echo('</header>');
			echo('<div class="resdetails">');
			
			if(!empty($talk['abst'])){
				echo('<section class="searchabst">');
				echo($talk['abst']);
				echo('</section>');
			}
			
			$talktagqry = "SELECT tags.id, tags.tag FROM tags, lecturetags WHERE lecturetags.lecture = '$talkid' && tags.id = lecturetags.tag";
			$talktags = mysql_query($talktagqry);
			echo('<span class="tags">');
			while($talktag = mysql_fetch_array($talktags)) {
				echo('<a href="/search/'.str_replace(" ","+",$talktag['tag']).'">'.$talktag['tag'].'</a> ');
			}
			echo('</span>');
			@mysql_free_result($papertags);
			
			echo('</div>');
			echo('</section>');
		}
		echo('</section>');
		
	}
	echo('<br />');
	} else {
		echo('<h1>For Planners</h1>');
		echo('<section id="leftcol-results">');
		echo('<form action="/supersearch.php" method="get" style="margin:auto;padding-top:1.25em;">');
		echo('<label for="query">Search Speakers</label><br />');
		echo('<input type="search" size="70" name="query" />');
		echo('<input type="submit" value="Search" />');
		echo('</form>');
		echo('</section>');
		echo('<section id="rightcol-results">');
		echo('<div id="bigbutton" style="margin:auto;"><a href="/doopportunity.php">+ Add An Opportunity</a></div>');
		echo('</section>');
		echo('<div style="clear:both;">');
		echo('</div>');
		echo('<br />');
		if($logged_in){
			if(!empty($myopps)){
				echo('<section id="rightcol-results" class="opp-results">');
				echo('<header><h1 style="text-align:left;background:#EEEEFF;font-size:small;">&nbsp;Your Posted Opportunities</h1></header>');
				foreach($myopps as $opp) {
					$oppid = $opp['id'];
					
					echo('<section class="result"><header>');
					echo('<h1>'.$opp['title'].'</h1>');
					echo('<h2>'.$opp['locdetail'].', '.$opp['location'].'</h2>');
					echo ($logged_in) ? '<div class="profileTool"><a href="/doopportunity.php?edit='.$opp['id'].'"><img alt="Edit this item" src="images/wrench.png"></a> <a href="/doopportunity.php?delete='.$opp['id'].'"><img alt="Delete this item" src="images/minus.png"></a></div>' : "";
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
			} else {
				echo('<section id="rightcol-results" class="opp-results" style="background:#FFEBEE;">');
				echo('<header><h1 style="text-align:left;background:darkred;color:white;font-size:small;">&nbsp;Your Posted Opportunities</h1></header>');
				echo('<p>None yet! Why not <a href="doopportunity.php">add one?</a></p>');
			}
			echo('</section>');
		}
		
		echo('<section id="leftcol-results" class="opp-results">');
		echo('<header><h1 style="text-align:left;background:#EEEEFF;">&nbsp;Most Active Presenters</h1></header>');
		foreach($actives as $person) {
			$persid = $person['id'];
			
			$persfieldqry = "SELECT fields.name AS fname FROM fields, userfields WHERE userfields.uid = '$persid' && fields.code = userfields.fcode";
			$persfield = mysql_query($persfieldqry);
			$pfield = mysql_fetch_row($persfield);
			@mysql_free_result($persfield);
			
			echo('<section class="result"><header>');
			echo('<h1><a href="/'.$person['username'].'">'.$person['name'].'</a></h1>');
			if(!empty($pfield)){
				echo('<h2>'.$pfield[0].', '.$person['inst'].'</h2>');
			} else {
				echo('<h2>'.$person['inst'].'</h2>');
			}
			
			$userintqry = "SELECT tags.id, tags.tag FROM tags, userinterests WHERE userinterests.uid = '$persid' && tags.id = userinterests.intid";
			$interests = mysql_query($userintqry);
			echo('<span class="tags">');
			while($interest = mysql_fetch_array($interests)) {
				echo('<a href="/search/'.str_replace(" ","+",$interest['tag']).'">'.$interest['tag'].'</a> ');
			}
			echo('</span>');
			@mysql_free_result($interests);
			echo('</header>');
			echo('<div class="resdetails">');
			
			$persresearchqry = "SELECT title, yr FROM research WHERE uid = '$persid' ORDER BY yr DESC LIMIT 10";
			$perspapers = mysql_query($persresearchqry);
			$numpapers = mysql_num_rows($perspapers);
			if($numpapers > 0){
				echo('<section class="detail">');
				echo('<h4>Research</h4>');
				echo('<ul class="research">');
				while($paper = mysql_fetch_array($perspapers)) {
					echo('<li>'.$paper['title'].' ('.$paper['yr'].')</li>');
				}
				echo('</ul>');
				echo('</section>');
			}
			@mysql_free_result($perspapers);
			
			$perslectureqry = "SELECT title, locdetail, start FROM lectures WHERE creator = '$persid' ORDER BY start DESC LIMIT 10";
			$perslectures = mysql_query($perslectureqry);
			$numlectures = mysql_num_rows($perslectures);
			if($numlectures > 0){
				echo('<section class="detail">');
				echo('<h4>Lectures</h4>');
				echo('<ul class="lecture">');
				while($lecture = mysql_fetch_array($perslectures)) {
					if(!empty($lecture['locdetail'])){
						echo('<li>'.$lecture['title'].', '.$lecture['locdetail'].', '.date('m/d/Y',strtotime($lecture['start'])).'</li>');
					} else {
						echo('<li>'.$lecture['title'].', '.date('m/d/Y',strtotime($lecture['start'])).'</li>');
					}
				}
				echo('</ul>');
				echo('</section>');
			}
			@mysql_free_result($perslectures);
			echo('</div>');
			echo('</section>');
		}
		echo('</section>');
		
		echo('<section id="rightcol-results" class="opp-results">');
		echo('<header><h1 style="text-align:left;background:#EEEEFF;">&nbsp;Most Recently Active</h1></header>');
		foreach($recents as $person) {
			$persid = $person['id'];
			
			$persfieldqry = "SELECT fields.name AS fname FROM fields, userfields WHERE userfields.uid = '$persid' && fields.code = userfields.fcode";
			$persfield = mysql_query($persfieldqry);
			$pfield = mysql_fetch_row($persfield);
			@mysql_free_result($persfield);
			
			echo('<section class="result"><header>');
			echo('<h1><a href="/'.$person['username'].'">'.$person['name'].'</a></h1>');
			if(!empty($pfield)){
				echo('<h2>'.$pfield[0].', '.$person['inst'].'</h2>');
			} else {
				echo('<h2>'.$person['inst'].'</h2>');
			}
			
			$userintqry = "SELECT tags.id, tags.tag FROM tags, userinterests WHERE userinterests.uid = '$persid' && tags.id = userinterests.intid";
			$interests = mysql_query($userintqry);
			echo('<span class="tags">');
			while($interest = mysql_fetch_array($interests)) {
				echo('<a href="/search/'.str_replace(" ","+",$interest['tag']).'">'.$interest['tag'].'</a> ');
			}
			echo('</span>');
			@mysql_free_result($interests);
			echo('</header>');
			echo('<div class="resdetails">');
			
			$persresearchqry = "SELECT title, yr FROM research WHERE uid = '$persid' ORDER BY yr DESC LIMIT 10";
			$perspapers = mysql_query($persresearchqry);
			$numpapers = mysql_num_rows($perspapers);
			if($numpapers > 0){
				echo('<section class="detail">');
				echo('<h4>Research</h4>');
				echo('<ul class="research">');
				while($paper = mysql_fetch_array($perspapers)) {
					echo('<li>'.$paper['title'].' ('.$paper['yr'].')</li>');
				}
				echo('</ul>');
				echo('</section>');
			}
			@mysql_free_result($perspapers);
			
			$perslectureqry = "SELECT title, locdetail, start FROM lectures WHERE creator = '$persid' ORDER BY start DESC LIMIT 10";
			$perslectures = mysql_query($perslectureqry);
			$numlectures = mysql_num_rows($perslectures);
			if($numlectures > 0){
				echo('<section class="detail">');
				echo('<h4>Lectures</h4>');
				echo('<ul class="lecture">');
				while($lecture = mysql_fetch_array($perslectures)) {
					if(!empty($lecture['locdetail'])){
						echo('<li>'.$lecture['title'].', '.$lecture['locdetail'].', '.date('m/d/Y',strtotime($lecture['start'])).'</li>');
					} else {
						echo('<li>'.$lecture['title'].', '.date('m/d/Y',strtotime($lecture['start'])).'</li>');
					}
				}
				echo('</ul>');
				echo('</section>');
			}
			@mysql_free_result($perslectures);
			echo('</div>');
			echo('</section>');
		}
		echo('</section>');
		
		echo('<div style="clear:both;">');
		echo('</div>');
		echo('<br />');
	}
?>
<script type="text/javascript">
    $(document).ready(function() {
 
        $(".detail").shorten({
    		"showChars" : 17
		});
 
    });
</script>
<?php
	include('footer.php');
?>
