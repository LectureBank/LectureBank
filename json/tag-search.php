<?php
require_once('../config/database-connect.php');

$input = clean($_GET["q"]);
$data = array();

$query = mysql_query("SELECT id, tag FROM tags WHERE tag LIKE '%%$input%%' LIMIT 10");
while ($row = mysql_fetch_array($query)) {
$json = array();
$json[id] = intval($row[id]);
$json[name] = $row[tag];
$data[] = $json;
}

$json_response = json_encode($data);

# Optionally: Wrap the response in a callback function for JSONP cross-domain support
if($_GET["callback"]) {
    $json_response = $_GET["callback"] . "(" . $json_response . ")";
}
# Return the response
header("Content-type: application/json");
echo $json_response;
?>