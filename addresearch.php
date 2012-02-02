<?php
	session_start();
	
	/* Connect to the database */
	require_once('config/database-connect.php');
	
	$email = $_SESSION["email"];
	$uid = $_SESSION["uid"];
	
	if ($_POST['form_submitted'] == '1' && !empty($uid)) {
	
	
	//Sanitize the POST values
	$title = clean($_POST["title"]);
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
	
	$values = "$uid,";
	
	!empty($title) ? ($values .= "'$title',") : ($values .= "NULL,") ;
	!empty($link) ? ($values .= "'$link',") : ($values .= "NULL,") ;
	!empty($year) ? ($values .= "'$year',") : ($values .= "NULL,") ;
	!empty($abstract) ? ($values .= "'$abstract',") : ($values .= "NULL,") ;
	!empty($journal) ? ($values .= "'$journal',") : ($values .= "NULL,") ;
	!empty($volume) ? ($values .= "'$volume',") : ($values .= "NULL,") ;
	!empty($issue) ? ($values .= "'$issue',") : ($values .= "NULL,") ;
	!empty($startpg) ? ($values .= "'$startpg',") : ($values .= "NULL,") ;
	!empty($endpg) ? ($values .= "'$endpg',") : ($values .= "NULL,") ;
	!empty($doi) ? ($values .= "'$doi'") : ($values .= "NULL") ;
	
	$qry = "INSERT INTO research (uid,title,link,yr,abstract,journal,volume,issue,startpg,endpg,doi) VALUES ($values)";
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
}	
	
	$title = "Add Research";
	$protected = true;
	$tokeninput = true;
	$profile = true;
    include('header.php');
?>

<h1>Add Research/Publication Info</h1>
<h2>Go ahead, don't be shy-promote yourself!</h2>

<form id="research" action="addresearch.php" enctype="multipart/form-data" method="post">
<fieldset>
<label for="title" onmouseover="tooltip.show('<span class=\'tiptext\'>a descriptive name or title for this entry<br /><br />140 characters or less, please. keep it tweetable!</span>');" onmouseout="tooltip.hide();">Title</label>
<input type="text" id="title" name="title" size="60" style="width:320px;" required />
<label for="year" onmouseover="tooltip.show('<span class=\'tiptext\'>the year you published or completed your research<br /><br />not required</span>');" onmouseout="tooltip.hide();">Year</label>
<input type="text" id="year" name="year" size="15" />
<br />
<a href="javascript:void()" onclick="toggle_visibility('cite',this);" style="font-size:x-small">add citation info</a>
<div id="cite" style="display:none;background:#EEEEFF;">
<label for="journal" class="twocol">Journal</label>
<input type="text" id="journal" class="twocol" name="journal" size="60" />
<br /><br />
<label for="volume" class="twocol">Volume</label>
<span class="twocol" style="background:inherit;">
<input type="number" id="volume" name="volume" min="1" max="99999" size="4" style="width:45px" />
<label for="issue">Issue</label>
<input type="number" id="issue" name="issue" min="1" max="99999" size="3" style="width:40px;" />
&emsp;
<label for="startpg">Pages</label>
<input type="number" id="startpg" name="startpg" min="1" max="99999" size="4" style="width:45px;" />
<label for="endpg">to</label>
<input type="number" id="endpg" name="endpg" min="1" max="99999" size="4" style="width:45px;" />
</span>
<br /><br />
<label for="doi" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>digital object identifier, if you were provided one<br /><br />click for more information</span>');" onmouseout="tooltip.hide();"><a href="http://www.doi.org" style="text-decoration:none; color:black;">DOI</a></label>
<input type="url" id="doi" class="twocol" name="doi" size="60" />
<br />
</div>
<br />
<label for="collab" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>the people you worked with on this project<br />your principal investigator, coauthors, etc.<br />all separated by commas<br /><br />not required</span>');" onmouseout="tooltip.hide();">Collaborators</label>
<input type="text" id="collab" class="twocol" name="collab" size="60" />
<br /><br />
<label for="link" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>a link to more information about this project<br />maybe a website or a PDF version of the paper?<br /><br />not required, no spam please</span>');" onmouseout="tooltip.hide();">Link</label>
<input type="url" id="link" class="twocol" name="link" size="60" />
<br /><br />
<label for="abstract"  onmouseover="tooltip.show('<span class=\'tiptext\'>100-500 words describing the project in abstract<br />focus on the aspects you could give a talk about<br />maybe interesting findings or innovative bits about your methodology?<br /><br />not required, but highly recommended</span>');" onmouseout="tooltip.hide();" style="text-align:center;">Abstract or Description</label>
<br />
<textarea id="abstract" name="abstract" rows="5" cols="60" style="margin:auto;"></textarea>
<br /><br />
    <label for="tags" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>some key words to describe your research<br /><br />be specific! add as many as you like<br />start typing and we\'ll make some suggestions</span>');" onmouseout="tooltip.hide();"><div class="taglabel">Tags<br /><small>Keywords</small></div></label>
<input type="text" class="twocol" id="tags" name="tags" size="60" />
<script type="text/javascript">
        $(document).ready(function() {
            $("#tags").tokenInput("/json/tag-search.php", {
				hintText: "Start typing some keywords",
				preventDuplicates: true,
				theme: "facebook"
            });
        });
</script>
<input type="hidden" name="form_submitted" value="1" />
<br />
<br />
<input type="submit" value="Add" />
</fieldset>
</form>
<?php
	include('footer.php');
?>