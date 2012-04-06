<?php
require_once('config/database-connect.php');
require_once('includes/wordhelper.php');

$input = clean($_GET["query"]);

if(!empty($input)){
	$instqry = "SELECT id, name, address, city, state, zip FROM institutions WHERE ((name LIKE '%%$input%%') OR (address LIKE '%%$input%%') OR (city LIKE '%%$input%%') OR (zip='$input'))";
	$instresult = mysql_query($instqry);
	while($row = mysql_fetch_array($instresult)) {
		if(!empty($row['address'])){
			$instlist[] = array(id => $row['id'], name => $row['name'], address => $row['address'].', '.$row['city'].', '.$row['state'].' '.$row['zip']);
		} else {
			$instlist[] = array(id => $row['id'], name => $row['name'], address => $row['city'].', '.$row['state'].' '.$row['zip']);
		}
	}
	@mysql_free_result($instresult);
	
	$peopleqry = "SELECT users.id_user AS id, users.name AS name, users.username AS username, institutions.name AS inst FROM users, institutions, userinterests, userinstitutions, tags WHERE ((users.name LIKE '%%$input%%') OR (users.username='$input') OR (users.email='$input') OR (tags.tag LIKE '%%$input%%' AND tags.id = userinterests.intid AND users.id_user = userinterests.uid)) AND userinstitutions.uid = users.id_user AND institutions.id = userinstitutions.instid GROUP BY username";
	$peopleresult = mysql_query($peopleqry);
	while($row = mysql_fetch_array($peopleresult)) {
		$peoplelist[] = array(id => $row['id'], username => $row['username'], name => $row['name'], inst => $row['inst']);
	}
	@mysql_free_result($peopleresult);
	
	$researchqry = "SELECT users.name AS author, users.username AS username, institutions.name AS authorinst, research.id AS id, research.title AS title, research.yr AS year, research.abstract AS abst, research.journal AS journal, research.volume AS volume, research.issue AS issue, research.startpg AS startpg, research.endpg AS endpg FROM research, users, researchtags, tags, userinstitutions, institutions WHERE ((research.title LIKE '%%$input%%') OR (research.abstract LIKE '%%$input%%') OR (research.journal LIKE '%%$input%%') OR (tags.tag LIKE '%%$input%%' AND tags.id = researchtags.tag AND researchtags.research = research.id)) AND users.id_user = research.uid AND userinstitutions.uid = users.id_user AND institutions.id = userinstitutions.instid GROUP BY research.id";
	$researchresult = mysql_query($researchqry);
	while($row = mysql_fetch_array($researchresult)) {
		$researchlist[] = array(id => $row['id'], title => $row['title'], author => $row['author'], username => $row['username'], authorinst => $row['authorinst'], year => $row['year'], journal => $row['journal'], volume => $row['volume'], startpg => $row['startpg'], endpg => $row['endpg'], abst => $row['abst']);
	}
	@mysql_free_result($researchresult);
	
	$lectureqry = "SELECT users.name AS speaker, users.username AS username, institutions.name AS inst, lectures.id AS id, lectures.title AS title, lectures.locdetail AS locdetail, lectures.abstract AS abst, lectures.start AS start FROM lectures, users, lecturetags, tags, institutions WHERE ((lectures.title LIKE '%%$input%%') OR (lectures.abstract LIKE '%%$input%%') OR (tags.tag LIKE '%%$input%%' AND tags.id = lecturetags.tag AND lecturetags.lecture = lectures.id)) AND users.id_user = lectures.creator AND lectures.loc_id = institutions.id GROUP BY lectures.id";
	$lectureresult = mysql_query($lectureqry);
	while($row = mysql_fetch_array($lectureresult)) {
		$lecturelist[] = array(id => $row['id'], title => $row['title'], speaker => $row['speaker'], username => $row['username'], inst => $row['inst'], date => date('m/d/Y',strtotime($row['start'])), locdetail => $row['locdetail'], abst => $row['abst']);
	}
	@mysql_free_result($lectureresult);
	
	$title = 'SuperSearch for "'.$input.'"';
	$search = true;
} else {
	$title = "SuperSearch";
}

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
			
			$instpeopleqry = "SELECT users.name AS name, users.username AS username, CASE WHEN userfields.type = 'PRIM' THEN fields.name ELSE userfields.fcode END AS field FROM users, userinstitutions, userfields, fields WHERE (userfields.type = 'PRIM' AND userinstitutions.instid = '$instid' AND users.id_user = userinstitutions.uid AND userfields.uid = users.id_user AND fields.code = userfields.fcode) OR (userfields.type = 'OTHER' AND userinstitutions.instid = '$instid' AND users.id_user = userinstitutions.uid AND userfields.uid = users.id_user) LIMIT 10";
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
			
			$persfieldqry = "SELECT CASE WHEN userfields.type = 'PRIM' THEN fields.name ELSE userfields.fcode END AS fname FROM fields, userfields WHERE (userfields.type = 'PRIM' AND userfields.uid = '$persid' && fields.code = userfields.fcode) OR (userfields.type = 'OTHER' AND userfields.uid = '$persid')";
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
		echo('<h3>LectureBank SuperSearch</h3>');
		echo('<form action="/supersearch.php" method="get">');
		echo('<input type="search" size="100" name="query" /><br />');
		echo('<input type="submit" value="Search" />');
		echo('</form><br />');
	}
?>
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
