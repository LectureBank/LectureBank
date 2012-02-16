<?php
	session_start();
	
	/* Connect to the database */
	require_once('config/database-connect.php');
	
	$email = $_SESSION["email"];
	$uid = $_SESSION["uid"];
	
	$toedit = clean($_GET["edit"]);
	$todelete = clean($_GET["delete"]);
	
	$title = "Add Research";
	if (!empty($todelete) && !empty($uid)) {
		$title = "Delete Research";
		$qry = "SELECT id, uid FROM research WHERE id='$todelete' AND uid='$uid'";
		$result = mysql_query($qry);
		$delcheck = mysql_fetch_array($result);
		if($result && (mysql_num_rows($result) > 0) && ($delcheck['uid'] == $uid)) {
			@mysql_free_result($result);
			$qry = "DELETE FROM research WHERE id='$todelete'";
			mysql_query($qry);
			header("Location: profile.php");
		} else {
			@mysql_free_result($result);
			header("Location: profile.php");
		}
	} elseif (!empty($toedit) && !empty($uid)) {
		$preqry = "SELECT * FROM research WHERE id=$toedit";
		$preqryresult = mysql_query($preqry);
		$checkprepaper = mysql_fetch_array($preqryresult);
		if($checkprepaper && ($checkprepaper['uid'] == $uid)) {
			$prepaper = $checkprepaper;
			$prepaperid = $prepaper['id'];
			
			$pretagqry = "SELECT tags.id, tags.tag FROM tags, researchtags WHERE researchtags.research = $toedit && tags.id = researchtags.tag";
			$pretagresults = mysql_query($pretagqry);
			$pretags = array();
			while ($row = mysql_fetch_array($pretagresults)) {
				$pretag = array();
				$pretag[id] = intval($row[id]);
				$pretag[name] = $row[tag];
				$pretags[] = $pretag;
			}
	
			if (!empty($pretags)) {
				$pretagjson = json_encode($pretags);
			}
			@mysql_free_result($pretagresults);
			$title = "Edit Research";
		} else {
			$errors = true;
			$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">You do not have permission to edit this record.</font>';
		}
		@mysql_free_result($checkprepaper);
		
	} elseif ($_POST['form_submitted'] && !empty($uid)) {
	
	//Sanitize the POST values
	$posttitle = clean($_POST["title"]);
	$year = clean($_POST["year"]);
	$journal = clean($_POST["journal"]);
	$volume = clean($_POST["volume"]);
	$issue = clean($_POST["issue"]);
	$startpg = clean($_POST["startpg"]);
	$endpg = clean($_POST["endpg"]);
	$doi = clean($_POST["doi"]);
	$collab = clean($_POST["collab"]);
	$link = clean($_POST["link"]);
	$abstract = clean($_POST["abstract"]);
	$taginput = clean($_POST["tags"]);
	$tagarray = explode(",",$taginput);
	
	if ($_POST['form_submitted'] == '2' && $_POST['record_to_update'] && !empty($uid)){
		$uprec = $_POST['record_to_update'];
		$permitqry = "SELECT uid FROM research WHERE id=$uprec";
		$permitresult = mysql_query($permitqry);
		$permitresult = mysql_fetch_array($permitresult);
		if($permitresult && ($permitresult['uid'] == $uid)) {
			$upok = true;
			$title = "Edit Research";
			$prepaperid = "$uprec";
		} else {
			$errors = true;
			$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">You do not have permission to edit this record.</font>';
			break 3;
		}
		@mysql_free_result($permitresult);
	}
	
	if(empty($posttitle)){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">You must enter a title.</font>';
	}
	if(!empty($year) && (!is_numeric($year) || strlen($year) != 4)){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Year must be a number entered in the format YYYY.</font>';
	}
	if(!empty($volume) && !is_numeric($volume)){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Volume must be a number.</font>';
	}
	if(!empty($volume) && strlen($volume) > 5){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Volume number must be five digits or less.</font>';
	}
	if(!empty($issue) && !is_numeric($issue)){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Issue must be a number.</font>';
	}
		if(!empty($issue) && strlen($issue) > 5){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Issue number must be five digits or less.</font>';
	}
	if(!empty($startpg) && !is_numeric($startpg)){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Start Page must be a number.</font>';
	}
	if(!empty($startpg) && strlen($startpg) > 5){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">Start Page must be five digits or less.</font>';
	}
	if(!empty($endpg) && !is_numeric($endpg)){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">End Page must be a number.</font>';
	}
	if(!empty($endpg) && strlen($endpg) > 5){
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">End Page must be five digits or less.</font>';
	}
	
	$values = "$uid,";
	
	!empty($posttitle) ? ($values .= "'$posttitle',") : ($values .= "NULL,") ;
	!empty($link) ? ($values .= "'$link',") : ($values .= "NULL,") ;
	!empty($year) ? ($values .= "'$year',") : ($values .= "NULL,") ;
	!empty($abstract) ? ($values .= "'$abstract',") : ($values .= "NULL,") ;
	!empty($journal) ? ($values .= "'$journal',") : ($values .= "NULL,") ;
	!empty($volume) ? ($values .= "'$volume',") : ($values .= "NULL,") ;
	!empty($issue) ? ($values .= "'$issue',") : ($values .= "NULL,") ;
	!empty($startpg) ? ($values .= "'$startpg',") : ($values .= "NULL,") ;
	!empty($endpg) ? ($values .= "'$endpg',") : ($values .= "NULL,") ;
	!empty($doi) ? ($values .= "'$doi'") : ($values .= "NULL") ;
	
	if(!$errors) {
		if($uprec && $upok) {
			$qry = "REPLACE INTO research (id,uid,title,link,yr,abstract,journal,volume,issue,startpg,endpg,doi) VALUES ($uprec,$values)";
		} else {
			$qry = "INSERT INTO research (uid,title,link,yr,abstract,journal,volume,issue,startpg,endpg,doi) VALUES ($values)";
		}
		mysql_query($qry);
		$resid = mysql_insert_id();
		
		$qry = "DELETE FROM researchtags WHERE research=$resid";
		mysql_query($qry);
		
		for($i=0;$i<count($tagarray);$i++){
		$usetag = clean(stripslashes(ltrim(rtrim($tagarray[$i]))));
		if($usetag == "") continue;
		$qry = "SELECT id FROM tags WHERE tag='$usetag' OR id='$usetag'";
		$result = mysql_query($qry);
		if($result && (mysql_num_rows($result) > 0)) {
			// If the tag is already in our database, link it to the research
			$row = mysql_fetch_array($result);
			$tagid  = $row['id'];
			@mysql_free_result($result);
		} else {
			// If not, we have to insert it into the database and THEN link it to the research
			@mysql_free_result($result);
			$qry = "INSERT INTO tags (tag) VALUES ('$usetag')";
			mysql_query($qry);
			$tagid = mysql_insert_id();
		}
		$qry = "INSERT INTO researchtags (research,tag) VALUES ($resid,$tagid) ON DUPLICATE KEY UPDATE research=research";
		mysql_query($qry);
		}
		
		header("Location: profile.php");
	} elseif(!empty($taginput)) {
		$posttags = array();
		foreach($tagarray as $posttag) {
			$posttagqry = "SELECT tags.id, tags.tag FROM tags WHERE tags.id = $posttag";
			$posttagresult = mysql_query($posttagqry);
			$posttagcheck = mysql_fetch_array($posttagresult);
			if(!empty($posttagcheck)) {
				$posttagentry = array();
				$posttagentry[id] = intval($posttagcheck[id]);
				$posttagentry[name] = $posttagcheck[tag];
				$posttags[] = $posttagentry;
			} else {
				$posttagentry = array();
				$posttagentry[id] = $posttag;
				$posttagentry[name] = $posttag;
				$posttags[] = $posttagentry;
			}
			@mysql_free_result($posttagresult);
		}
		
		if (!empty($posttags)) {
			$posttagjson = json_encode($posttags);
		}
	}
}	
	$protected = true;
	$tokeninput = true;
	$profile = true;
    include('header.php');
?>
<header>
<hgroup>
<h1><?php echo (!empty($prepaperid)) ? 'Edit' : 'Add' ;?> Research/Publication Info</h1>
<h2>Go ahead, don't be shy-promote yourself!</h2>
</hgroup>
<div class="error">
<?php
	if($form_errors) {
		foreach($form_errors as $msg) {
			echo $msg;
			echo "<br />";
		}
		unset($form_errors);
	}
?></div>
</header>

<form id="research" action="doresearch.php" enctype="multipart/form-data" method="post">
<fieldset>
<label for="title" onmouseover="tooltip.show('<span class=\'tiptext\'>a descriptive name or title for this entry<br /><br />140 characters or less, please. keep it tweetable!</span>');" onmouseout="tooltip.hide();">Title</label>
<input type="text" id="title" name="title" size="60" <?php if(!empty($prepaper['title'])) {echo('value="'.$prepaper['title'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($posttitle).'"');}?> style="width:320px;" required />
<label for="year" onmouseover="tooltip.show('<span class=\'tiptext\'>the year you published or completed your research<br /><br />not required</span>');" onmouseout="tooltip.hide();">Year</label>
<input type="text" id="year" name="year" <?php if(!empty($prepaper['yr'])) {echo('value="'.$prepaper['yr'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($year).'"');} ?> size="15" />
<br />
<?php 
if(!empty($prepaper['journal']) || !empty($prepaper['volume']) || !empty($prepaper['issue']) || !empty($prepaper['startpg']) || !empty($prepaper['endpg']) || !empty($prepaper['doi']) || !empty($journal) || !empty($volume) || !empty($issue) || !empty($startpg) || !empty($endpg) || !empty($doi)) {
	echo('<a href="javascript:void()" onclick="toggle_visibility(\'cite\',this);" style="font-size:x-small">hide citation info</a>
	<div id="cite" style="background:#EEEEFF;">');
} else {
	echo('<a href="javascript:void()" onclick="toggle_visibility(\'cite\',this);" style="font-size:x-small">add citation info</a>
	<div id="cite" style="display:none;background:#EEEEFF;">');
}
?>
<label for="journal" class="twocol">Journal</label>
<input type="text" id="journal" class="twocol" name="journal" <?php if(!empty($prepaper['journal'])) {echo('value="'.$prepaper['journal'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($journal).'"');}?> size="60" />
<br /><br />
<label for="volume" class="twocol">Volume</label>
<span class="twocol" style="background:inherit;">
<input type="number" id="volume" name="volume" min="1" max="99999" size="4" <?php if(!empty($prepaper['volume'])) {echo('value="'.$prepaper['volume'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($volume).'"');} ?> style="width:45px" />
<label for="issue">Issue</label>
<input type="number" id="issue" name="issue" min="1" max="99999" size="3" <?php if(!empty($prepaper['issue'])) {echo('value="'.$prepaper['issue'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($issue).'"');} ?> style="width:40px;" />
&emsp;
<label for="startpg">Pages</label>
<input type="number" id="startpg" name="startpg" min="1" max="99999" size="4" <?php if(!empty($prepaper['startpg'])) {echo('value="'.$prepaper['startpg'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($startpg).'"');} ?> style="width:45px;" />
<label for="endpg">to</label>
<input type="number" id="endpg" name="endpg" min="1" max="99999" size="4" <?php if(!empty($prepaper['endpg'])) {echo('value="'.$prepaper['endpg'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($endpg).'"');} ?> style="width:45px;" />
</span>
<br /><br />
<label for="doi" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>digital object identifier, if you were provided one<br /><br />click for more information</span>');" onmouseout="tooltip.hide();"><a href="http://www.doi.org" style="text-decoration:none; color:black;">DOI</a></label>
<input type="url" id="doi" class="twocol" name="doi" <?php if(!empty($prepaper['doi'])) {echo('value="'.$prepaper['doi'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($doi).'"');} ?> size="60" />
<br />
</div>
<br />
<label for="collab" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>the people you worked with on this project<br />your principal investigator, coauthors, etc.<br />all separated by commas<br /><br />not required</span>');" onmouseout="tooltip.hide();">Collaborators</label>
<input type="text" id="collab" class="twocol" name="collab" size="60" />
<br /><br />
<label for="link" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>a link to more information about this project<br />maybe a website or a PDF version of the paper?<br /><br />not required, no spam please</span>');" onmouseout="tooltip.hide();">Link</label>
<input type="url" id="link" class="twocol" name="link" <?php if(!empty($prepaper['link'])) {echo('value="'.$prepaper['link'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($link).'"');} ?> size="60" />
<br /><br />
<label for="abstract"  onmouseover="tooltip.show('<span class=\'tiptext\'>100-500 words describing the project in abstract<br />focus on the aspects you could give a talk about<br />maybe interesting findings or innovative bits about your methodology?<br /><br />not required, but highly recommended</span>');" onmouseout="tooltip.hide();" style="text-align:center;">Abstract or Description</label>
<br />
<textarea id="abstract" name="abstract" rows="5" cols="60" style="margin:auto;"><?php if(!empty($prepaper['abstract'])) {echo($prepaper['abstract']);} elseif($_POST['form_submitted']) {echo(stripcslashes($abstract));} ?></textarea>
<br /><br />
    <label for="tags" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>some key words to describe your research<br /><br />be specific! add as many as you like<br />start typing and we\'ll make some suggestions</span>');" onmouseout="tooltip.hide();"><div class="taglabel">Tags<br /><small>Keywords</small></div></label>
<input type="text" class="twocol" id="tags" name="tags" size="60" />
<script type="text/javascript">
        $(document).ready(function() {
            $("#tags").tokenInput("/json/tag-search.php", {
				hintText: "Start typing some keywords",
				preventDuplicates: true,
				theme: "facebook"
				<?php if (isset($pretagjson)) {echo(",
				prePopulate: $pretagjson");}
				elseif ($_POST['form_submitted'] && !empty($posttagjson)) {echo(",
				prePopulate: $posttagjson");} ?>
            });
        });
</script>
    <input type="hidden" name="form_submitted" value="<?php echo (!empty($prepaperid)) ? '2' : '1' ;?>" />
	<?php if(!empty($prepaperid)) echo('<input type="hidden" name="record_to_update" value="'.$prepaperid.'" />'); ?>
<br />
<br />
<input type="submit" value="<?php echo (!empty($prepaperid)) ? 'Update' : 'Add' ;?>" />
<a style="text-decoration:none" href="/profile.php"><input type="button" name="cancel" value="Cancel" /></a>
</fieldset>
</form>
<?php
	include('footer.php');
?>