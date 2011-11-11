<?php

class component_geo {

	public static function getLatLngIp2Location($ip='') {

		#if(!isset($_SESSION['coordinates']) || count($_SESSION['coordinates']) < 1) {
		$page = file_get_contents('http://www.ip2location.com/' . $ip);

		preg_match('/<span id="dgLookup__ctl2_lblICountry">([^\>]*)<\/span>/Ui', $page, $country);
		preg_match('/<span id="dgLookup__ctl2_lblICity">([^\>]*)<\/span>/Ui', $page, $city);
		preg_match('/<span id="dgLookup__ctl2_lblILatitude">([^\>]*)<\/span>/Ui', $page, $latitude);
		preg_match('/<span id="dgLookup__ctl2_lblILongitude">([^\>]*)<\/span>/Ui', $page, $longitude);
		preg_match('/<span id="dgLookup__ctl2_lblITimeZone">([^\>]*)<\/span>/Ui', $page, $timezone);

		if ($_SERVER['REMOTE_ADDR'] == '77.99.26.25') {
			//echo htmlentities($page);
		}

		$coordinates = array(
			'country' => (isset($country[1]) ? $country[1] : ''),
			'city' => (isset($city[1]) ? $city[1] : ''),
			'latitude' => (isset($latitude[1]) ? $latitude[1] : ''),
			'longitude' => (isset($longitude[1]) ? $longitude[1] : ''),
			'ip' => $ip,
			'zoom' => 3
		);
		geolookup::set($coordinates["country"], $coordinates["city"], $coordinates["latitude"], $coordinates["longitude"], $coordinates["ip"]);

		$_SESSION['coordinates'] = $coordinates;
		#} else {
		#	$coordinates = $_SESSION['coordinates'];
		#}
		// mail('workstation@mystream.co.uk','getLatLngIp2Location',print_r($coordinates,true)."\n\n".$ip."\n\n".$page,'From: andrew@languagenut.com');

		return $coordinates;
	}

	public static function coordinates_by_ip($ip='') {
		if (!isset($_SESSION['coordinates']) || count($_SESSION['coordinates']) < 1) {
			$coordinates = geolookup::get($ip);

			if ($coordinates) {
				$coordinates['zoom'] = 3;
				$country = $coordinates['country'];
				$city = $coordinates['city'];
				$latitude = $coordinates['latitude'];
				$longitude = $coordinates['longitude'];
				$ip = $coordinates['ip'];
				$zoom = $coordinates['zoom'];
				$_SESSION['coordinates'] = $coordinates;
			} else {
				$result = file_get_contents('http://api.hostip.info/get_html.php?ip=' . $ip . '&position=true');


				//mail('workstation@mystream.co.uk','coordinates_by_ip',print_r($coordinates,true)."\n\n".$ip."\n\n".$result,'From: andrew@languagenut.com');
				#
				# http://www.geo-location.com/cgi-bin/index.cgi?s=77.99.26.25
				# http://www.geobytes.com/IpLocator.htm?GetLocation&IpAddress=77.99.26.25
				# http://www.ip2location.com/77.99.26.25
				#
				$raw = $result;
				$parts = explode("\n", $result);
				$realparts = array();
				$country = '';
				$city = '';
				$latitude = '';
				$longitude = '';
				$ip = '';
				$zoom = '3'; // default

				foreach ($parts as $part) {
					if (strlen($part) > 0) {
						$realparts[] = $part;
					}
				}
				foreach ($realparts as $part) {
					$bits = explode('Country: ', $part);
					if (count($bits) == 2) {
						$country = $bits[1];
					} else {
						$bits = explode('City: ', $part);
						if (count($bits) == 2) {
							$city = $bits[1];
						} else {
							$bits = explode('Latitude: ', $part);
							if (count($bits) == 2) {
								$latitude = $bits[1];
							} else {
								$bits = explode('Longitude: ', $part);
								if (count($bits) == 2) {
									$longitude = $bits[1];
								} else {
									$bits = explode('IP: ', $part);
									if (count($bits) == 2) {
										$ip = $bits[1];
									}
								}
							}
						}
					}
				}

				geolookup::set($country, $city, $latitude, $longitude, $ip);

				$_SESSION['coordinates'] = array(
					'country' => $country,
					'city' => $city,
					'latitude' => $latitude,
					'longitude' => $longitude,
					//'latitude'	=> '51.46898',
					//'longitude'	=> '-0.00412',
					'ip' => $ip,
					'zoom' => $zoom
				);
			}
		} else {
			$coordinates = $_SESSION['coordinates'];
			$country = $coordinates['country'];
			$city = $coordinates['city'];
			$latitude = $coordinates['latitude'];
			$longitude = $coordinates['longitude'];
			$ip = $coordinates['ip'];
			$zoom = $coordinates['zoom'];
		}

		return array(
			'country' => $country,
			'city' => $city,
			'latitude' => $latitude,
			'longitude' => $longitude,
			'ip' => $ip,
			'zoom' => $zoom
		);
	}

	public static function getRedirection($country='') {
		if (isset($country) && !empty($country)) {
			
			$explode = explode('(',$country);
			if(count($explode) > 1) {
				$country = trim($explode[0]);
			}
			if($country == '') {
				return false;
			}
			$sql = "SELECT `prefix` FROM ";
			$sql.=" `language`";
			$sql.=" WHERE";
			$sql.=" `lookup_country` LIKE '%".$country."%'";
			$sql.=" AND";
			$sql.="`ip_redirect` = '1'";
			$sql.=" LIMIT 1";
			$data = database::arrQuery($sql);
			return (isset($data[0]["prefix"])) ? $data[0]["prefix"] : false;
		}
		return false;
	}

	/* following method is developed by shailesh on 28/06/2011 */
	public function ip_look_up() {
		$ip_address = $_SERVER['REMOTE_ADDR'];

		$locale = false;

		$arrGEOLookUP = array();
		// intially check in geolookup db and check do we've IP exist
		$arrGEOLookUP = geolookup::get($ip_address);
		if(count($arrGEOLookUP)) {
			// IF IP is exist then get locale by country
			$locale = component_geo::getRedirection($arrGEOLookUP['country']);
		} else {
			// ELSE look ip to location by third party site
			$url = 'http://api.ipinfodb.com/v3/ip-city/?';
			$url.= 'key=8a152ca422fb9dcf5e39a6edd9a5bd504b6944119525a789b089826d245745ce';
			$url.= '&ip='.$ip_address;
			$url.= '&format=xml';
			$response = simplexml_load_string(file_get_contents($url));
			// add new ip details to geolookup db
			geolookup::set(
				$response->countryName.'('.$response->countryCode.')',
				$response->cityName,
				$response->latitude,
				$response->longitude,
				$ip_address
			);
			$locale = component_geo::getRedirection($response->countryName.'('.$response->countryCode.')');
		}
		return $locale;
	}

}

?>