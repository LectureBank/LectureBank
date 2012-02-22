<?php
	session_start();
	
	/* Connect to the database */
	require_once('config/database-connect.php');
	require_once('includes/wordhelper.php');
	
	$username = clean($_GET["user"]);
	$today = date("Y-m-d");
	
	if(!empty($username)) {
		$qry = "SELECT id_user, email FROM users WHERE username='$username' LIMIT 1";
		$result = mysql_query($qry);
		$row = mysql_fetch_array($result);
		if (!empty($row)) {
   			$uid  = $row['id_user'];
			$email = $row['email'];
		} else {
			header("Location: index.php");
		}
	} else {
		$uid = $_SESSION['uid'];
		$email = $_SESSION['email'];
		$logged_in = !!(isset($_SESSION['logged_in']) && $_SESSION['logged_in']);
		$protected = true;
	}
	@mysql_free_result($result);
	
if(($protected && $logged_in) || !$protected) {

	/* Get all user information */
	$qry = "SELECT name, username, zip FROM users WHERE id_user=$uid LIMIT 1";
    $result = mysql_query($qry);
	$row = mysql_fetch_array($result);
   	$name  = $row['name'];
	$username  = $row['username'];
	$zip = $row['zip'];
	@mysql_free_result($result);
	$qry = "SELECT institutions.name FROM institutions, userinstitutions WHERE userinstitutions.uid = $uid && institutions.id = userinstitutions.instid";
	$result = mysql_query($qry);
	$row = mysql_fetch_array($result);
   	$inst  = $row['name'];
	@mysql_free_result($result);
	$qry = "SELECT fields.name FROM fields, userfields WHERE userfields.uid = $uid && fields.code = userfields.fcode";
	$result = mysql_query($qry);
	$row = mysql_fetch_array($result);
   	$field  = $row['name'];
	@mysql_free_result($result);
	
	$qry = "SELECT tags.id, tags.tag FROM tags, userinterests WHERE userinterests.uid = $uid && tags.id = userinterests.intid";
	$result = mysql_query($qry);
	while($row = mysql_fetch_array($result)) {
		$interests[] = $row['tag'];
	}
	@mysql_free_result($result);
	
	$qry = "SELECT lectures.id, lectures.title, lectures.start, lectures.eventname, lectures.abstract, lectures.link, institutions.name FROM lectures, institutions WHERE lectures.creator = $uid AND institutions.id = lectures.loc_id AND lectures.start < '$today'";
	$result = mysql_query($qry);
	while($row = mysql_fetch_array($result)) {
		$prevlist[] = array(id => $row['id'], title => $row['title'], humandate => date('m/d/Y',strtotime($row['start'])), machinedate => date('Y-m-d',strtotime($row['start'])), microtime => date('c',strtotime($row['start'])), name => $row['eventname'], abst => $row['abstract'], link => $row['link'], inst => $row['name']);
	}
	@mysql_free_result($result);
	
	$qry = "SELECT lectures.id, lectures.title, lectures.start, lectures.eventname, lectures.abstract, lectures.link, institutions.name FROM lectures, institutions WHERE lectures.creator = $uid AND institutions.id = lectures.loc_id AND lectures.start >= '$today'";
	$result = mysql_query($qry);
	while($row = mysql_fetch_array($result)) {
		$schedlist[] = array(id => $row['id'], title => $row['title'], humandate => date('m/d/Y',strtotime($row['start'])), machinedate => date('Y-m-d',strtotime($row['start'])), microtime => date('c',strtotime($row['start'])), name => $row['eventname'], abst => $row['abstract'], link => $row['link'], inst => $row['name']);
	}
	@mysql_free_result($result);
	
	$qry = "SELECT id, title, link, yr, abstract FROM research WHERE uid = $uid ORDER BY yr DESC";
	$result = mysql_query($qry);
	while($row = mysql_fetch_array($result)) {
		$reslist[] = array(id => $row['id'], title => $row['title'], link => $row['link'], year => $row['yr'], abst => $row['abstract']);
	}
	@mysql_free_result($result);
	
}
	
	if (!$protected) {
		if ($name) {
			//Check name ends with "s"
			$reverse = strrev( $name );
			if($reverse{0} === "s") {
				$title = $name."' Profile";
			} else {
				$title = $name."'s Profile";
			}
		} else {
			//Check username ends with "s"
			$reverse = strrev( $username );
			if($reverse{0} === "s") {
				$title = $username."' Profile";
			} else {
				$title = $username."'s Profile";
			}
		}
	} else {
		$title = "My Profile";
	}
	$author = $name;
	$metakeywords = implode(",", $interests);
	$profile = true;
    include('header.php');
?>
<section id="profileBasicBar">
<?php echo ($protected) ? '<a title="Edit Profile" href="updateprofile.php"><img id="profileBasicEdit" alt="wrench" src="images/wrench.png"></a>' : ""; ?>
<header itemscope itemtype="http://schema.org/Person">
<hgroup>
<h2><?php echo (!empty($name)) ? '<span itemprop="name">'.$name.'</span>' : "No name entered"; ?></h2>
<h3><?php echo (!empty($inst)) ? '<span itemprop="affiliation" itemscope itemtype="http://schema.org/Organization"><span itemprop="name">'.$inst.'</span></span>' : "No institution entered"; ?></h3>
<h4><?php echo (!empty($field)) ? $field : "No field entered"; ?></h4>
</hgroup>
<?php echo('<a href="mailto:'.$email.'" itemprop="email">'.$email.'</a>'); ?>, <?php echo (!empty($zip)) ? '<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><span itemprop="postalCode">'.$zip.'</span></span>' : "No ZIP entered"; ?>
</header>
<br />
<span class="tags" style="font-size:small;">
<?php
	foreach ($interests as $interest) {
		echo('<a href="/search/'.str_replace(" ","+",$interest).'" rel="tag">'.$interest.'</a> ');
	}
?>
</span>
<div class="clear" style="height:10px;">
</div>
<div class="g-plusone" data-size="medium" data-annotation="inline" data-width="180" data-href="http://www.lecturebank.org/<?php echo($username) ?>"></div>
</section>
<section>
<header>
<h3>Scheduled Talks</h3>
<?php echo ($protected) ? '<div class="profileTool"><a href="dolecture.php"><img alt="Add an item" src="images/plus.png"></a></div>' : ""; ?>
</header>
<?php
	if (!empty($schedlist)) {
		foreach($schedlist as $scheditem) {
			echo ('<article itemscope itemtype="http://schema.org/EducationEvent">');
			echo ("<header>");
			echo ('<a itemprop="url" href="/'.$username.'/talks/'.cleanSlug($scheditem['title']).'"><span itemprop="name">'.$scheditem['title'].'</span></a>');
			echo (' <div class="g-plusone" data-size="small" data-annotation="none" data-href="http://www.lecturebank.org/'.$username.'/talks/'.cleanSlug($scheditem['title']).'"></div>');
			if(!empty($scheditem['link'])) echo (' <a itemprop="url" href="'.$scheditem['link'].'" target="_blank"><img alt="External link" src="/images/external-link-icon.gif"></a>');
			echo ("</header>");
			echo ($protected) ? '<div class="profileTool"><a href="/dolecture.php?edit='.$scheditem['id'].'"><img alt="Edit this item" src="images/wrench.png"></a> <a href="/dolecture.php?delete='.$scheditem['id'].'"><img alt="Delete this item" src="images/minus.png"></a></div>' : "";
			echo ("<p class=\"profileSub\">");
			echo (!empty($scheditem['name'])) ? '<span itemprop="superEvent" itemscope itemtype="http://schema.org/EducationEvent"><span itemprop="name">'.$scheditem['name'].'</span></span>' : "";
			echo ((!empty($scheditem['name']) && !empty($scheditem['inst'])) || (!empty($scheditem['name']) && !empty($scheditem['humandate']))) ? ", " : "";
			echo (!empty($scheditem['inst'])) ? '<span itemprop="location" itemscope itemtype="http://schema.org/Place"><span itemprop="name">'.$scheditem['inst'].'</span></span>' : "";
			echo (!empty($scheditem['inst']) && !empty($scheditem['humandate'])) ? ", " : "";
			echo (!empty($scheditem['humandate'])) ? '<time itemprop="startDate" content="'.$scheditem['microtime'].'" datetime="'.$scheditem['machinedate'].'">'.$scheditem['humandate'].'</time>' : "";
			echo ("</p>");
			echo (!empty($scheditem['abst'])) ? '<p itemprop="description" class="profileAbst">'.$scheditem['abst'].'</p>' : "";
			echo ("</article>");
		}
	} else {
		echo ($protected) ? 'None yet! Why not <a href="dolecture.php">add one?</a>' : '<span style="color:#CCC;">None</span>';
	}
?>
</section>
<hr />
<section>
<header>
<h3>Previous Engagements</h3>
<?php echo ($protected) ? '<div class="profileTool"><a href="dolecture.php"><img alt="Add an item"  src="images/plus.png"></a></div>' : ""; ?>
</header>
<?php
	if (!empty($prevlist)) {
		foreach($prevlist as $previtem) {
			echo ('<article itemscope itemtype="http://schema.org/EducationEvent">');
			echo ("<header>");
			echo ('<a itemprop="url" href="/'.$username.'/talks/'.cleanSlug($previtem['title']).'"><span itemprop="name">'.$previtem['title'].'</span></a>');
			echo (' <div class="g-plusone" data-size="small" data-annotation="none" data-href="http://www.lecturebank.org/'.$username.'/talks/'.cleanSlug($previtem['title']).'"></div>');
			if(!empty($previtem['link'])) echo (' <a itemprop="url" href="'.$previtem['link'].'" target="_blank"><img alt="External link" src="/images/external-link-icon.gif"></a>');
			echo ("</header>");
			echo ($protected) ? '<div class="profileTool"><a href="/dolecture.php?edit='.$previtem['id'].'"><img alt="Edit this item" src="images/wrench.png"></a> <a href="/dolecture.php?delete='.$previtem['id'].'"><img alt="Delete this item" src="images/minus.png"></a></div>' : "";
			echo ("<p class=\"profileSub\">");
			echo (!empty($previtem['name'])) ? '<span itemprop="superEvent" itemscope itemtype="http://schema.org/EducationEvent"><span itemprop="name">'.$previtem['name'].'</span></span>' : "";
			echo ((!empty($previtem['name']) && !empty($previtem['inst'])) || (!empty($previtem['name']) && !empty($previtem['humandate']))) ? ", " : "";
			echo (!empty($previtem['inst'])) ? '<span itemprop="location" itemscope itemtype="http://schema.org/Place"><span itemprop="name">'.$previtem['inst'].'</span></span>' : "";
			echo (!empty($previtem['inst']) && !empty($previtem['humandate'])) ? ", " : "";
			echo (!empty($previtem['humandate'])) ? '<time itemprop="startDate" content="'.$previtem['microtime'].'" datetime="'.$previtem['machinedate'].'">'.$previtem['humandate'].'</time>' : "";
			echo ("</p>");
			echo (!empty($previtem['abst'])) ? '<p itemprop="description" class="profileAbst">'.$previtem['abst'].'</p>' : "";
			echo ("</article>");
		}
	} else {
		echo ($protected) ? 'None yet! Why not <a href="dolecture.php">add one?</a>' : '<span style="color:#CCC;">None</span>';
	}
?>
</section>
<hr />
<section>
<header>
<h3>Research/Publications</h3>
<?php echo ($protected) ? '<div class="profileTool"><a href="doresearch.php"><img alt="Add an item" src="images/plus.png"></a></div>' : ""; ?>
</header>
<?php
	if (!empty($reslist)) {
		foreach($reslist as $resitem) {
			echo ('<article itemscope itemtype="http://schema.org/ScholarlyArticle">');
			echo ("<header>");
			echo ('<a itemprop="url" href="/'.$username.'/research/'.cleanSlug($resitem['title']).'"><span itemprop="name">'.$resitem['title'].'</span></a>');
			echo (!empty($resitem['year'])) ? ' (<span itemprop="datePublished">'.$resitem['year'].'</span>)' : "";
			echo (' <div class="g-plusone" data-size="small" data-annotation="none" data-href="http://www.lecturebank.org/'.$username.'/research/'.cleanSlug($resitem['title']).'"></div>');
			if(!empty($resitem['link'])) echo (' <a itemprop="url" href="'.$resitem['link'].'" target="_blank"><img alt="External link" src="/images/external-link-icon.gif"></a>');
			echo ("</header>");
			echo ($protected) ? '<div class="profileTool"><a href="/doresearch.php?edit='.$resitem['id'].'"><img alt="Edit this item" src="images/wrench.png"></a> <a href="/doresearch.php?delete='.$resitem['id'].'"><img alt="Delete this item" src="images/minus.png"></a></div>' : "";
			echo (!empty($resitem['abst'])) ? '<p itemprop="description" id="abst'.$resitem['id'].'" class="profileAbst">'.$resitem['abst'].'</p>' : "";
			echo ("</article>");
		}
	} else {
		echo ($protected) ? 'None yet! Why not <a href="doresearch.php">add one?</a>' : '<span style="color:#CCC;">None</span>';
	}
?>
<script type="text/javascript">
    $(document).ready(function() {
 
        $(".profileAbst").shorten({
    		"showChars" : 500
		});
 
    });
</script>
<div class="clear">
</div>
</section>
<br />
<?php include('footer.php');?>