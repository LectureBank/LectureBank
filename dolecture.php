<?php 
	session_start();
	
	/* Connect to the database */
	require_once('config/database-connect.php');
	require_once('handle-institution.php');
	
	$email = $_SESSION["email"];
	$uid = $_SESSION["uid"];
	
	$toedit = clean($_GET["edit"]);
	$todelete = clean($_GET["delete"]);
	
	$title = "Add A Lecture";
	if (!empty($todelete) && !empty($uid)) {
		$title = "Delete Lecture";
		$qry = "SELECT id, creator FROM lectures WHERE id='$todelete' AND creator='$uid'";
		$result = mysql_query($qry);
		$delcheck = mysql_fetch_array($result);
		if($result && (mysql_num_rows($result) > 0) && ($delcheck['creator'] == $uid)) {
			@mysql_free_result($result);
			$qry = "DELETE FROM lectures WHERE id='$todelete'";
			mysql_query($qry);
			header("Location: profile.php");
		} else {
			@mysql_free_result($result);
			header("Location: profile.php");
		}
	} elseif (!empty($toedit) && !empty($uid)) {
		$preqry = "SELECT * FROM lectures WHERE id=$toedit";
		$preqryresult = mysql_query($preqry);
		$checkprelect = mysql_fetch_array($preqryresult);
		if($checkprelect && ($checkprelect['creator'] == $uid)) {
			$prelect = $checkprelect;
			$prelectid = $prelect['id'];
			$preinstqry = "SELECT institutions.id, institutions.name FROM institutions WHERE institutions.id = ".$prelect['loc_id'];
			$preinstresult = mysql_query($preinstqry);
			$preinst = mysql_fetch_array($preinstresult);
			if(!empty($preinst)) {
				$preinstjson  = json_encode($preinst);
			}
			@mysql_free_result($preinstresult);
			
			$pretagqry = "SELECT tags.id, tags.tag FROM tags, lecturetags WHERE lecturetags.lecture = $toedit && tags.id = lecturetags.tag";
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
			$title = "Edit Lecture";
		} else {
			$errors = true;
			$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">You do not have permission to edit this record.</font>';
		}
		@mysql_free_result($checkprelect);
		
	} elseif ($_POST['form_submitted'] && !empty($uid)) {
		
		//Sanitize the POST values
		$posttitle = clean($_POST["title"]);
		$eventname = clean($_POST["eventname"]);
		$location = trim(clean($_POST["location"]),",");
		$abstract = clean($_POST["abstract"]);
		$link = clean($_POST["link"]);
		$locdetail = clean($_POST["locdetail"]);
		$date = clean($_POST["date"]);
		$starttime = clean($_POST["starttime"]);
		$endtime = clean($_POST["endtime"]);
		$timezone = clean($_POST["timezone"]);
		$taginput = clean($_POST["tags"]);
		$tagarray = explode(",",$taginput);
		
		if ($_POST['form_submitted'] == '2' && $_POST['record_to_update'] && !empty($uid)){
			$uprec = $_POST['record_to_update'];
			$permitqry = "SELECT creator FROM lectures WHERE id=$uprec";
			$permitresult = mysql_query($permitqry);
			$permitresult = mysql_fetch_array($permitresult);
			if($permitresult && ($permitresult['creator'] == $uid)) {
				$upok = true;
				$title = "Edit Lecture";
				$prelectid = "$uprec";
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
		if(!empty($date) && !empty($starttime)) {
			$start = date("Y-m-d H:i:00", mktime(date("H", strtotime($starttime)),date("i", strtotime($starttime)),00,date("m", strtotime($date)),date("d", strtotime($date)),date("Y", strtotime($date))));
		} else {
			$errors = true;
			$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">You must submit a date and time.</font>';
		}
		
		if(!empty($date) && !empty($endtime)) {
			$end = date("Y-m-d H:i:00", mktime(date("H", strtotime($endtime)),date("i", strtotime($endtime)),00,date("m", strtotime($date)),date("d", strtotime($date)),date("Y", strtotime($date))));
		} 
		
		if(!empty($location)){
			$location = handle_institution($location);
			if(empty($location)) {
				$errors = true;
				$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">We couldn\'t find that institution. Try a different spelling?</font>';
			}
		} else {
			$errors = true;
			$form_errors[] = '<img src="/images/error.png" align="absmiddle">&nbsp;<font color="red">You must enter a sponsoring institution.</font>';
		}
		
		$values = "$uid,";
		
		!empty($posttitle) ? ($values .= "'$posttitle',") : ($values .= "NULL,") ;
		!empty($eventname) ? ($values .= "'$eventname',") : ($values .= "NULL,") ;
		!empty($location) ? ($values .= "'$location',") : ($values .= "NULL,") ;
		!empty($abstract) ? ($values .= "'$abstract',") : ($values .= "NULL,") ;
		!empty($link) ? ($values .= "'$link',") : ($values .= "NULL,") ;
		!empty($locdetail) ? ($values .= "'$locdetail',") : ($values .= "NULL,") ;
		!empty($start) ? ($values .= "'$start',") : ($values .= "NULL,") ;
		!empty($end) ? ($values .= "'$end',") : ($values .= "NULL,") ;
		!empty($timezone) ? ($values .= "'$timezone'") : ($values .= "'-5'") ;
		
		if (!$errors) {
			if($uprec && $upok) {
				$qry = "REPLACE INTO lectures (id,creator,title,eventname,loc_id,abstract,link,locdetail,start,end,timezone,sequence) VALUES($uprec,$values,sequence+1)";
			} else {
				$qry = "INSERT INTO lectures (creator,title,eventname,loc_id,abstract,link,locdetail,start,end,timezone) VALUES($values)";
			}
			mysql_query($qry);
			$resid = mysql_insert_id();
		
			$qry = "DELETE FROM lecturetags WHERE lecture=$resid";
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
			$qry = "INSERT INTO lecturetags (lecture,tag) VALUES ($resid,$tagid) ON DUPLICATE KEY UPDATE lecture=lecture";
			mysql_query($qry);
			}
		
			header("Location: profile.php");
		} elseif(!empty($location) || !empty($taginput)) {
			if(!empty($location)) {
				$postinstqry = "SELECT institutions.id, institutions.name FROM institutions WHERE institutions.id = ".$location;
				$postinstresult = mysql_query($postinstqry);
				$postinstcheck = mysql_fetch_array($postinstresult);
				if(!empty($postinstcheck)) {
					$postinst = array();
					$postinst[id] = $postinstcheck[id];
					$postinst[name] = $postinstcheck[name];
					$postinstjson  = json_encode($postinst);
					echo($postinstjson);
				}
				@mysql_free_result($postinstresult);
			}
			
			if(!empty($taginput)) {
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
	}
	
	$datepicker = true;
	$tokeninput = true;
	$protected = true;
	$profile = true;
	include('header.php');
?>

<header>
<hgroup>
<h1><?php echo (!empty($prelectid)) ? 'Edit' : 'Add' ;?> Lecture</h1>
<h2>Past, present, or future-tell us about your talking experiences!</h2>
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
<form id="lecture" action="dolecture.php" enctype="multipart/form-data" method="post">
  <fieldset>
    <label for="title" onmouseover="tooltip.show('<span class=\'tiptext\'>a descriptive name or title for this entry<br /><br />140 characters or less, please. keep it tweetable!</span>');" onmouseout="tooltip.hide();">Title</label>
    <input type="text" id="title" name="title" size="60" style="width:320px;" <?php if(!empty($prelect['title'])) {echo('value="'.$prelect['title'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($posttitle).'"');} ?> required />
    <label for="date" onmouseover="tooltip.show('<span class=\'tiptext\'>the date of the event you spoke at<br />self explanatory..?<br /><br />required</span>');" onmouseout="tooltip.hide();">Date</label>
    <input type="date" id="date" name="date" size="15" <?php if(!empty($prelect['start'])) {echo('value="'.date('m/d/Y',strtotime($prelect['start'])).'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($date).'"');} ?> required />
    <script type="text/javascript">
	var i = document.createElement("input");
	i.setAttribute("type", "date");
	if(i.type == "text"){    
        var opts = {
			formElements:{"date":"m-sl-d-sl-Y"}
			<?php if(!empty($prelect['start'])) {echo(',
			cursorDate:"'.date('Ymd',strtotime($prelect['start'])).'"');}
			elseif($_POST['form_submitted'] && !empty($date)) {echo(',
			cursorDate:"'.date('Ymd',strtotime($date)).'"');}?>
		};
		datePickerController.createDatePicker(opts);
	}
    </script>
    <br /><br />
    <label for="starttime">Starting at</label>
    <select name="starttime" id="starttime" required>
    <?php
	$tm_start = strtotime('00:00');
	$tm_end = strtotime('23:30');
	
	for ($i = $tm_start; $i <= $tm_end; $i += 1800) {
		if(!empty($prelect['start'])) {
			echo ($i == strtotime(date('H:i',strtotime($prelect['start']))) ? '<option selected value="'.date('H:i',$i).'">' : '<option value ="'.date('H:i',$i).'">') . date('H:i', $i) . '</option>';
		} elseif($_POST['form_submitted'] && !empty($start)) {
			echo ($i == strtotime(date('H:i',strtotime($start))) ? '<option selected value="'.date('H:i',$i).'">' : '<option value ="'.date('H:i',$i).'">') . date('H:i', $i) . '</option>';
		} else {
			echo ($i == strtotime('12:00') ? '<option selected value="'.date('H:i',$i).'">' : '<option value ="'.date('H:i',$i).'">') . date('H:i', $i) . '</option>';
		}
	}
	?>
    </select>
    &emsp;
    <label for="endtime">Ending at</label>
    <select name="endtime" id="endtime">
    <?php
	$tm_start = strtotime('00:00');
	$tm_end = strtotime('12:00');
	
	for ($i = $tm_start; $i <= $tm_end; $i += 1800) {
		if(!empty($prelect['end'])) {
			echo ($i == strtotime(date('H:i',strtotime($prelect['end']))) ? '<option selected value="'.date('H:i',$i).'">' : '<option value ="'.date('H:i',$i).'">') . date('H:i', $i) . '</option>';
		} elseif($_POST['form_submitted'] && !empty($end)) {
			echo ($i == strtotime(date('H:i',strtotime($end))) ? '<option selected value="'.date('H:i',$i).'">' : '<option value ="'.date('H:i',$i).'">') . date('H:i', $i) . '</option>';
		} else {
			echo '<option value ="'.date('H:i',$i).'">' . date('H:i', $i) . '</option>';
		}
	}
	
    if(empty($prelect['end']) && empty($endtime)) echo('<option selected />');
    
	$tm_start = strtotime('12:30');
	$tm_end = strtotime('23:30');
	
	for ($i = $tm_start; $i <= $tm_end; $i += 1800) {
		if(!empty($prelect['end'])) {
			echo ($i == strtotime(date('H:i',strtotime($prelect['end']))) ? '<option selected value="'.date('H:i',$i).'">' : '<option value ="'.date('H:i',$i).'">') . date('H:i', $i) . '</option>';
		} elseif($_POST['form_submitted'] && !empty($end)) {
			echo ($i == strtotime(date('H:i',strtotime($end))) ? '<option selected value="'.date('H:i',$i).'">' : '<option value ="'.date('H:i',$i).'">') . date('H:i', $i) . '</option>';
		} else {
			echo '<option value ="'.date('H:i',$i).'">' . date('H:i', $i) . '</option>';
		}
	}
	?>
    </select>
    <script type="text/javascript">
	var i = document.createElement("input");
	i.setAttribute("type", "time");
	if(i.type !== "text"){    
        var e1 = document.getElementById("starttime");
		var e2 = document.getElementById("endtime");
		var t1 = document.createElement("input");
		var t2 = document.createElement("input");
		t1.setAttribute("type", "time");
		t2.setAttribute("type", "time");
		t1.setAttribute("size", "10");
		t2.setAttribute("size", "10");
		t1.setAttribute("required", "required");
		t1.setAttribute("name", "starttime");
		t1.setAttribute("id", "starttime");
		t2.setAttribute("name", "endtime");
		t2.setAttribute("id", "endtime");
		<?php if(!empty($prelect['start'])) {echo('t1.setAttribute("value", "'.date('H:i',strtotime($prelect['start'])).'");');} elseif($_POST['form_submitted'] && !empty($starttime)) {echo('t1.setAttribute("value", "'.date('H:i',strtotime($starttime)).'");');} ?>
		<?php if(!empty($prelect['end'])) {echo('t2.setAttribute("value", "'.date('H:i',strtotime($prelect['end'])).'");');} elseif($_POST['form_submitted'] && !empty($endtime)) {echo('t2.setAttribute("value", "'.date('H:i',strtotime($endtime)).'");');} ?>
		e1.parentNode.replaceChild(t1,e1);
		e2.parentNode.replaceChild(t2,e2);
	}
    </script>
    <br />
    <br />
    <label for="locdetail" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>address or building/floor/room, etc.<br /><br />not required</span>');" onmouseout="tooltip.hide();">Where</label>
    <textarea id="locdetail" class="twocol" name="locdetail" rows="3" cols="60"><?php if(!empty($prelect['locdetail'])) {echo($prelect['locdetail']);} elseif($_POST['form_submitted']) {echo(stripcslashes($locdetail));} ?></textarea>
<br />
<br />
    <label for="location" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>what institution or organization did you speak at?<br />start typing and we\'ll make some suggestions<br /><br />required</span>');" onmouseout="tooltip.hide();">Sponsor</label>
    <div>
        <input type="text" id="location" class="twocol" name="location" size="60" required />
        <script type="text/javascript">
        $(document).ready(function() {
            $("#location").tokenInput("/json/institution-search.php", {
				hintText: "Start typing the name of an institution",
                tokenLimit: 1,
				preventDuplicates: true
				<?php if (isset($preinst)) {echo(",
				prePopulate: [$preinstjson]");} 
				elseif ($_POST['form_submitted'] && !empty($postinstjson)) {echo(",
				prePopulate: [$postinstjson]");} ?>
            });
        });
        </script>
    </div>
    <br />
    <br />
    <label for="name" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>what conference or series was your talk a part of?<br /><br />not required</span>');" onmouseout="tooltip.hide();">Event</label>
    <input type="text" id="eventname" class="twocol" name="eventname" <?php if(!empty($prelect['eventname'])) {echo('value="'.$prelect['eventname'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($eventname).'"');}  ?> size="60" />
    <br />
    <br />
<label for="link" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>a link to more information about this event<br /><br />not required, no spam please</span>');" onmouseout="tooltip.hide();">Link</label>
<input type="url" id="link" class="twocol" name="link" <?php if(!empty($prelect['link'])) {echo('value="'.$prelect['link'].'"');} elseif($_POST['form_submitted']) {echo('value="'.stripcslashes($link).'"');}  ?> size="60" />
    <br />
    <br />
    <label for="abstract" onmouseover="tooltip.show('<span class=\'tiptext\'>100-500 words describing the project in abstract<br />focus on the aspects you could give a talk about<br />maybe interesting findings or innovative bits about your methodology?<br /><br />not required, but highly recommended</span>');" onmouseout="tooltip.hide();" style="text-align:center;">Abstract or Description</label>
    <br />
    <textarea id="abstract" name="abstract" rows="5" cols="60" style="margin:auto;"><?php if(!empty($prelect['abstract'])) {echo($prelect['abstract']);} elseif($_POST['form_submitted']) {echo(stripcslashes($abstract));}  ?></textarea>
    <br />
    <br />
    <label for="tags" class="twocol" onmouseover="tooltip.show('<span class=\'tiptext\'>some key words to describe your lecture<br /><br />be specific! add as many as you like<br />start typing and we\'ll make some suggestions</span>');" onmouseout="tooltip.hide();"><div class="taglabel">Tags<br /><small>Keywords</small></div></label>
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
<input type="hidden" name="timezone" id="timezone" value="" />
<script>
// script by Josh Fraser (http://www.onlineaspect.com)

function calculate_time_zone() {
	var rightNow = new Date();
	var jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);  // jan 1st
	var june1 = new Date(rightNow.getFullYear(), 6, 1, 0, 0, 0, 0); // june 1st
	var temp = jan1.toGMTString();
	var jan2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
	temp = june1.toGMTString();
	var june2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
	var std_time_offset = (jan1 - jan2) / (1000 * 60 * 60);
	var daylight_time_offset = (june1 - june2) / (1000 * 60 * 60);
	var dst;
	if (std_time_offset == daylight_time_offset) {
		dst = "0"; // daylight savings time is NOT observed
	} else {
		// positive is southern, negative is northern hemisphere
		var hemisphere = std_time_offset - daylight_time_offset;
		if (hemisphere >= 0)
			std_time_offset = daylight_time_offset;
		dst = "1"; // daylight savings time is observed
	}
	var i;
	// check just to avoid error messages
	if (document.getElementById('timezone')) {
		e = document.getElementById('timezone');
		e.value = convert(std_time_offset);
	}
}

function convert(value) {
	var hours = parseInt(value);
   	value -= parseInt(value);
	value *= 60;
	var mins = parseInt(value);
   	value -= parseInt(value);
	value *= 60;
	var secs = parseInt(value);
	var display_hours = hours;
	// handle GMT case (00:00)
	if (hours == 0) {
		display_hours = "00";
	} else if (hours > 0) {
		// add a plus sign and perhaps an extra 0
		display_hours = (hours < 10) ? "+0"+hours : "+"+hours;
	} else {
		// add an extra 0 if needed 
		display_hours = (hours > -10) ? "-0"+Math.abs(hours) : hours;
	}
	
	mins = (mins < 10) ? "0"+mins : mins;
	return display_hours;
}


calculate_time_zone();
</script>
    <input type="hidden" name="form_submitted" value="<?php echo (!empty($prelectid)) ? '2' : '1' ;?>" />
	<?php if(!empty($prelectid)) echo('<input type="hidden" name="record_to_update" value="'.$prelectid.'" />'); ?>
    <br />
    <br />
    <input type="submit" value="<?php echo (!empty($prelectid)) ? 'Update' : 'Add' ;?>" />
    <a style="text-decoration:none" href="/profile.php"><input type="button" name="cancel" value="Cancel" /></a>
  </fieldset>
</form>

<?php include('footer.php');?>
