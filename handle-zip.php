<?php
require_once('config/database-connect.php');

function handle_zip($zip) {
if(is_numeric($zip) && strlen($zip) >= 5) {
	$zip = substr($zip, 0, 5);
	$qry = "SELECT * FROM ziplocation WHERE zip='$zip'";
	$result = mysql_query($qry);
	if($result && (mysql_num_rows($result) > 0)) {
		$row = mysql_fetch_array($result);
   		$location  = $row['id'];
		@mysql_free_result($result);
		return $zip;
	} else {
		@mysql_free_result($result);
                $gquery_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$zip."&sensor=false";

                $ch = curl_init();
                $timeout = 5; // set to zero for no timeout
                curl_setopt ($ch, CURLOPT_URL, $gquery_url);
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $google_response = curl_exec($ch);
                curl_close($ch);

		// $google_response=file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".$zip."&sensor=false");

		$google_result = json_decode($google_response, true);
		if($google_result['status'] == "OK") {
			$lat = $google_result['results'][0]['geometry']['location']['lat'];
			$lon = $google_result['results'][0]['geometry']['location']['lng'];
			$address = array();
			foreach($google_result['results'][0]['address_components'] as $address_component) {
				foreach($address_component['types'] as $type) {
					switch ($type) {
    					case 'locality':
        					$address['city'] = $address_component['long_name'];
        					break;
						case 'administrative_area_level_1':
        					$address['state'] = $address_component['short_name'];
        					break;
					}
				}
			}
			$city = $address['city'];
			$state = $address['state'];
			$qry = "INSERT INTO ziplocation (zip, city, state, lat, lon) VALUES ('$zip', '$city', '$state', '$lat', '$lon')";
			mysql_query($qry);
			return $zip;
		} else {
			return NULL;
	}
	}
	} else {
		return NULL;
	}
}

?>