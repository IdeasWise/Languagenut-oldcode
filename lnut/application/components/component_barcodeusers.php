<?php

class component_barcodeusers {

	/* following method is developed by shailesh on 07/03/2012 */
	public function ip_look_up() {
		$ip_address = $_SERVER['REMOTE_ADDR'];
		if(!isset($_SESSION['is_barcode_redirect_script_checked']) && !isset($_SESSION['user'])) {
			$_SESSION['is_barcode_redirect_script_checked'] = true;
			$query ="SELECT ";
			$query.="`locale` ";
			$query.="FROM ";
			$query.="`user` ";
			$query.="WHERE ";
			$query.="`barcode_ip_address`='".$ip_address."' ";
			$query.="AND ";
			$query.="`deleted`='0' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result) && $result) {
				$arrRow = mysql_fetch_array($result);
				if(isset($arrRow['locale']) && !empty($arrRow['locale'])) {
					header('Location:' . config::base($arrRow['locale']) . '/goldfields-login/');
					exit();
				}
			}
		}
		
	}

}

?>