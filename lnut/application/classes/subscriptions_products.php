<?php

class subscriptions_products extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}
	
	public static function addEntry($subscriptions_uid=null,$product_locale_uid=null,$product_uid=null) {
		if($subscriptions_uid!=null && $product_locale_uid!=null && $product_uid!=null) {
			$query ="INSERT INTO ";
			$query.="`subscriptions_products` ";
			$query.="(`subscriptions_uid`,`product_locale_uid`,`product_uid`) ";
			$query.="VALUES ('".$subscriptions_uid."', ";
			$query.="'".$product_locale_uid."', ";
			$query.="'".$product_uid."')";
			$result = database::query($query);
			if(mysql_error() == '') {
				return mysql_insert_id();
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
}
?>