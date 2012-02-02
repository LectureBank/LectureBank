<?php
require_once('../config/database-connect.php');

$input = clean($_GET["q"]);
$data = array();

$query = mysql_query("SELECT id, name FROM institutions WHERE name LIKE '%%$input%%' LIMIT 10");
while ($row = mysql_fetch_object($query)) {
$data[] = $row;
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
