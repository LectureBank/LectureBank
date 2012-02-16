<?php
require_once('config/database-connect.php');
require_once('includes/wordhelper.php');

	$input = clean($_GET["query"]);

	$instqry = "SELECT id, name, address, city, state, zip FROM institutions WHERE ((name LIKE '%%$input%%') OR (address LIKE '%%$input%%') OR (city LIKE '%%$input%%') OR (zip='$input'))";
	$instresult = mysql_query($instqry);
	$numinstresult = mysql_num_rows($instresult);
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
	$numpeopleresult = mysql_num_rows($peopleresult);
	while($row = mysql_fetch_array($peopleresult)) {
		$peoplelist[] = array(id => $row['id'], username => $row['username'], name => $row['name'], inst => $row['inst']);
	}
	@mysql_free_result($peopleresult);
	
	$researchqry = "SELECT users.name AS author, users.username AS username, institutions.name AS authorinst, research.id AS id, research.title AS title, research.yr AS year, research.abstract AS abst, research.journal AS journal, research.volume AS volume, research.issue AS issue, research.startpg AS startpg, research.endpg AS endpg FROM research, users, researchtags, tags, userinstitutions, institutions WHERE ((research.title LIKE '%%$input%%') OR (research.abstract LIKE '%%$input%%') OR (research.journal LIKE '%%$input%%') OR (tags.tag LIKE '%%$input%%' AND tags.id = researchtags.tag AND researchtags.research = research.id)) AND users.id_user = research.uid AND userinstitutions.uid = users.id_user AND institutions.id = userinstitutions.instid GROUP BY research.id";
	$researchresult = mysql_query($researchqry);
	$numresearchresult = mysql_num_rows($researchresult);
	while($row = mysql_fetch_array($researchresult)) {
		$researchlist[] = array(id => $row['id'], title => $row['title'], author => $row['author'], username => $row['username'], authorinst => $row['authorinst'], year => $row['year'], journal => $row['journal'], volume => $row['volume'], startpg => $row['startpg'], endpg => $row['endpg'], abst => $row['abst']);
	}
	@mysql_free_result($researchresult);
	
	$lectureqry = "SELECT users.name AS speaker, users.username AS username, institutions.name AS inst, lectures.id AS id, lectures.title AS title, lectures.locdetail AS locdetail, lectures.abstract AS abst, lectures.start AS start FROM lectures, users, lecturetags, tags, institutions WHERE ((lectures.title LIKE '%%$input%%') OR (lectures.abstract LIKE '%%$input%%') OR (tags.tag LIKE '%%$input%%' AND tags.id = lecturetags.tag AND lecturetags.lecture = lectures.id)) AND users.id_user = lectures.creator AND lectures.loc_id = institutions.id GROUP BY lectures.id";
	$lectureresult = mysql_query($lectureqry);
	$numlectureresult = mysql_num_rows($lectureresult);
	while($row = mysql_fetch_array($lectureresult)) {
		$lecturelist[] = array(id => $row['id'], title => $row['title'], speaker => $row['speaker'], username => $row['username'], inst => $row['inst'], date => date('m/d/Y',strtotime($row['start'])), locdetail => $row['locdetail'], abst => $row['abst']);
	}
	@mysql_free_result($lectureresult);
	
	
$now = date("D, d M Y H:i:s T");

$output = '<?xml version="1.0" encoding="UTF-8"?>
 			<rss version="2.0" 
      			xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/"
      			xmlns:atom="http://www.w3.org/2005/Atom">
   			<channel>
                    <title>LectureBank.org Search: '.$input.'</title>
     				<link>http://www.lecturebank.org/search/'.str_replace(" ","+",$input).'</link>
                    <description>Search results for "'.$input.'" at LectureBank.org</description>
					<opensearch:totalResults>'.($numinstresult+$numpeopleresult+$numresearchresult+$numlectureresult).'</opensearch:totalResults>
					<atom:link rel="search" type="application/opensearchdescription+xml" href="http://www.lecturebank.org/opensearchdescription.xml"/>
					<opensearch:Query role="request" searchTerms="'.$input.'"/>';

		foreach($instlist as $inst) {
			$output .= '<item>';
			$output .= '<title>'.$inst['name'].'</title>';
			$output .= '<description>'.$inst['address'].'</description>';
			$output .= '</item>';
		}
	
		foreach($peoplelist as $person) {
			$persid = $person['id'];
			$persfieldqry = "SELECT fields.name AS fname FROM fields, userfields WHERE userfields.uid = '$persid' && fields.code = userfields.fcode";
			$persfield = mysql_query($persfieldqry);
			$pfield = mysql_fetch_row($persfield);
			@mysql_free_result($persfield);
			
			$output .= '<item>';
			$output .= '<title>'.$person['name'].'</title>';
			$output .= '<link>http://www.lecturebank.org/'.$person['username'].'</link>';
			$output .= '<description>';
			
			if(!empty($pfield)) $output .= 'Primary Field of Study: '.$pfield[0].', ';
			$output .= 'Home Institution: '.$person['inst'].', ';
			
			$userintqry = "SELECT tags.id, tags.tag FROM tags, userinterests WHERE userinterests.uid = '$persid' && tags.id = userinterests.intid";
			$interests = mysql_query($userintqry);
			$numinterests = mysql_num_rows($interests);
			if($numinterests > 0) {
				$output .= 'Tags: ';
				while($interest = mysql_fetch_array($interests)) {
					$intarr[] = $interest['tag'];
				}
				$output .= implode(',',$intarr);
			}
			@mysql_free_result($interests);
			
			$output .= '</description></item>';
		}
		
		foreach($researchlist as $paper) {
			$paperid = $paper['id'];
			
			$output .= '<item>';
			$output .= '<title>'.$paper['title'].'</title>';
			$output .= '<link>http://www.lecturebank.org/'.$paper['username'].'/research/'.cleanSlug($paper['title']).'</link>';
			$output .= '<description>'.$paper['author'].' - ';
			if(!empty($paper['journal'])) $output .= $paper['journal'].', ';
			$output .= $paper['year'].' ';
			if(!empty($paper['abst'])) $output .= 'Abstract: '.$paper['abst'].' ';
			
			$papertagqry = "SELECT tags.id, tags.tag FROM tags, researchtags WHERE researchtags.research = '$paperid' && tags.id = researchtags.tag";
			$papertags = mysql_query($papertagqry);
			$numpapertags = mysql_num_rows($papertags);
			if($numpapertags > 0){
				$output .= 'Keywords: ';
				while($papertag = mysql_fetch_array($papertags)) {
					$kwarr[] = $papertag['tag'];
				}
				$output .= implode(',',$kwarr);
			}
			@mysql_free_result($papertags);
			$output .= '</description></item>';
		}
		
		foreach($lecturelist as $talk) {
			$talkid = $talk['id'];
			
			$output .= '<item>';
			$output .= '<title>'.$talk['title'].'</title>';
			$output .= '<link>http://www.lecturebank.org/'.$talk['username'].'/talks/'.cleanSlug($talk['title']).'</link>';
			$output .= '<description>Speaker: '.$talk['speaker'].', Location: ';
			if(!empty($talk['locdetail'])) $output .= $talk['locdetail'].', ';
			$output .= $talk['inst'].', Date: '.$talk['date'].' ';
			if(!empty($talk['abst'])) $output .= 'Abstract: '.$talk['abst'].' ';
			
			$talktagqry = "SELECT tags.id, tags.tag FROM tags, lecturetags WHERE lecturetags.lecture = '$talkid' && tags.id = lecturetags.tag";
			$talktags = mysql_query($talktagqry);
			$numtalktags = mysql_num_rows($talktags);
			if($numtalktags > 0){
				$output .= 'Keywords: ';
				while($talktag = mysql_fetch_array($talktags)) {
					$ttarr[] = $talktag['tag'];
				}
				$output .= implode(',',$ttarr);
			}
			@mysql_free_result($papertags);
			$output .= '</description></item>';
		}
		$output .= '</channel></rss>';
		
		header("Content-Type: application/rss+xml");
		echo $output;
?>