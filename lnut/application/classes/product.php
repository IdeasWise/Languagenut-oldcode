<?php

class product extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public static function getProductByUid($uid=null){
		
		$query = "SELECT * ";
		$query .= "FROM ";
		$query .= "`product` ";
		$query .= "WHERE ";
		$query .= "`product`.`uid` = '".$uid."'";
		echo $query;
		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			return mysql_fetch_array($result);
		} else {
			return false;
		}
	}
}
?>