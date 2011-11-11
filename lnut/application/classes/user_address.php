<?php

class user_address extends generic_object {

	private $lib_property_address_uk = null;
	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
		$this->lib_property_address_uk = new lib_property_address_uk();
		$this->lib_property_address_uk->load();
	}

	public function Getmyaddress($data = array()) {
		if ($data['tbl_name'] != '') {
			$query = "SELECT * FROM lib_property_address_uk where uid = '" . @$data['address_id'] . "'";
			return database::arrQuery($query);
		}
	}

	public function FindAddresses() {

		$parts = config::get('paths');

		$result = database::query("SELECT COUNT(AD.uid) FROM lib_property_address_uk AD, lib_country C  WHERE
                    C.uid = country_uid AND (
                    flat_number LIKE '%" . $_POST['keyword'] . "%' OR
                    number LIKE '%" . $_POST['keyword'] . "%' OR
                    name LIKE '%" . $_POST['keyword'] . "%' OR
                    street_name_1  LIKE '%" . $_POST['keyword'] . "%' OR
                    street_name_2  LIKE '%" . $_POST['keyword'] . "%' OR
                    district LIKE '%" . $_POST['keyword'] . "%' OR
                    town LIKE '%" . $_POST['keyword'] . "%' OR
                    city LIKE '%" . $_POST['keyword'] . "%' OR
                    county LIKE '%" . $_POST['keyword'] . "%' OR
                    postcode LIKE '%" . $_POST['keyword'] . "%' OR
                    country_uid IN (SELECT uid FROM lib_country WHERE common_name LIKE '%" . $_POST['keyword'] . "%' )
                    )");
		$max = 0;
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_array($result);
			$max = $row[0];
		}
		$pageId = '';
		if ($pageId == '') {
			$n = count($parts) - 1;
			$n = -1;
			if (isset($parts[$n]) && is_numeric($parts[$n]) && $parts[$n] > 0) {
				$pageId = $parts[$n];
			} else {
				$pageId = 1;
			}
		}
		$this->pager(
				$max, //see above
				config::get("pagesize"), //how many records to display at one time
				$pageId, array("php_self" => "")
		);

		$this->set_range(20);
		//$result = database::query("SELECT SB.*, ".$asField." FROM subscriptions SB ".$where." ORDER BY " . $OrderBy . "  LIMIT ".$this->get_limit());

		$query = "SELECT AD.*,common_name FROM lib_property_address_uk AD, lib_country C  WHERE
                    C.uid = country_uid AND (
                    flat_number LIKE '%" . $_POST['keyword'] . "%' OR
                    number LIKE '%" . $_POST['keyword'] . "%' OR
                    name LIKE '%" . $_POST['keyword'] . "%' OR
                    street_name_1  LIKE '%" . $_POST['keyword'] . "%' OR
                    street_name_2  LIKE '%" . $_POST['keyword'] . "%' OR
                    district LIKE '%" . $_POST['keyword'] . "%' OR
                    town LIKE '%" . $_POST['keyword'] . "%' OR
                    city LIKE '%" . $_POST['keyword'] . "%' OR
                    county LIKE '%" . $_POST['keyword'] . "%' OR
                    postcode LIKE '%" . $_POST['keyword'] . "%' OR
                    country_uid IN (SELECT uid FROM lib_country WHERE common_name LIKE '%" . $_POST['keyword'] . "%' )
                    )
                    LIMIT " . $this->get_limit();


		return database::arrQuery($query);
	}

	public function get_user_address($user_uid = 0, $pageId = '', $all = true) {
		if ($all == false) {
			$query = "   SELECT COUNT(`user_address`.`uid`) FROM `user_address`
                            INNER JOIN `lib_property_address_uk`            ON `lib_property_address_uk`.`uid`                  = `user_address`.`address_uid`
                            LEFT OUTER JOIN `lib_country`                   ON `lib_property_address_uk`.`country_uid`          = `lib_country`.`uid`
                            WHERE `user_address`.`user_uid` = '$user_uid'";

			$this->setPagination($query, $pageId);

			$query = "   SELECT `user_address`.`uid` as `user_address_uid`,`lib_property_address_uk`.*,`lib_country`.`common_name` as `country` FROM `user_address`
                            INNER JOIN `lib_property_address_uk`            ON `lib_property_address_uk`.`uid`                  = `user_address`.`address_uid`
                            LEFT OUTER JOIN `lib_country`                   ON `lib_property_address_uk`.`country_uid`          = `lib_country`.`uid`
                            WHERE `user_address`.`user_uid` = '$user_uid'
                            ORDER BY `lib_property_address_uk`.`street_name_1` LIMIT " . $this->get_limit();
		} else {
			$query = "   SELECT `user_address`.`uid` as `user_address_uid`,`lib_property_address_uk`.*,`lib_country`.`common_name` as `country` FROM `user_address`
                            INNER JOIN `lib_property_address_uk`            ON `lib_property_address_uk`.`uid`                  = `user_address`.`address_uid`
                            LEFT OUTER JOIN `lib_country`                   ON `lib_property_address_uk`.`country_uid`          = `lib_country`.`uid`
                            WHERE `user_address`.`user_uid` = '$user_uid'
                            ORDER BY `lib_property_address_uk`.`street_name_1`";
		}

		return database::arrQuery($query);
	}

	public function get_user_address_with_ids($address_uids = array()) {
		$addresses = array();
		if (!empty($address_uids)) {
			$query = " SELECT `user_address`.`uid` as `user_address_uid`,`lib_property_address_uk`.*,`lib_country`.`common_name` as `country` FROM `user_address`
                        INNER JOIN `lib_property_address_uk`            ON `lib_property_address_uk`.`uid`                    = `user_address`.`address_uid`
                        LEFT OUTER JOIN `lib_country`                   ON `lib_property_address_uk`.`country_uid`            = `lib_country`.`uid`
                        WHERE `user_address`.`uid` IN (" . implode(",", $address_uids) . ")
                        ORDER BY `lib_property_address_uk`.`street_name_1`";
			$addresses = database::arrQuery($query);
		}
		return $addresses;
	}

	public static function get_current_user_address($user_uid = 0) {
		$data = array();
		$query = " SELECT `user_address`.`uid` as `user_address_uid`,`lib_property_address_uk`.*,`lib_country`.`common_name` as `country` FROM `user_address`
                    INNER JOIN `lib_property_address_uk`            ON `lib_property_address_uk`.`uid`                    = `user_address`.`address_uid`
                    LEFT OUTER JOIN `lib_country`                   ON `lib_property_address_uk`.`country_uid`            = `lib_country`.`uid`
                    WHERE `user_address`.`user_uid`                 =   '$user_uid'
                    ORDER BY `user_address`.`uid` DESC LIMIT 1";
		$data = database::arrQuery($query);
		if (empty($data)) {
			$query = " SELECT `user_address`.`uid` as `user_address_uid`,`lib_property_address_uk`.*,`lib_country`.`common_name` as `country` FROM `user_address`
                        INNER JOIN `lib_property_address_uk`            ON `lib_property_address_uk`.`uid`                    = `user_address`.`address_uid`
                        LEFT OUTER JOIN `lib_country`                   ON `lib_property_address_uk`.`country_uid`            = `lib_country`.`uid`
                        WHERE `user_address`.`user_uid`                 =   '$user_uid'
                        ORDER BY `user_address`.`uid` DESC LIMIT 1";
			$data = database::arrQuery($query);
		}
		return $data;
	}

	public function isUpdateSuccessful() {
		$update = false;
		if ($this->lib_property_address_uk->save()) {
			$this->save();
			$update = true;
		}
		return $update;
	}

	public function isCreateSuccessful() {
		$update = false;
		$insert_id = false;

		if (($address_uid = $this->lib_property_address_uk->insert()) !== false) {
			$this->arrFields['address_uid']['Value'] = $address_uid;
			if (($insert_id = $this->insert()) !== false) {
				$update = true;
			}
		}
		return $insert_id;
	}

	public function delete() {
		$lib_property_address_uk = new lib_property_address_uk($this->arrFields['address_uid']['Value']);
		if ($lib_property_address_uk->get_valid()) {
			$lib_property_address_uk->delete();
		}
		parent::delete();
	}

	public function isValidData($update = false, $user_uid = 0) {
		$response = array(
			'fields' => array(
				'uid' => array(
					'default' => 'Address ID',
					'message' => '',
					'highlight' => false,
					'error' => false,
					'value' => ''
				),
				'user_uid' => array(
					'default' => 'User ID',
					'message' => '',
					'highlight' => false,
					'error' => false,
					'value' => ''
				),
				'address_uid' => array(
					'default' => 'Address ID',
					'message' => '',
					'highlight' => false,
					'error' => false,
					'value' => ''
				)
			),
			'message' => ''
		);
		$error = false;
		// validation starts here
		if ($update) {
			if (validation::isPresent('user-address-uid', $_POST)) {
				if (validation::isValid('integer', $_POST['user-address-uid'])) {
					$response['fields']['uid']['value'] = $_POST['user-address-uid'];
					parent::__construct($response['fields']['uid']['value'], __CLASS__);
					$this->load();
				} else {
					$response['fields']['uid']['message'] = 'Please select a valid Contact Number ID';
					$response['fields']['uid']['error'] = true;
					$response['fields']['uid']['highlight'] = true;
				}
			}
		}


		if (($response_address = $this->lib_property_address_uk->isValidData($update, $this->arrFields['address_uid']['Value'])) !== true) {
			$response = array_merge($response, $response_address);
		}
		if (count($response['fields']) > 0) {
			foreach ($response['fields'] as $key => $data) {
				if ($data['error'] == true) {
					$error = true;
					break;
				}
			}
		}
		if (!$error) {
			$this->arrFields['user_uid']['Value'] = $user_uid;
		}
		if (!$error) {
			return true;
		} else {
			return $response;
		}
	}

	public function doSave() {
		$response = true;
		$response = $this->isValidate();
		if (count($response) == 0) {

			if ($_POST['user-address-uid'] > 0)
				$this->lib_property_address_uk->save();
			else {
				$insert = $this->lib_property_address_uk->insert();
				$this->arrForm['user-address-uid'] = $insert;
			}
		} else {
			$msg = NULL;
			foreach ($response as $idx => $val) {
				$this->arrForm[$idx] = 'label_error';
				$msg .= '<li>' . $val . '</li>';
			}
			if ($msg != NULL)
				$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $msg . '</ul>';
		}
		if (count($response) > 0)
			return false;
		else
			return true;
	}

	public function isValidate() {
		if (isset($_POST['user-address-uid']) && is_numeric($_POST['user-address-uid'])) {
			$this->lib_property_address_uk = new lib_property_address_uk($_POST['user-address-uid']);
			$this->lib_property_address_uk->load();
		}
		if (is_numeric($_POST['user-address-uid']) && $_POST['user-address-uid'] > 0) {
			parent::__construct($_POST['user-address-uid'], __CLASS__);
			$this->load();
		}
		$message = array();
		if (strlen(trim($_POST['flat_number'])) > 8) {
			$message['error_flat_number'] = "Flat number should not exceed 8 characters.";
		}
		if (strlen(trim($_POST['number'])) > 8) {
			$message['error_number'] = "Building number should not exceed 8 characters.";
		}
		if (strlen(trim($_POST['name'])) > 32) {
			$message['error_name'] = "Building name should not exceed 32 characters.";
		}
		if (strlen(trim($_POST['street_name_1'])) > 64) {
			$message['error_street_name_1'] = "Street name 1 should not exceed 64 characters.";
		}
		if (strlen(trim($_POST['street_name_2'])) > 64) {
			$message['error_street_name_2'] = "Street name 2 should not exceed 64 characters.";
		}
		if (strlen(trim($_POST['district'])) > 32) {
			$message['error_district'] = "District should not exceed 32 characters.";
		}
		if (strlen(trim($_POST['town'])) > 64) {
			$message['error_town'] = "Town should not exceed 64 characters.";
		}
		if (strlen(trim($_POST['city'])) > 64) {
			$message['error_city'] = "City should not exceed 64 characters.";
		}
		if (strlen(trim($_POST['county'])) > 32) {
			$message['error_county'] = "County should not exceed 32 characters.";
		}
		if (strlen(trim($_POST['postcode'])) > 9) {
			$message['error_postcode'] = "Postcode should not exceed 9 characters.";
		}


		foreach ($_POST as $idx => $val) {
			$this->arrForm[$idx] = $val;
			if (in_array($idx, array('uid', 'submit-address-save', 'user-address-uid', 'form')))
				continue;
			$this->lib_property_address_uk->arrFields[$idx]['Value'] = $val;
		}
		return $message;
	}

	function set_address_id($data = array()) {
		$sql = "update " . $data['tbl_name'] . " SET address_id = '" . $data['address_aid'] . "' where uid = '" . $data['profile_uid'] . "'";
		database::query($sql);
	}

}

?>