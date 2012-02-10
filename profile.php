<?php
	session_start();
	
	/* Connect to the database */
	require_once('config/database-connect.php');
	
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
	$qry = "SELECT name, zip FROM users WHERE id_user=$uid LIMIT 1";
    $result = mysql_query($qry);
	$row = mysql_fetch_array($result);
   	$name  = $row['name'];
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
		$interests[] = '<a href="/search/'.str_replace(" ","+",$row['tag']).'" rel="tag">'.$row['tag'].'</a>';
	}
	@mysql_free_result($result);
	
	$qry = "SELECT lectures.id, lectures.title, lectures.start, lectures.eventname, lectures.abstract, lectures.link, institutions.name FROM lectures, institutions WHERE lectures.creator = $uid AND institutions.id = lectures.loc_id AND lectures.start < '$today'";
	$result = mysql_query($qry);
	while($row = mysql_fetch_array($result)) {
		$prevlist[] = array(id => $row['id'], title => $row['title'], humandate => date('m/d/Y',strtotime($row['start'])), machinedate => date('Y-m-d',strtotime($row['start'])), name => $row['eventname'], abst => $row['abstract'], link => $row['link'], inst => $row['name']);
	}
	@mysql_free_result($result);
	
	$qry = "SELECT lectures.id, lectures.title, lectures.start, lectures.eventname, lectures.abstract, lectures.link, institutions.name FROM lectures, institutions WHERE lectures.creator = $uid AND institutions.id = lectures.loc_id AND lectures.start >= '$today'";
	$result = mysql_query($qry);
	while($row = mysql_fetch_array($result)) {
		$schedlist[] = array(id => $row['id'], title => $row['title'], humandate => date('m/d/Y',strtotime($row['start'])), machinedate => date('Y-m-d',strtotime($row['start'])), name => $row['eventname'], abst => $row['abstract'], link => $row['link'], inst => $row['name']);
	}
	@mysql_free_result($result);
	
	$qry = "SELECT id, title, link, yr, abstract FROM research WHERE uid = $uid";
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
	$profile = true;
    include('header.php');
?>
<section id="profileBasicBar">
<?php echo ($protected) ? '<a title="Edit Profile" href="updateprofile.php"><img id="profileBasicEdit" alt="wrench" src="images/wrench.png"></a>' : ""; ?>
<header>
<hgroup>
<h2><?php echo (!empty($name)) ? $name : "No name entered"; ?></h2>
<h3><?php echo (!empty($inst)) ? $inst : "No institution entered"; ?></h3>
<h4><?php echo (!empty($field)) ? $field : "No field entered"; ?></h4>
</hgroup>
<?php echo($email); ?>, <?php echo (!empty($zip)) ? $zip : "No ZIP entered"; ?>
</header>
<br />
<?php
	$tagstring = implode(", ", $interests);
		if (!empty($tagstring)) {
			echo($tagstring);
		}
?>
<div class="clear">
</div>
</section>
<section>
<header>
<h3>Scheduled Talks</h3>
<?php echo ($protected) ? '<div class="profileTool"><a href="addlecture.php"><img src="images/plus.png"></a></div>' : ""; ?>
</header>
<?php
	if (!empty($schedlist)) {
		foreach($schedlist as $scheditem) {
			echo ("<article>");
			echo ("<header>");
			echo (!empty($scheditem['link'])) ? "<a href=\"".$scheditem['link']."\">".$scheditem['title']."</a>" : $scheditem['title'];
			echo ("</header>");
			echo ($protected) ? '<div class="profileTool"><!--<a href="#"><img src="images/wrench.png"></a>--> <a href="/deletelecture.php?item='.$scheditem['id'].'"><img src="images/minus.png"></a></div>' : "";
			echo ("<p class=\"profileSub\">");
			echo (!empty($scheditem['name'])) ? $scheditem['name'] : "";
			echo ((!empty($scheditem['name']) && !empty($scheditem['inst'])) || (!empty($scheditem['name']) && !empty($scheditem['humandate']))) ? ", " : "";
			echo (!empty($scheditem['inst'])) ? $scheditem['inst'] : "";
			echo (!empty($scheditem['inst']) && !empty($scheditem['humandate'])) ? ", " : "";
			echo (!empty($scheditem['humandate'])) ? '<time datetime="'.$scheditem['machinedate'].'">'.$scheditem['humandate'].'</time>' : "";
			echo ("</p>");
			echo (!empty($scheditem['abst'])) ? "<p class=\"profileAbst\">".$scheditem['abst']."</p>" : "";
			echo ("</article>");
		}
	} else {
		echo ($protected) ? 'None yet! Why not <a href="addlecture.php">add one?</a>' : '<span style="color:#CCC;">None</span>';
	}
?>
</section>
<hr />
<section>
<header>
<h3>Previous Engagements</h3>
<?php echo ($protected) ? '<div class="profileTool"><a href="addlecture.php"><img src="images/plus.png"></a></div>' : ""; ?>
</header>
<?php
	if (!empty($prevlist)) {
		foreach($prevlist as $previtem) {
			echo ("<article>");
			echo ("<header>");
			echo (!empty($previtem['link'])) ? "<a href=\"".$previtem['link']."\">".$previtem['title']."</a>" : $previtem['title'];
			echo ("</header>");
			echo ($protected) ? '<div class="profileTool"><!--<a href="#"><img src="images/wrench.png"></a>--> <a href="/deletelecture.php?item='.$previtem['id'].'"><img src="images/minus.png"></a></div>' : "";
			echo ("<p class=\"profileSub\">");
			echo (!empty($previtem['name'])) ? $previtem['name'] : "";
			echo ((!empty($previtem['name']) && !empty($previtem['inst'])) || (!empty($previtem['name']) && !empty($previtem['humandate']))) ? ", " : "";
			echo (!empty($previtem['inst'])) ? $previtem['inst'] : "";
			echo (!empty($previtem['inst']) && !empty($previtem['humandate'])) ? ", " : "";
			echo (!empty($previtem['humandate'])) ? '<time datetime="'.$previtem['machinedate'].'">'.$previtem['humandate'].'</time>' : "";
			echo ("</p>");
			echo (!empty($previtem['abst'])) ? "<p class=\"profileAbst\">".$previtem['abst']."</p>" : "";
			echo ("</article>");
		}
	} else {
		echo ($protected) ? 'None yet! Why not <a href="addlecture.php">add one?</a>' : '<span style="color:#CCC;">None</span>';
	}
?>
</section>
<hr />
<section>
<header>
<h3>Research/Publications</h3>
<?php echo ($protected) ? '<div class="profileTool"><a href="addresearch.php"><img src="images/plus.png"></a></div>' : ""; ?>
</header>
<?php
	if (!empty($reslist)) {
		foreach($reslist as $resitem) {
			echo ("<article>");
			echo ("<header>");
			echo (!empty($resitem['link'])) ? "<a href=\"".$resitem['link']."\">".$resitem['title']."</a>" : $resitem['title'];
			echo (!empty($resitem['year'])) ? " (".$resitem['year'].")" : "";
			echo ("</header>");
			echo ($protected) ? '<div class="profileTool"><!--<a href="#"><img src="images/wrench.png"></a>--> <a href="/deleteresearch.php?item='.$resitem['id'].'"><img src="images/minus.png"></a></div>' : "";
			echo (!empty($resitem['abst'])) ? '<p id="abst'.$resitem['id'].'" class="profileAbst">'.$resitem['abst'].' <a href="javascript:void()" onclick="toggle_more(\'abst'.$resitem['id'].'\',this);" style="font-size:x-small">less</a></p>' : "";
			echo ("</article>");
		}
	} else {
		echo ($protected) ? 'None yet! Why not <a href="addresearch.php">add one?</a>' : '<span style="color:#CCC;">None</span>';
	}
?>
<div class="clear">
</div>
</section>
<br />
<?php include('footer.php');?>