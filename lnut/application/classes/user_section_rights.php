<?php

class user_section_rights extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function EnableNextSection($user_uid=null) {
		if($user_uid!=null) {
			$query ="SELECT ";
			$query.="`section_uid`, ";
			$query.="`section_position` ";
			$query.="FROM ";
			$query.="`user_section_rights`  ";
			$query.="WHERE ";
			$query.="`user_uid`='".$user_uid."' ";
			$query.="AND ";
			$query.="`is_lately_added`='1'";
			$result = database::query($query);
			if($result && mysql_num_rows($result) && mysql_error()=='') {
				$row=mysql_fetch_array($result);
				$query ="SELECT ";
				$query.="`uid` AS `section_uid`,";
				$query.="`unit_uid`,";
				$query.="`section_position` ";
				$query.="FROM ";
				$query.="`sections` ";
				$query.="WHERE ";
				$query.="`section_position`>'".$row['section_position']."' ";
				$query.="ORDER BY ";
				$query.="`section_position` ";
				$query.="LIMIT 0,1";
				$resSection = database::query($query);
				if($resSection && mysql_num_rows($resSection) && mysql_error()=='') {
					$arrSection = mysql_fetch_array($resSection);
					$this->updatePreviousEntries($user_uid);
					$query ="INSERT ";
					$query.="INTO ";
					$query.="`user_section_rights` ( ";
					$query.="`user_uid`,";
					$query.="`unit_uid`,";
					$query.="`section_uid`,";
					$query.="`section_position`,";
					$query.="`is_lately_added`,";
					$query.="`enabled_datetime`,";
					$query.="`time`";
					$query.=") VALUES (";
					$query.="'".$user_uid."',";
					$query.="'".$arrSection['unit_uid']."',";
					$query.="'".$arrSection['section_uid']."',";
					$query.="'".$arrSection['section_position']."',";
					$query.="'1',";
					$query.="'".date('Y-m-d H:i:s')."',";
					$query.="'".time()."'";
					$query.=") ";
					database::query($query);
				}
			}
		}
	}

	private function updatePreviousEntries($user_uid=null) {
		if($user_uid!=null) {
			$query ="UPDATE ";
			$query.="`user_section_rights` ";
			$query.="SET ";
			$query.="`is_lately_added`='0' ";
			$query.="WHERE ";
			$query.="`user_uid`='".$user_uid."' ";
			$query.="AND ";
			$query.="`is_lately_added`='1' ";
			database::query($query);
		}
	}

	public function getMaxSection_uid() {
		$query = "SELECT MAX(`uid`) AS `section_uid`FROM `sections`";
		$result=database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			$row = mysql_fetch_array($result);
			return $row['section_uid'];
		}
		return 0;
	}

	public function cronCommand(){
		$section_upgrade_time = '-7 days';
		//$section_upgrade_time = '-5 minute';
		$query = "SELECT MAX(`uid`) AS `section_uid`FROM `sections`";
		$result=database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			$row = mysql_fetch_array($result);
			$max_section_uid = $row['section_uid'];
			$query ="SELECT ";
			$query.="`user_uid` ";
			$query.="FROM ";
			$query.="`user_section_rights` ";
			$query.="WHERE ";
			$query.="`section_uid` < '".$max_section_uid."' ";
			$query.="AND ";
			$query.="`is_lately_added`='1' ";
			$query.="AND ";
			$query.="`time` <= '".strtotime($section_upgrade_time)."'";
			//echo $query;
			$result = database::query($query);
			if(mysql_error()=='' && $result && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$this->EnableNextSection($arrRow['user_uid']);
				}
			}
		}
	}

	public function EnableNextSectionBasedOnBronzeMedal($user_uid=null,$section_uid=null) {

		if($user_uid==null && $section_uid==null && !is_numeric($user_uid) && !is_numeric($section_uid)) {
			return false;
		}

		$query = "SELECT MAX(`uid`) AS `section_uid`FROM `sections`";
		$result=database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			$row = mysql_fetch_array($result);
			if($row['section_uid'] <= $section_uid) {
				return false;
			}
		}


		$query = "SELECT ";
		$query.="`section_uid` ";
		$query.="FROM ";
		$query.="`user_section_rights`";
		$query.="WHERE ";
		$query.="`user_uid`='".$user_uid."' ";
		$query.="AND ";
		$query.="`is_lately_added`='1' ";
		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			$row = mysql_fetch_array($result);
			if($row['section_uid'] == $section_uid) {
				if($this->isWon4BronzeMedal($user_uid,$section_uid)===true) {
					$this->EnableNextSection($user_uid);
				}
			}
		}

	}

	private function isWon4BronzeMedal($user_uid=null,$section_uid=null) {
		if($user_uid==null && $section_uid==null && !is_numeric($user_uid) && !is_numeric($section_uid)) {
			return false;
		}

		$query ="SELECT ";
		$query.="COUNT(`uid`) AS `total` ";
		$query.="FROM ";
		$query.="`gamescore` ";
		$query.="WHERE ";
		$query.="`user_uid`='".$user_uid."' ";
		$query.="AND ";
		$query.="`section_uid`='".$section_uid."' ";
		$query.="AND ";
		$query.="`score_right` >= '40' ";
		$result = database::query($query);
		if($result && mysql_error()=='') {
			$row = mysql_fetch_array($result);
			if($row['total'] >= 4) {
				return true;
			}
		}
		return false;
	}
}
?>