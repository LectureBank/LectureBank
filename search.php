<?php
require_once('config/database-connect.php');

$input = str_replace('_',' ',clean($_GET["tag"]));

$qry = "SELECT id, tag FROM tags WHERE tag='$input' OR id='$input'";
$result = mysql_query($qry);
$row = mysql_fetch_array($result);
$tagid = $row['id'];
$tagname  = $row['tag'];
@mysql_free_result($result);

$qry = "SELECT users.name AS name, users.username AS username, institutions.name AS inst FROM users, institutions, userinterests, userinstitutions WHERE userinterests.intid = $tagid AND users.id_user = userinterests.uid AND userinstitutions.uid = users.id_user AND institutions.id= userinstitutions.instid";
$result = mysql_query($qry);
	while($row = mysql_fetch_array($result)) {
		$personlist[] = array(name => $row['name'], username => $row['username'], inst => $row['inst']);
	}
	@mysql_free_result($result);

$title = "Tag Search";
$profile = true;
include('header.php');
?>
<?php
	if (!empty($tagname)) {
		echo("<h3>People tagged \"$tagname\"</h3><hr />");
		if (!empty($personlist)) {
			foreach($personlist as $person) {
				echo ("<a href=\"/".$person['username']."\">".$person['name']."</a> ".$person['inst']."<br />");
		}} else {
			echo("No results");
		}
	} else {
		echo("<h3>Tag does not exist</h3>");
	}
	
	include('footer.php');
?>
