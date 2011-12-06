<?php

/**
 * packagescript.php
 */

class packageScript extends Controller {



	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		$this->index();
	}

	private function index() {
		set_time_limit(0);
		// get all resellers

		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`prefix` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="WHERE ";
		$query.="`prefix` ";
		$query.="NOT IN ( ";
			$query.="SELECT ";
			$query.="`locale_rights` ";
			$query.="FROM ";
			$query.="`profile_reseller` ";
		$query.=") ";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($arrRow = mysql_fetch_array($result)) {
				$arrReseller = array();
				$arrReseller = $this->createReseller($arrRow['prefix']);
				$query ="INSERT ";
				$query.="INTO ";
				$query.="`reseller_sub_package` ( ";
					$query.="`reseller_uid`,";
					$query.="`name`,";
					$query.="`support_language_uid`,";
					$query.="`created_date`,";
					$query.="`learnable_language`,";
					$query.="`price`,";
					$query.="`vat`,";
					$query.="`sections`,";
					$query.="`games`,";
					$query.="`is_active`,";
					$query.="`package_type`,";
					$query.="`is_default_school_package`";
				$query.=") ";
				$query.="SELECT ";
				$query.="'".$arrReseller['iuser_uid']."',";
				$query.="'default school package',";
				$query.="'".$arrRow['uid']."',";
				$query.="'".date('Y-m-d H:i:s')."',";
				$query.="`learnable_language`,";
				$query.="`price`,";
				$query.="`vat`,";
				$query.="`sections`,";
				$query.="`games`,";
				$query.="'1',";
				$query.="'school',";
				$query.="'1' ";
				$query.="FROM ";
				$query.="`reseller_sub_package` ";
				$query.="WHERE ";
				$query.="`uid`='2'";
				//$CreatePackage = database::query($query);
			}
		}
	}

	private function createReseller($locale=null) {
		$arrReseller = array();
		// INSERT SCHOOL RECORD IN MAIN USER TABLE 
		$query = "INSERT INTO `user` SET ";
		$query .= "`registered_dts` = '".date('Y-m-d H:i:s')."', ";
		$query .= "`registration_ip` = '".$_SERVER['REMOTE_ADDR']."', ";
		$query .= "`email` = 'reseller.".$locale."@languagenut.com', ";
		$query .= "`password` = '".md5('reseller.'.$locale)."', ";
		$query .= "`active` = '1', ";
		$query .= "`access_allowed` = '1', ";
		$query .= "`allow_access_without_sub` = '1', ";
		$query .= "`locale` = '".$locale."', ";
		$query .= "`user_type` = 'reseller' ";
		//$query;
		$res = database::query( $query );
		$uid = mysql_insert_id();

		if( is_numeric($uid) && $uid > 0 ) {
			$registration_key = md5( $uid .'-'. $_SERVER['REMOTE_ADDR'] );
			$sql = "UPDATE `user` SET ";
			$sql .="`registration_key` = '".$registration_key."' ";
			$sql .="WHERE `uid` = '".$uid."'";
			database::query($sql);

			// INSERT ADMIN IN USERS ADMIN PROFILE TABLE...
			$query = "INSERT INTO `profile_reseller` SET ";
			$query .= "`iuser_uid` = '".$uid."', ";
			$query .= "`vfirstname` = 'Reseller', ";
			$query .= "`vlastname` = '".$locale."', ";
			$query .= "`vemail` = 'reseller.".$locale."@languagenut.com', ";
			$query .= "`locale_rights` = '".$locale."' ";
			$res		= database::query( $query ) or die($query.'<br>'.mysql_error());
			$admin_uid	= mysql_insert_id();
			return $arrReseller = array(
				'iuser_uid'	=>$uid
			);
		}

	}

}

?>