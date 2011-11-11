<?php

class validation {

	public static function isPresent($key = "",$data = array()) {
		return (array_key_exists($key,$data) && strlen(trim($data[$key])) > 0);
	}

	public static function isValid($type = "text",$value = "") {
		switch(strtolower($type)) {
			case 'integer':
			case 'int':
			case 'double':
				return (is_numeric($value))?true:false;
				break;
			case 'text':
			case 'string':
				return (is_string($value))?true:false;
				break;
			case 'date':
				return ((strtotime($value))!==false)?true:false;
				break;
			case 'time':
				break;
			case 'phone':
			case 'fax':
				return self::is_phone($value);
				break;
			case 'email':
				return self::is_email($value);
				break;
			case 'url':
				return self::is_url($value);
				break;
			case 'ip_address':
				return self::is_ip_address($value);
				break;
			case 'percentage':
				return self::is_percentage($value);
				break;
			case 'insurance_number':
				return self::is_insurance_number($value);
				break;
		}
	}

	public static function is_phone($phone = "") {
		$phone = preg_replace('/[^\d]/','',$phone);
		return (isset($phone[8]) && !isset($phone[21]))?true:false;
	}

	public static function is_email($email = "") {
		return (filter_var($email,FILTER_VALIDATE_EMAIL))?true:false;
	}

	public static function is_url($url = "") {
		return (filter_var($url,FILTER_VALIDATE_URL))?true:false;
	}

	public static function is_ip_address($ip_address = "") {
		return (filter_var($value,FILTER_VALIDATE_IP))?true:false;
	}

	public static function is_percentage($value = "") {
		return (is_numeric($value) && $value > 0 && $value < 100);
	}

	public static function is_insurance_number($value = "") {
		return (strlen($value) == 9 && is_string(substr($value,0,2)) && is_numeric(substr($value,2,6)) && is_string(substr($value,8)));
	}
}
?>