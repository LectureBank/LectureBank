<?php
	session_start();
	
	/* Connect to the database */
	require_once('config/database-connect.php');
	require_once('handle-institution.php');
	require_once('handle-zip.php');
	
	$email = $_SESSION["email"];
	$uid = $_SESSION["uid"];
	
if ($_POST['form_submitted'] == '1' && !empty($uid)) {
	
	//Validation error flag
	$errflag = false;
	
	//Sanitize the POST values
	$name = clean($_POST["name"]);
	$institution = clean($_POST["institution"]);
	$zip = clean($_POST["zip"]);
	$field = clean($_POST["field"]);
	$intinput = clean($_POST["interests"]);
	$intarray = explode(",",$intinput);
	
	$ziplocate = handle_zip($zip);
	if(empty($ziplocate)) {
		$errors = true;
		$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;Sorry, we couldn\'t locate that zip code. Try entering it again?';
	} else {
		$qry = "UPDATE users SET name='$name', zip='$zip' WHERE id_user=$uid";
		mysql_query($qry);
	}
	
	$qry = "INSERT INTO userfields (uid,fcode,type) VALUES ($uid,'$field','PRIM') ON DUPLICATE KEY UPDATE fcode='$field'";
	mysql_query($qry);
	
	if(!empty($institution)){
		$instid = handle_institution($institution);
		if(empty($instid)) {
			$errors = true;
			$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;We couldn\'t find that institution. Try a different spelling?';
		} else {
			$qry = "INSERT INTO userinstitutions (uid,instid) VALUES ($uid,$instid) ON DUPLICATE KEY UPDATE instid=$instid";
			mysql_query($qry);
		}
	} else {
		$qry = "DELETE FROM userinstitutions WHERE uid=$uid";
		mysql_query($qry);
	}
	
	$qry = "DELETE FROM userinterests WHERE uid=$uid";
	mysql_query($qry);
	
	for($i=0;$i<count($intarray);$i++){
	$usetag = clean(stripslashes(ltrim(rtrim($intarray[$i]))));
	if($usetag == "") continue;
	$qry = "SELECT id FROM tags WHERE tag='$usetag' OR id='$usetag'";
	$result = mysql_query($qry);
	if($result && (mysql_num_rows($result) > 0)) {
		// If the tag is already in our database, link it to the user
		$row = mysql_fetch_array($result);
   		$intid  = $row['id'];
		@mysql_free_result($result);
	} else {
		// If not, we have to insert it into the database and THEN link it to the user
		@mysql_free_result($result);
		$qry = "INSERT INTO tags (tag) VALUES ('$usetag')";
		mysql_query($qry);
		$intid = mysql_insert_id();
	}
	$qry = "INSERT INTO userinterests (uid,intid) VALUES ($uid,$intid) ON DUPLICATE KEY UPDATE uid=uid";
	mysql_query($qry);
	}
	
	if(empty($form_errors)){
		header("Location: profile.php");
	}
}


	/* Get all user information */
	$qry = "SELECT name, zip FROM users WHERE id_user=$uid LIMIT 1";
    $result = mysql_query($qry);
	$row = mysql_fetch_array($result);
   	$prename  = $row['name'];
	$prezip = $row['zip'];
	@mysql_free_result($result);
	$qry = "SELECT institutions.id, institutions.name FROM institutions, userinstitutions WHERE userinstitutions.uid = $uid && institutions.id = userinstitutions.instid";
	$result = mysql_query($qry);
	$row = mysql_fetch_array($result);
	if($row) {
   		$preinst  = json_encode($row);
	}
	@mysql_free_result($result);
	$qry = "SELECT fields.code FROM fields, userfields WHERE userfields.uid = $uid && fields.code = userfields.fcode";
	$result = mysql_query($qry);
	$row = mysql_fetch_array($result);
   	$prefield  = $row['code'];
	@mysql_free_result($result);
	
	$qry = "SELECT tags.id, tags.tag FROM tags, userinterests WHERE userinterests.uid = $uid && tags.id = userinterests.intid";
	$result = mysql_query($qry);
	$preints = array();

	while ($row = mysql_fetch_array($result)) {
		$preint = array();
		$preint[id] = intval($row[id]);
		$preint[name] = $row[tag];
		$preints[] = $preint;
	}

	if (!empty($preints)) {
		$preinterests = json_encode($preints);
	}
	@mysql_free_result($result);

	/* Get the list of "broader fields of study" */
	$qry = "SELECT * FROM fields";
    $result = mysql_query($qry);
	$fields = array();
	while ($row = mysql_fetch_array($result)) {
		$fields[] = array(code => $row['code'], name => $row['name']);
	}
	
	$title = "Create Your Profile";
	$protected = true;
	$profile = true;
	$tokeninput = true;
    include('header.php');
?>

<h1>Create Your Profile</h1>
<h2>Tell us a little about yourself!</h2>
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
<form id="profile" action="updateprofile.php" enctype="multipart/form-data" method="post">
<fieldset>
<label for="name" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>your name as you want it displayed on the site<br /><br />this is so people you know can find you</span>');" onmouseout="tooltip.hide();">Name</label>
<input type="text" class="twocol" id="name" name="name" value="<?php echo($prename) ?>" size="60" tabindex=0 />
<br /><br />
<label for="institution" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>the organization where you are studying or working<br /><br />this makes it easier to connect with your coworkers<br />and helps us identify opportunities near you</span>');" onmouseout="tooltip.hide();">Institution</label>
<input type="text" class="twocol" id="institution" name="institution" size="60" />
<script type="text/javascript">
        $(document).ready(function() {
            $("#institution").tokenInput("/json/institution-search.php", {
				hintText: "Start typing the name of an institution",
                tokenLimit: 1,
				preventDuplicates: true
				<?php if (isset($preinst)) echo(",
				prePopulate: [$preinst]") ?>
            });
        });
</script>
<br /><br />
<label for="zip" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>so we can show you relevant opportunities near you</span>');" onmouseout="tooltip.hide();">ZIP Code</label>
<input type="text" class="twocol" id="zip" name="zip" value="<?php echo($prezip) ?>" size="15" />
<br /><br />
<label for="field" class="twocol"  onmouseover="tooltip.show('<span class=\'tiptext\'>the field that best matches your degree program<br /><br />we\'re adding new fields regularly.<br />if yours isn\'t here, click and let us know!</span>');" onmouseout="tooltip.hide();"><a href="mailto:admin@lecturebank.org" style="text-decoration:none; color:black;">Primary Field of Study</a></label>
<select id="field" class="twocol" name="field">
  <?php if (empty($prefield)) echo("<option selected></option>") ?>
  <?php
  	/* While loop to list all of the field options from the database */
  	foreach($fields as $field) {
		$fieldcode = $field['code'];
		$fieldname = $field['name'];
		if ($prefield == $fieldcode) {
			echo("<option selected value=\"$fieldcode\">$fieldname</option>"); 
		} else {
			echo("<option value=\"$fieldcode\">$fieldname</option>"); 
		}
	}
  ?>
</select>
<br /><br />
<label for="interests" class="twocol"   onmouseover="tooltip.show('<span class=\'tiptext\'>some key words to describe your research focus<br />and your scientific interests<br /><br />be specific! add as many as you like<br />start typing and we\'ll make some suggestions</span>');" onmouseout="tooltip.hide();"><div class="taglabel">Areas of Interest<br /><small>Keywords</small></div></label>
<input type="text" class="twocol" id="interests" name="interests" size="60" />
<script type="text/javascript">
        $(document).ready(function() {
            $("#interests").tokenInput("/json/tag-search.php", {
				hintText: "Start typing some keywords",
				preventDuplicates: true,
				theme: "facebook"
				<?php if (isset($preinterests)) echo(",
				prePopulate: $preinterests") ?>
            });
        });
</script>
<input type="hidden" name="form_submitted" value="1" />
<br /><br />
<input type="submit" value="Update" />
</fieldset>
</form>
<br />
Or you can <a href="profile.php">skip this &gt;</a>
<?php
	include('footer.php');
?>