<?php

class language extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function exists($language_uid=null) {
		$sql = "SELECT ";
		$sql.="`uid` ";
		$sql.="FROM ";
		$sql.="`language` ";
		$sql.="WHERE ";
		$sql.="`uid`='" . $language_uid . "' ";
		$sql.="LIMIT 1";
		$res = database::query($sql);
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getLanguageComboBox($listbox_name = 'locale_rights[]', $selected = array()) {
		$html = '<select size="10" options_only="" multiple="multiple" id="locale_rights" name="' . $listbox_name . '">';

		$query = "SELECT ";
		$query .= "`name` ";
		$query .= ", `prefix` ";
		$query .= "FROM ";
		$query .= "`language` ";
		$query .= "WHERE ";
		$query .= "`available` = '1' ";
		$query .= "AND ";
		$query .= "`active` = '1' ";
		$query .= "ORDER BY `name` ";

		$result = database::query($query);
		if (mysql_error() == '' && mysql_num_rows($result)) {
			while ($row = mysql_fetch_array($result)) {
				$html .='<option ';
				$html .='value="' . $row['prefix'] . '"';
				if (count($selected) && in_array($row['prefix'], $selected)) {
					$html .=' selected="selected" ';
				}
				$html .='>';
				$html .=$row['name'];
				$html .='</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}

	public function LanguageSelectBox($inputName="locale", $selctedValue = NULL,$skip_language_uid=null) {
		$sql = "SELECT ";
		$sql.= "`uid`, ";
		$sql.= "`name`, ";
		$sql.= "`prefix` ";
		$sql.= "FROM ";
		$sql.= "`language` ";
		if (isset($_SESSION['user']['localeRights'])) {
			$sql.= "WHERE `prefix` IN( " . $_SESSION['user']['localeRights'] . ") ";
		}
		$sql.= "ORDER BY ";
		$sql.= "`name` ASC";
		$res = database::query($sql);
		$data = array();
		$data[0] = 'Language';
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			while ($row = mysql_fetch_assoc($res)) {
				if($skip_language_uid==$row['uid']) {
					continue;
				}
				$data[$row['uid']] = $row['name'] . ' ( ' . $row['prefix'] .' )';
			}
		}
		return format::to_select(
				array(
			"name" => $inputName,
			"id" => $inputName,
			"style" => "width:180px;",
			"options_only" => false
				), $data, $selctedValue
		);
	}

	public function LanguageSelectBoxNamedLanguages($inputName="locale", $selectedValue = NULL,$skip_language_uid=null) {
		$sql = "SELECT ";
		$sql.= "`uid`, ";
		$sql.= "`name`, ";
		$sql.= "`prefix` ";
		$sql.= "FROM ";
		$sql.= "`language` ";
		if (isset($_SESSION['user']['localeRights'])) {
			$sql.= "WHERE `prefix` IN( " . $_SESSION['user']['localeRights'] . ") ";
		}
		$sql.= "ORDER BY ";
		$sql.= "`name` ASC";
		$res = database::query($sql);
		$data = array();
		$data[0] = 'Language';
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			while ($row = mysql_fetch_assoc($res)) {
				if($skip_language_uid==$row['uid']) {
					continue;
				}
				$data[$row['uid']] = $row['name'] . ' ( ' . $row['prefix'] .' )';
			}
		}
		return format::to_select(
			array(
				"name"			=> $inputName,
				"id"			=> $inputName,
				"style"			=> "width:180px;",
				"options_only"	=> false
			),
			$data,
			$selectedValue
		);
	}

	public function LocaleSelectBoxBasedOnAccessRight($inputName='locale', $selctedValue = NULL) {
		$locale = '';
		if (isset($_SESSION['user']['localeRights'])) {
			$locale = $_SESSION['user']['localeRights'];
		}
		$sql = "SELECT ";
		$sql.= "`prefix` ";
		$sql.= "FROM ";
		$sql.= "`language` ";
		$sql.= "WHERE ";
		$sql.= "`prefix` IN (" . $locale . ")";
		$sql.= "ORDER BY ";
		$sql.= "`prefix` ASC";
		$res = database::query($sql);
		$data = array();
		$data[''] = 'Locale';
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			while ($row = mysql_fetch_assoc($res)) {
				$data[$row['prefix']] = $row['prefix'];
			}
		}
		return format::to_select(
				array(
			"name" => $inputName,
			"id" => $inputName,
			"options_only" => false
				), $data, $selctedValue
		);
	}

	public function LocaleSelectBox($inputName='locale', $selctedValue = NULL) {
		$sql = "SELECT ";
		$sql.= "`prefix` ";
		$sql.= "FROM ";
		$sql.= "`language` ";
		if (isset($_SESSION['user']['localeRights'])) {
			$sql.= "WHERE `prefix` IN( " . $_SESSION['user']['localeRights'] . ") ";
		}
		$sql.= "ORDER BY ";
		$sql.= "`prefix` ASC";
		$res = database::query($sql);
		$data = array();
		$data[''] = 'Locale';
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			while ($row = mysql_fetch_assoc($res)) {
				$data[$row['prefix']] = $row['prefix'];
			}
		}
		return format::to_select(
				array(
			"name" => $inputName,
			"id" => $inputName,
			"options_only" => false
				), $data, $selctedValue
		);
	}

	public function getList($data = array(), $OrderBy = "name ", $all = false) {
		$where = ' WHERE 1 = 1';
		foreach ($data as $idx => $val) {
			$where .= " AND " . $idx . "='" . $val . "'";
		}
		if (!$all) {
			$query = "SELECT ";
			$query.="COUNT(`uid`) ";
			$query.="FROM ";
			$query.="`language` ";
			$query.=$where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`language` ";
		$query.=$where . " ";
		$query.=" ORDER BY " . $OrderBy;
		if ($all == false) {
			$query.= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function doSave() {
		if ($this->isValidateFormData() == true) {
			if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;
			}
			return true;
		} else {
			return false;
		}
	}

	public function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$name = (isset($_POST['name'])) ? trim($_POST['name']) : '';
		$prefix = (isset($_POST['prefix'])) ? trim($_POST['prefix']) : '';
		$available = (isset($_POST['available'])) ? $_POST['available'] : '0';
		$active = (isset($_POST['active'])) ? $_POST['active'] : 0;
		$is_learnable = (isset($_POST['is_learnable'])) ? $_POST['is_learnable'] : 0;
		$is_support = (isset($_POST['is_support'])) ? $_POST['is_support'] : 0;
		$currency_uid = (isset($_POST['currency_uid'])) ? $_POST['currency_uid'] : 0;
		$home_user_price = (isset($_POST['home_user_price'])) ? $_POST['home_user_price'] : 0;
		$school_price = (isset($_POST['school_price'])) ? $_POST['school_price'] : 0;
		$vat = (isset($_POST['vat'])) ? $_POST['vat'] : 0;
		$ip_redirect = (isset($_POST['ip_redirect']) && ($_POST['ip_redirect'] == '0' || $_POST['ip_redirect'] == '1')) ? $_POST['ip_redirect'] : 1;
		$lookup_country = (isset($_POST['lookup_country'])) ? trim($_POST['lookup_country']) : '';

		$arrMessages = array();
		if (strlen($name) < 3 || strlen($name) > 255) {
			$arrMessages['error_name'] = "Language name must be 3 to 255 characters in length.";
		} else if (!validation::isValid('text', $name)) {
			$arrMessages['error_name'] = "Please enter valid language name.";
		}
		if (strlen($prefix) < 2 || strlen($prefix) > 3) {
			$arrMessages['error_prefix'] = "Language prefix must be 2 to 3 characters in length.";
		} else if (!validation::isValid('text', $prefix)) {
			$arrMessages['error_prefix'] = "Please enter valid language prefix.";
		}
		if (!validation::isValid('int', $active)) {
			$arrMessages['error_active'] = "Please choose language active option.";
		}
		if (!validation::isValid('int', $available)) {
			$arrMessages['error_unit_uid'] = "Please choose language available option.";
		}
		if (!validation::isValid('int', $is_learnable)) {
			$arrMessages['error_is_learnable'] = "Please choose language is learnable or not.";
		}
		if (!validation::isValid('int', $is_support)) {
			$arrMessages['error_is_support'] = "Please choose language for support or not.";
		}
		if (!validation::isValid('int', $currency_uid) || $currency_uid<=0) {
			$arrMessages['error_currency_uid'] = "Please select valid currency.";
		}
		if (!validation::isValid('double', $home_user_price) || $home_user_price<0 ) {
			$arrMessages['error_home_user_price'] = "Please enter valid home user price.";
		}
		if (!validation::isValid('double', $school_price) || $school_price<0 ) {
			$arrMessages['error_school_price'] = "Please enter valid school price.";
		}
		if (!validation::isValid('double', $vat) || $vat<0 ) {
			$arrMessages['error_vat'] = "Please enter valid vat.";
		}
		if (count($arrMessages) == 0) {
			$this->set_name($name);
			$this->set_prefix($prefix);
			$this->set_active($active);
			$this->set_available($available);
			$this->set_is_learnable($is_learnable);
			$this->set_is_support($is_support);
			$this->set_currency_uid($currency_uid);
			$this->set_home_user_price($home_user_price);
			$this->set_school_price($school_price);
			$this->set_vat($vat);
			$this->set_directory($prefix);
			$this->set_logo_url('URLcom.png');
			$this->set_gold_bg('bg_GOLD.png');
			$this->set_silver_bg('bg_SILVER.png');
			$this->set_bronze_bg('bg_BRONZE.png');
			//$this->set_runtime($prefix);
			//$this->set_audiodirectory($prefix);
			$this->set_ip_redirect($ip_redirect);
			$this->set_lookup_country($lookup_country);
		} else {
			$strMessage = '';
			foreach ($arrMessages as $index => $value) {
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>' . $value . '</li>';
			}
			$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $strMessage . '</ul>';
		}
		foreach ($_POST as $index => $value) {
			$this->arrForm[$index] = $value;
		}
		if (count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}
	}

	public function doSaveImages() {
		$response = true;
		$response = $this->isValidImages();
		if (count($response) == 0) {
			if ($_POST['uid'] > 0) {
				$this->save();
			} else {
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;
			}
		} else {
			$msg = NULL;
			foreach ($response as $idx => $val) {
				$this->arrForm[$idx] = 'label_error';
				$msg .= '<li>' . $val . '</li>';
			}
			if ($msg != NULL) {
				$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $msg . '</ul>';
			}
		}
		if (count($response) > 0) {
			return false;
		} else {
			return true;
		}
	}

	public function isValidImages() {
		$ImgPath = config::get('site') . '/images/certificate/';
		if (is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$message = array();
		if (isset($_FILES['logo_url']) && trim($_FILES['logo_url']['name']) == '' && trim($_POST['logo_url_hidden']) == '') {
			$message['error_logo_url'] = "Please upload logo image.";
		}
		if (isset($_FILES['gold_bg']) && trim($_FILES['gold_bg']['name']) == '' && trim($_POST['gold_bg_hidden']) == '') {
			$message['error_gold_bg'] = "Please upload gold background image.";
		}
		if (isset($_FILES['silver_bg']) && trim($_FILES['silver_bg']['name']) == '' && trim($_POST['silver_bg_hidden']) == '') {
			$message['error_silver_bg'] = "Please upload silver background image.";
		}
		if (isset($_FILES['bronze_bg']) && trim($_FILES['bronze_bg']['name']) == '' && trim($_POST['bronze_bg_hidden']) == '') {
			$message['error_bronze_bg'] = "Please upload bronze background image.";
		}

		$allow_types = array(
			'image/jpeg',
			'image/png',
			'image/gif',
			'image/pjpeg'
		);

		if (trim($_FILES['bronze_bg']['name']) != '') {
			$image = '';
			$image_name = '';
			if (in_array($_FILES['bronze_bg']['type'], $allow_types) == false) {
				$message['error_bronze_bg'] = "Please upload valid bronze background image.";
			} else {
				$image_name = '';
				$image_name = explode('.', $_FILES['bronze_bg']['name']);
				$image = 'bronze_bg_' . time() . '.' . $image_name[count($image_name) - 1];
				move_uploaded_file($_FILES['bronze_bg']['tmp_name'], $ImgPath . $image);
				if (!empty($_POST['bronze_bg_hidden']) && $_POST['bronze_bg_hidden'] != 'bg_BRONZE.png') {
					unlink($ImgPath . $_POST['bronze_bg_hidden']);
				}
				$this->arrFields['bronze_bg']['Value'] = $image;
			}
		}
		if (trim($_FILES['silver_bg']['name']) != '') {
			$image = '';
			$image_name = '';
			if (in_array($_FILES['silver_bg']['type'], $allow_types) == false) {
				$message['error_silver_bg'] = "Please upload valid silver background image.";
			} else {
				$image_name = '';
				$image_name = explode('.', $_FILES['silver_bg']['name']);
				$image = 'silver_bg_' . time() . '.' . $image_name[count($image_name) - 1];
				move_uploaded_file($_FILES['silver_bg']['tmp_name'], $ImgPath . $image);
				if (!empty($_POST['silver_bg_hidden']) && $_POST['silver_bg_hidden'] != 'bg_SILVER.png') {
					unlink($ImgPath . $_POST['silver_bg_hidden']);
				}
				$this->arrFields['silver_bg']['Value'] = $image;
			}
		}
		if (trim($_FILES['gold_bg']['name']) != '') {
			$image = '';
			$image_name = '';
			if (in_array($_FILES['gold_bg']['type'], $allow_types) == false) {
				$message['error_gold_bg'] = "Please upload valid gold background image.";
			} else {
				$image_name = '';
				$image_name = explode('.', $_FILES['gold_bg']['name']);
				$image = 'gold_bg_' . time() . '.' . $image_name[count($image_name) - 1];
				move_uploaded_file($_FILES['gold_bg']['tmp_name'], $ImgPath . $image);
				if (!empty($_POST['gold_bg_hidden']) && @$_POST['gold_bg_hidden'] != 'bg_GOLD.png') {
					unlink($ImgPath . $_POST['gold_bg_hidden']);
				}
				$this->arrFields['gold_bg']['Value'] = $image;
			}
		}
		if (trim($_FILES['logo_url']['name']) != '') {
			$image = '';
			$image_name = '';
			if (in_array($_FILES['logo_url']['type'], $allow_types) == false) {
				$message['error_logo_url'] = "Please upload valid logo image.";
			} else {
				$image_name = '';
				$image_name = explode('.', $_FILES['logo_url']['name']);
				$image = 'logo_' . time() . '.' . $image_name[count($image_name) - 1];
				move_uploaded_file($_FILES['logo_url']['tmp_name'], $ImgPath . $image);
				if (!empty($_POST['logo_url_hidden']) && @$_POST['logo_url_hidden'] != 'URLcom.png') {
					unlink($ImgPath . $_POST['logo_url_hidden']);
				}
				$this->arrFields['logo_url']['Value'] = $image;
			}
		}
		return $message;
	}

	public function getLanguageArray() {
		$arrResponse = array();
		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`name`, ";
		$query.= "`prefix`, ";
		$query.= "`directory`, ";
		$query.= "`active`, ";
		$query.= "`available`, ";
		$query.= "`is_learnable`, ";
		$query.= "`is_support`, ";
		$query.= "`position`, ";
		$query.= "`currency_uid`, ";
		$query.= "`home_user_price`, ";
		$query.= "`school_price` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "ORDER BY ";
		$query.= "`name` ASC";
		$result = database::query($query);
		if ($result) {
			if (mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$arrResponse[$row['uid']] = stripslashes($row['name']);
				}
			}
		}
		return $arrResponse;
	}

	public function getLanguagesList($support_language_uid = 14) {
		$arrResponse = array();
		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`name`, ";
		$query.= "`directory`, ";
		$query.= "`available`, ";
		$query.= "`runtime`, ";
		$query.= "`audiodirectory`, ";
		$query.= "`is_learnable`, ";
		$query.= "`is_support`, ";
		$query.= "( ";
		$query.= "SELECT ";
		$query.= "`name` ";
		$query.= "FROM ";
		$query.= "`language_translation` ";
		$query.= "WHERE ";
		$query.= "`language_uid` = `language`.`uid` ";
		$query.= "AND ";
		$query.= "`language_translation_id` = '" . $support_language_uid . "' ";
		$query.= "LIMIT 1";
		#$query.= "`language_translation_id`=`language`.`uid` ";
		#$query.= "AND ";
		#$query.= "`language_uid`='".$support_language_uid."' ";
		#$query.= "LIMIT 1";
		$query.= ") ";
		$query.= "as `Lname`";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "ORDER BY ";
		$query.= "`name` ASC";
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				if (!empty($row['Lname']) && $row['Lname'] != NULL) {
					$row['name'] = $row['Lname'];
				}
				$arrResponse[$row['uid']] = array(
					'name' => ($row['Lname'] != '' && !is_null($row['Lname'])) ? stripslashes($row['Lname']) : stripslashes($row['name']),
					'directory' => stripslashes($row['directory']),
					'available' => $row['available'],
					'runtime' => stripslashes($row['runtime']),
					'audiodirectory' => stripslashes($row['audiodirectory']),
					'is_learnable' => ($row['is_learnable'] == 1) ? true : false,
					'is_support' => ($row['is_support'] == 1) ? true : false
				);
			}
		}
		return $arrResponse;
	}

	public function getLanguages() {
		$languages = array();
		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`name`, ";
		$query.= "`prefix`, ";
		$query.= "`directory`, ";
		$query.= "`active`, ";
		$query.= "`available`, ";
		$query.= "`is_learnable`, ";
		$query.= "`is_support`, ";
		$query.= "`position`, ";
		$query.= "`currency_uid`, ";
		$query.= "`home_user_price`, ";
		$query.= "`school_price` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "ORDER BY ";
		$query.= "`name` ASC";
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$languages[$row['uid']] = $row;
			}
		}
		return $languages;
	}

	public function getPrefix($language_id='') {
		$locale = 'en';
		$query = "SELECT ";
		$query.= "`prefix` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "WHERE ";
		$query.= "`uid`='" . mysql_real_escape_string($language_id) . "' ";
		$query.= "LIMIT 1";
		$result = database::query($query);
		if ($result) {
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$locale = $row['prefix'];
			}
		}
		return $locale;
	}

	public function CheckLocale($locale, $check_available = true) {
		$query = "SELECT ";
		$query.= "`uid` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "WHERE ";
		$query.= "`prefix`='" . mysql_real_escape_string($locale) . "' ";
		if ($check_available) {
			$query.= " AND `available`='1'";
		}
		$query.= "LIMIT 1";

		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_array($result);
			return $row['uid'];
		}
		return false;
	}

	public function doSavePricing() {
		if ($this->isValidatePriceData() === true) {
			$this->save();
			return true;
		} else {
			return false;
		}
	}

	public function isValidatePriceData() {
		if (is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$currency_uid = isset($_POST['currency_uid']) ? $_POST['currency_uid'] : 0;
		$home_user_price = isset($_POST['home_user_price']) ? $_POST['home_user_price'] : '';
		$school_price = isset($_POST['school_price']) ? $_POST['school_price'] : '';
		$vat = isset($_POST['vat']) ? $_POST['vat'] : '';

		$arrMessages = array();
		if ($currency_uid == "" || $currency_uid == "0") {
			$arrMessages['error.currency_uid'] = "Please choose currency.";
		} else if (!validation::isValid('int', $currency_uid)) {
			$arrMessages['error.currency_uid'] = "Please choose valid currency.";
		}
		if ($home_user_price == "" || $home_user_price == "0") {
			$arrMessages['error.home_user_price'] = "Please enter homeuser price.";
		} else if (!validation::isValid('int', $home_user_price)) {
			$arrMessages['error.home_user_price'] = "Please choose valid homeuser price.";
		}
		if ($school_price == "" || $school_price == "0") {
			$arrMessages['error.school_price'] = "Please enter school price.";
		} else if (!validation::isValid('int', $school_price)) {
			$arrMessages['error.school_price'] = "Please choose valid school price.";
		}
		if ($vat == "" || $vat == "0") {
			$arrMessages['error.vat'] = "Please enter VAT%.";
		} else if (!validation::isValid('int', $vat)) {
			$arrMessages['error.vat'] = "Please choose valid VAT%.";
		}
		if (count($arrMessages) == 0) {
			$this->set_currency_uid($currency_uid);
			$this->set_home_user_price($home_user_price);
			$this->set_school_price($school_price);
			$this->set_vat($vat);
		} else {
			$strMessage = '';
			foreach ($arrMessages as $index => $value) {
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>' . $value . '</li>';
			}
			$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $strMessage . '</ul>';
		}
		foreach ($_POST as $index => $value) {
			$this->arrForm[$index] = $value;
		}
		if (count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}
	}

	public static function getPrefixes() {
		$response = array();
		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`name`, ";
		$query.= "`prefix` ";
		$query.= "FROM ";
		$query.= "`language` ";
		if (isset($_SESSION['user']['localeRights'])) {
			$query.= "WHERE `prefix` IN( " . $_SESSION['user']['localeRights'] . ") ";
		}
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$response[$row['uid']] = array(
					'name' => stripslashes($row['name']),
					'prefix' => stripslashes($row['prefix'])
				);
			}
		}
		return $response;
	}

	public static function getPrefixesByLocale($wherein="") {
		$response = array();
		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`name`, ";
		$query.= "`prefix` ";
		$query.= "FROM ";
		$query.= "`language` ";
		if (!empty($wherein)) {
			$query.= "WHERE `prefix` IN( " . $wherein . ") ";
		}
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";
		$result = database::arrQuery($query);

		return $result;
	}

	public function GetUnsuedSectionVocabLanguagesListBox($inputName, $selctedValue = NULL) {
		$sql = "SELECT ";
		$sql.= "`LG`.`uid`, ";
		$sql.= "`LG`.`name` ";
		$sql.= "FROM ";
		$sql.= "`language` as `LG` ";
		$sql.= "WHERE ";
		$sql.= "`LG`.`uid` NOT IN ";
		$sql.= "( ";
		$sql.="SELECT ";
		$sql.="DISTINCT `language_id` ";
		$sql.= "FROM ";
		$sql.= "`sections_vocabulary_translations` ";
		$sql.= " ) ";
		$sql.= "ORDER BY ";
		$sql.= "`LG`.`name` ASC";

		$res = database::query($sql);
		$data = array();
		$data[0] = 'Language';
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			while ($row = mysql_fetch_assoc($res)) {
				$data[$row['uid']] = $row['name'];
			}
		}
		return format::to_select(
				array(
			"name" => $inputName,
			"id" => $inputName,
			"style" => "width:180px;",
			"options_only" => false
				), $data, $selctedValue
		);
	}

	public static function getUidFromPrefix($prefix="en") {
		$sql = "SELECT uid FROM `language` WHERE prefix='{$prefix}' LIMIT 1";
		$data = database::arrQuery($sql);
		return (isset($data[0]["uid"])) ? $data[0]["uid"] : false;
	}

	public function getAllAvailableLanguages() {
		$query = "SELECT ";
		$query.="`name`, ";
		$query.="`uid`, ";
		$query.="`prefix` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="WHERE ";
		$query.="`is_learnable` = 1 ";
		$query.="ORDER BY `name` ";
		return database::arrQuery($query);
	}

	public function getAllSupportLanguages() {
		$query = "SELECT ";
		$query.="`name`, ";
		$query.="`uid`, ";
		$query.="`prefix` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="WHERE ";
		$query.="`is_support` = 1 ";
		$query.="ORDER BY `name` ";
		return database::arrQuery($query);
	}

	public function getFilteredLanguages($arrFilter=array(0)) {
		$query = "SELECT ";
		$query.="`name`, ";
		$query.="`uid` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="WHERE ";
		$query.="`uid` IN (".implode(',',$arrFilter).") ";
		$query.=" ORDER BY `name`";
		return database::arrQuery($query);
	}

	public function CopyTranslation($from_uid=14,$to_uid=null,$copyDirs=true) {
		// FOLLOWING CODITION WILL RETURN FALSE IF SOMEONE ATTEMPT TO UPDATE EN LOCALE
		if($to_uid == 14 || $to_uid=='en') {
			return false;
		}

		$arrTables = array(
			array(
				'tableName'			=>'activity_skill_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'activity_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'certificate_messages_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'difficulty_level_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'email_templates_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'difficulty_level_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'exercise_qae_topic_content_question_option_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'exercise_qae_topic_content_question_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'exercise_qae_topic_content_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'exercise_qae_topic_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'exercise_type_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'flash_translations_locales',
				'fieldName'			=>'support_language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'game_translation',
				'fieldName'			=>'language_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'language_translation',
				'fieldName'			=>'language_translation_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_children_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_contact_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_culture_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_games_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_songs_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_teachers_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_welcome_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_messages_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_privacy_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_homeuser_stage_1_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_homeuser_stage_2_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_homeuser_stage_3_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_homeuser_stage_4_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_school_stage_1_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_school_stage_2_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_school_stage_3_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_school_stage_4_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_select_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_terms_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_widget_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'qae_topic_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'reference_material_type_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'school_registration_templates_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'sections_translations',
				'fieldName'			=>'language_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'sections_vocabulary_translations',
				'fieldName'			=>'language_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'send_application_translation',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'speaking_and_listening_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'units_translations',
				'fieldName'			=>'language_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'years_translations',
				'fieldName'			=>'language_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			)
		);
		$from_locale	=null;
		$to_locale		=null;
		if(is_numeric($from_uid) && $from_uid > 0) {
				$from_locale = $this->getPrefix($from_uid);
		}
		if(is_numeric($to_uid) && $to_uid > 0) {
				$to_locale = $this->getPrefix($to_uid);
		}
		if($from_uid==$to_uid || $from_locale==$to_locale) {
			return false;
		}

		// FOLLOWING CODITION WILL RETURN FALSE IF SOMEONE ATTEMPT TO UPDATE EN LOCALE
		if($to_uid == 14 || $to_locale=='en') {
			return false;
		}

		$copyFrom	=null;
		$copyTo		=null;

		foreach($arrTables as $arrTable) {
			if($arrTable['fieldType']=='int') {
				$copyFrom	=$from_uid;
				$copyTo		=$to_uid;
			} else {
				$copyFrom	=$from_locale;
				$copyTo		=$to_locale;
			}
			if(trim($arrTable['optionalFieldName'])=='') {
				$this->copySiteContentTranslation(
					$arrTable['tableName'],
					$arrTable['fieldName'],
					$copyFrom,
					$copyTo
				);
			} else {
				$this->copySiteContentTranslation(
					$arrTable['tableName'],
					$arrTable['fieldName'],
					$copyFrom,
					$copyTo,
					trim($arrTable['optionalFieldName']),
					$to_locale
				);
			}
		}
		if($copyDirs) {
			$this->RecursiveCopy(
				config::get('site').'images/'.$from_locale,
				config::get('site').'images',
				$to_locale
			);

			$this->RecursiveCopy(
				config::get('site').'styles/'.$from_locale,
				config::get('site').'styles',
				$to_locale
			);
		}

	}

	public function copySiteContentTranslation($txtTable=null, $txtLocaleFieldName=null, $copyFrom=null, $copyTo=null,$optionalFieldName=null, $optionalFieldValue=null) {

		if($txtTable==null || $txtLocaleFieldName==null || $copyFrom==null || $copyTo==null) {
			// required datas are missing
			return false;
		}

		// FOLLOWING CODITION WILL RETURN FALSE IF SOMEONE ATTEMPT TO UPDATE EN LOCALE
		if($copyTo == 14 || $copyTo=='en') {
			return false;
		}

		$txtTable			=trim($txtTable);
		$txtLocaleFieldName	=trim($txtLocaleFieldName);
		$copyFrom			=trim($copyFrom);
		$copyTo				=trim($copyTo);
		$arrInsertFields	=array();
		$arrValueFields		=array();

		//if($copyBy)
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`".$txtTable."` ";
		$query.="WHERE ";
		$query.="`".$txtLocaleFieldName."`='".$copyFrom."' ";
		$query.="LIMIT 0,1 ";
		$result = database::query($query);
		if( mysql_error()=='' && mysql_num_rows($result) && $result) {
			$arrFields = mysql_fetch_array($result);
			foreach($arrFields as $index => $value) {
				if($index == 'uid' || is_numeric($index)) {
					continue;
				}
				$arrInsertFields[]="`".$index."`";

				if($index == $txtLocaleFieldName) {
					$arrValueFields[]="'".$copyTo."'";
				} else if($optionalFieldName!=null && $optionalFieldValue!=null && $index == $optionalFieldName) {
					$arrValueFields[]="'".$optionalFieldValue."'";
				} else {
					$arrValueFields[]="`".$index."`";
				}
			}
		}

		/*
		* FIRST DELETE ALL EXIST ENTRY FOR GIVEN TO ID OT LOCALE
		*/

		$query ="DELETE ";
		$query.="FROM ";
		$query.="`".$txtTable."` ";
		$query.="WHERE ";
		$query.="`".$txtLocaleFieldName."`='".$copyTo."' ";
		$result = database::query($query);

		/*
		* NOW COPY TRANLATION FROM GIVEN $copyFrom TO $copyTo
		*/
		$query ="INSERT ";
		$query.="INTO ";
		$query.="`".$txtTable."` ";
		$query.="(".implode(',',$arrInsertFields).") ";
		$query.="SELECT ";
		$query.="".implode(',',$arrValueFields)." ";
		$query.="FROM ";
		$query.="`".$txtTable."` ";
		$query.="WHERE ";
		$query.="`".$txtLocaleFieldName."`='".$copyFrom."' ";
		$result = database::query($query);
	}

	private function RecursiveCopy($source, $dest, $diffDir = ''){
		$sourceHandle = opendir($source);
		if($diffDir=='') {
			$diffDir = $source;
		}

		mkdir($dest . '/' . $diffDir);

		while($res = readdir($sourceHandle)){
			if($res == '.' || $res == '..')
				continue;
			if(is_dir($source . '/' . $res)){
				$this->RecursiveCopy(
					$source . '/' . $res,
					$dest,
					$diffDir . '/' . $res
				);
			} else {
				if(!is_dir($dest . '/' . $diffDir)) {
					mkdir($dest . '/' . $diffDir);
				}
				if(!is_file($dest . '/' . $diffDir . '/' . $res)) {
					copy(
						$source . '/' . $res,
						$dest . '/' . $diffDir . '/' . $res
					);
				}
			}
		}
	}


}

?>