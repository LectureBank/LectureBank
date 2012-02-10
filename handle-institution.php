<?php
require_once('config/database-connect.php');

function geocode_institution($location) {
	$geocode_request_str=str_replace(" ", "+", $location);
	
				$gquery_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$geocode_request_str."&bounds=38,-80|45,-70&sensor=false";
	
	            $ch = curl_init();
                $timeout = 5; // set to zero for no timeout
                curl_setopt ($ch, CURLOPT_URL, $gquery_url);
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $google_response = curl_exec($ch);
                curl_close($ch);
				
		// $google_response=file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".$geocode_request_str."&bounds=38,-80|45,-70&sensor=false");
		
		$google_result = json_decode($google_response, true);
		if($google_result['status'] == "OK") {
			$address = array();
			$address['lat'] = $google_result['results'][0]['geometry']['location']['lat'];
			$address['lon'] = $google_result['results'][0]['geometry']['location']['lng'];
			foreach($google_result['results'][0]['address_components'] as $address_component) {
				foreach($address_component['types'] as $type) {
					$cityfound = 0;
					switch ($type) {
    					case 'street_number':
        					$address['number'] = $address_component['long_name'];
        					break;
    					case 'route':
        					$address['street'] = $address_component['long_name'];
       						break;
    					case 'locality':
        					$address['city'] = $address_component['long_name'];
							$cityfound = 3;
        					break;
						case 'administrative_area_level_3':
							if($cityfound < 3){
        						$address['city'] = $address_component['long_name'];
								$cityfound = 2;
        						break;
							}
						case 'sublocality' :
							if($cityfound < 2){
								$address['city'] = $address_component['long_name'];
								$cityfound = 1;
        						break;
							}
						case 'administrative_area_level_1':
        					$address['state'] = $address_component['short_name'];
        					break;
						case 'postal_code':
        					$address['zip'] = substr($address_component['long_name'], 0, 5);
        					break;
					}
				}
			}
			return $address;
		} else {
			return NULL;
		}
}

function handle_institution($location) {
	$qry = "SELECT id, name, lat, lon FROM institutions WHERE name='$location' OR id='$location'";
	$result = mysql_query($qry);
	if($result && (mysql_num_rows($result) > 0)) {
		$row = mysql_fetch_array($result);
		$inst_lat = $row['lat'];
		$inst_lon = $row['lon'];
		$inst_name = $row['name'];
		$inst_id = $row['id'];
		@mysql_free_result($result);
		if(($inst_lat == 0) || ($inst_lon == 0)) {
			$address = geocode_institution($inst_name);
			if(!empty($address)){
				if(!empty($address['number']) && !empty($address['street'])) {
					$street_address = $address['number']." ".$address['street'];
				}
				$lat = $address['lat'];
				$lon = $address['lon'];
				$city = $address['city'];
				$state = $address['state'];
				$zip = $address['zip'];
				
				if(!empty($street_address) && !empty($zip)){
					$values = "address = '$street_address', city = '$city', state = '$state', zip = '$zip', lat = '$lat', lon = '$lon'";
				} elseif (!empty($street_address)) {
					$values = "address = '$street_address', city = '$city', state = '$state', lat = '$lat', lon = '$lon'";
				} elseif (!empty($zip)) {
					$values = "city = '$city', state = '$state', zip = '$zip', lat = '$lat', lon = '$lon'";
				} else {
					$values = "city = '$city', state = '$state', lat = '$lat', lon = '$lon'";
				}
				
				$qry = "UPDATE institutions SET $values WHERE id = '$inst_id'";
				mysql_query($qry);
				return $inst_id;
			} else {
				return NULL;
			}
		} else {
			return $inst_id;
		}
	} else {
		@mysql_free_result($result);
			$address = geocode_institution($location);
			if(!empty($address) && !is_numeric($location)){
				if(!empty($address['number']) && !empty($address['street'])) {
					$street_address = $address['number']." ".$address['street'];
				}
				$lat = $address['lat'];
				$lon = $address['lon'];
				$city = $address['city'];
				$state = $address['state'];
				$zip = $address['zip'];
				
				if(!empty($street_address) && !empty($zip)){
					$values = "'$location', '$street_address', '$city', '$state', '$zip', '$lat', '$lon'";
				} elseif (!empty($street_address)) {
					$values = "'$location', '$street_address', '$city', '$state', NULL, '$lat', '$lon'";
				} elseif (!empty($zip)) {
					$values = "'$location', NULL, '$city', '$state', '$zip', '$lat', '$lon'";
				} else {
					$values = "'$location', NULL, '$city', '$state', NULL, '$lat', '$lon'";
				}
				$qry = "INSERT INTO institutions (name, address, city, state, zip, lat, lon) VALUES ($values)";
				mysql_query($qry);
				$inst_id = mysql_insert_id();
				return $inst_id;
			} else {
				return NULL;
			}
	}
}

?>