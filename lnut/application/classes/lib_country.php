<?php

class lib_country extends generic_object {

public function __construct($uid = 0) {
	parent::__construct($uid, __CLASS__);
}

public function get_country($pageId = '',$all = false) {
	if($all == false) {
		$result = database::query('SELECT COUNT(`lib_country`.`uid`) FROM `lib_country`');
		$max = 0;
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_array($result);
			$max = $row[0];
		}
		if($pageId=='') {
			$parts = config::get('paths');
			if(isset($parts[3]) && is_numeric($parts[3]) && $parts[3] > 0) {
				$pageId = $parts[3];
			} else {
				$pageId = 1;
			}
		}
		$this->pager(
				$max,						//see above
				config::get("pagesize"),	//how many records to display at one time
				$pageId,
				array("php_self" => "")
		);
		$this->set_range(10);


		$query      =  "SELECT `lib_country`.* FROM `lib_country` ORDER BY `common_name` LIMIT ".$this->get_limit();
	}
	else {
		$query      =  "SELECT `lib_country`.* FROM `lib_country` ORDER BY `common_name`";
	}
	
	return database::arrQuery($query);
}

public static function country_exists($country = "",$uid = 0) {
	$found  =   false;
	$type   =   mysql_real_escape_string($type);
	$sql    =   "SELECT * FROM `lib_country` WHERE `common_name` = '$country'";
	if(is_numeric($uid) && $uid > 0) {
		$sql .= " AND `uid` != '$uid'";
	}
	$sql    .=  " LIMIT 1";
	$result =   database::query($sql);
	if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
		$found  =   true;
	}
	return $found;
}

public function isCreateSuccessFul() {
	$response   =   array();
	$response   =   $this->isValidData();
	if(!empty ($response)) {
		if($response[0] == false) {
			$insert_id      = $this->insert();
			$response[1][]  = "Country Added Successfully";
		}
	}
	$message_type           =   ($response[0] == true)?"error":"success";
	component_response::htmlSuccessFailureResponse($insert_id,$message_type,$response[1]);
}

public function isUpdateSuccessFul() {
	$response   =   array();
	$response   =   $this->isValidData(true);
	if(!empty ($response)) {
		if($response[0] == false) {
			$this->save();
			$response[1][]  = "Country Updated Successfully";
		}
	}
	$message_type           =   ($response[0] == true)?"error":"success";
	component_response::htmlSuccessFailureResponse($insert_id,$message_type,$response[1]);
}

public function isValidData($update = false) {
	$country_uid                =   (isset($_POST['country_uid'])               ? format::to_integer($_POST['country_uid']) : '0');
	$common_name                =   (isset($_POST['common_name'])               ? format::to_string($_POST['common_name']) : '');
	$formal_name                =   (isset($_POST['formal_name'])               ? format::to_string($_POST['formal_name']) : '');
	$type_uid                   =   (isset($_POST['type_uid'])                  ? format::to_integer($_POST['type_uid']) : '0');
	$sub_type_uid               =   (isset($_POST['sub_type_uid'])              ? format::to_integer($_POST['sub_type_uid']) : '0');
	$sovereignty                =   (isset($_POST['sovereignty'])               ? format::to_string($_POST['sovereignty']) : '');
	$capital                    =   (isset($_POST['capital'])                   ? format::to_string($_POST['capital']) : '');
	$iso_4217_currency_code     =   (isset($_POST['iso_4217_currency_code'])    ? format::to_string($_POST['iso_4217_currency_code']) : '');
	$iso_4217_currency_name     =   (isset($_POST['iso_4217_currency_name'])    ? format::to_string($_POST['iso_4217_currency_name']) : '');
	$itu_t_telephone_code       =   (isset($_POST['itu_t_telephone_code'])      ? format::to_string($_POST['itu_t_telephone_code']) : '');
	$iso_3166_1_2_letter_code   =   (isset($_POST['iso_3166_1_2_letter_code'])  ? format::to_string($_POST['iso_3166_1_2_letter_code']) : '');
	$iso_3166_1_3_letter_code   =   (isset($_POST['iso_3166_1_3_letter_code'])  ? format::to_string($_POST['iso_3166_1_3_letter_code']) : '');
	$iso_3166_1_number          =   (isset($_POST['iso_3166_1_number'])         ? format::to_string($_POST['iso_3166_1_number']) : '');
	$iana_country_code_tld      =   (isset($_POST['iana_country_code_tld'])     ? format::to_string($_POST['iana_country_code_tld']) : '');
	$is_active                  =   (isset($_POST['is_active'])                 ? format::to_integer($_POST['is_active']) : 0);

	$error                      =   false;
	$message                    =   array();

	if(is_numeric($country_uid) && $country_uid > 0) {
		parent::__construct($country_uid);
		$this->load();
	}

	if(strlen($common_name) <= 0 || strlen($common_name) > 255) {
		$error          =   true;
		$message[]      =   "Please Provide Valid Name";
	}
	else if(self::country_exists($common_name, $country_uid)) {
		$error          =   true;
		$message[]      =   "Name Already Exist";
	}
	if(!$error) {
		$this->arrFields['common_name']['Value']                =   $common_name;
		$this->arrFields['formal_name']['Value']                =   $formal_name;
		$this->arrFields['type_uid']['Value']                   =   $type_uid;
		$this->arrFields['sub_type_uid']['Value']               =   $sub_type_uid;
		$this->arrFields['sovereignty']['Value']                =   $sovereignty;
		$this->arrFields['capital']['Value']                    =   $capital;
		$this->arrFields['iso_4217_currency_code']['Value']     =   $iso_4217_currency_code;
		$this->arrFields['iso_4217_currency_name']['Value']     =   $iso_4217_currency_name;
		$this->arrFields['itu_t_telephone_code']['Value']       =   $itu_t_telephone_code;
		$this->arrFields['iso_3166_1_2_letter_code']['Value']   =   $iso_3166_1_2_letter_code;
		$this->arrFields['iso_3166_1_3_letter_code']['Value']   =   $iso_3166_1_3_letter_code;
		$this->arrFields['iso_3166_1_number']['Value']          =   $iso_3166_1_number;
		$this->arrFields['iana_country_code_tld']['Value']      =   $iana_country_code_tld;
		$this->arrFields['active']['Value']                     =   $is_active;
	}
	
	return array($error,$message);
}
}
?>