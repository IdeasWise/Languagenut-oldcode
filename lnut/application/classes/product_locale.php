<?php

class product_locale extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public static function getDefaultByLocale(){
		
		$locale = config::get('locale');
		
		$query = "SELECT `product_locale`.`product_uid`, `product_locale`.`years_1`, `product_locale`.`uid` ";
		$query .= "FROM ";
		$query .= "`product_locale` ";
		$query .= "LEFT JOIN ";
		$query .= "`language` ";
		$query .= "ON ";
		$query .= "`product_locale`.`language_uid` = `language`.`uid` ";
		$query .= "WHERE ";
		$query .= "`language`.`prefix` = '".$locale."' ";
		$query .= "AND ";
		$query .= "`product_locale`.`default` = '1' ";
		$query .= "LIMIT 1";
		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			$row = mysql_fetch_array($result);
			return $row;
		} else {
			return false;
		}
	}
	
	public static function getByLocale(){
		
		$locale = config::get('locale');
		$query = "SELECT `product_locale`.`uid`, `product_locale`.`name` ";
		$query .= "FROM ";
		$query .= "`product_locale` ";
		$query .= "LEFT JOIN ";
		$query .= "`language` ";
		$query .= "ON ";
		$query .= "`product_locale`.`language_uid` = `language`.`uid` ";
		$query .= "WHERE ";
		$query .= "`language`.`prefix` = '".$locale."'";
		
		// type needs to be school or homeuser
						   
		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			while ($row = mysql_fetch_array($result)) {
				$products[] = $row;
			}
		} else {
			return false;
		}
		return $products;
	}
	
	public static function getByLocaleUid($uid=null){
		
		$query = "SELECT `product_locale`.`uid`, `product_locale`.`name`, `product_locale`.`years_1`, `product_locale`.`product_uid` ";
		$query .= "FROM ";
		$query .= "`product_locale` ";
		$query .= "WHERE ";
		$query .= "`product_locale`.`uid` = '".$uid."'";
		
		// type needs to be school or homeuser
						   
		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			return mysql_fetch_array($result);
		} else {
			return false;
		}
	}
	
}
?>