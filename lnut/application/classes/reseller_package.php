<?php

class reseller_package extends generic_object {
	
	public $json_languages		= array();
	public $json_years			= array();
	public $json_units			= array();
	public $json_sections		= array();
	public $json_section_uids	= array();
	public $json_games			= array();
	public $games				= array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
		//$this->ParsePackage();
	}


	public function insertOrUpdate($resellerUid=0, $arrData=array()) {
		if ($resellerUid != 0 && !empty($arrData["packages"])) {

			foreach ($arrData["packages"] as $package) {
				$this->isValidCreate($resellerUid, $package);
			}

			return TRUE;
		} else {
			$arrData['message'] = 'Please select any package';
			return FALSE;
		}
	}

	public function deleteBeforeUpdate($resellerUid=0, $packageUid=0) {
		$sql = "UPDATE reseller_package SET
					deleted='1'
					WHERE 
					reseller_uid='{$resellerUid}'
					AND
					package_uid='{$packageUid}'";
		database::query($sql);
		

	}

	public function isValidCreate($resellerUid, $packageUid) {

		$sql = "SELECT * FROM package WHERE uid='{$packageUid}'";
		$packageRecord = database::arrQuery($sql);
		$sql = "SELECT uid FROM reseller_package 
					WHERE
                    iupdated_date='0' 
                    AND
                    reseller_uid='{$resellerUid}'
                    AND
                    package_uid='{$packageUid}'";
		$packageRecordForUpdate = database::arrQuery($sql);
		$packageRecordForUpdateUid = (isset($packageRecordForUpdate[0]["uid"])) ? $packageRecordForUpdate[0]["uid"] : "0";

		$newPackageUid = 0;
		foreach ($packageRecord as $record) {
			$sql = "UPDATE reseller_package SET
                    updated_date='" . date("Y-m-d H:i:s") . "',
                    iupdated_date='" . time() . "'
                    WHERE
                    uid='{$packageRecordForUpdateUid}'
                ";
			database::query($sql);
			$sql = "INSERT INTO reseller_package SET
                    reseller_uid='{$resellerUid}',
                    package_uid='{$packageUid}',
                    name='{$record["name"]}',
					created_date='" . date("Y-m-d H:i:s") . "',
                    support_language_uid='{$record["support_language_uid"]}'
                ";
			$newPackageUid = database::insert($sql);
		}
			// for school package
			$sql="UPDATE `schooladmin_package`";
			$sql.=" SET ";
			$sql.=" reseller_package_uid='{$newPackageUid}'";
			$sql.=" WHERE";
			$sql.=" reseller_package_uid='{$packageRecordForUpdateUid}'";
			database::query($sql);
			// end for school package
		$sql = "SELECT * FROM package_activity WHERE package_uid='{$packageUid}'";
		$package_activity_Record = database::arrQuery($sql);
		foreach ($package_activity_Record as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");			
			$sql = "INSERT INTO reseller_package_activity SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM package_activity_reading WHERE package_uid='{$packageUid}'";
		$package_activity_readingRecord = database::arrQuery($sql);
		foreach ($package_activity_readingRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");			
			$sql = "INSERT INTO reseller_package_activity_reading SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM package_activity_speaklisten WHERE package_uid='{$packageUid}'";
		$package_activity_speaklistenRecord = database::arrQuery($sql);
		foreach ($package_activity_speaklistenRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			
			$sql = "INSERT INTO reseller_package_activity_speaklisten SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM package_activity_writing WHERE package_uid='{$packageUid}'";
		$package_activity_writingRecord = database::arrQuery($sql);
		foreach ($package_activity_writingRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			
			$sql = "INSERT INTO reseller_package_activity_writing SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM package_games WHERE package_uid='{$packageUid}'";
		$package_gamesRecord = database::arrQuery($sql);
		foreach ($package_gamesRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			$sql = "INSERT INTO reseller_package_games SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM package_language WHERE package_uid='{$packageUid}'";
		$package_languageRecord = database::arrQuery($sql);
		foreach ($package_languageRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			
			$sql = "INSERT INTO reseller_package_language SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM package_price WHERE package_uid='{$packageUid}'";
		$package_priceRecord = database::arrQuery($sql);
		foreach ($package_priceRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			
			$sql = "INSERT INTO reseller_package_price SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}

		$sql = "SELECT * FROM package_sections WHERE package_uid='{$packageUid}'";
		$package_sectionsRecord = database::arrQuery($sql);
		foreach ($package_sectionsRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			
			$sql = "INSERT INTO reseller_package_sections SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		
		$sql = "SELECT * FROM package_translation WHERE package_uid='{$packageUid}'";
		$package_translationRecord = database::arrQuery($sql);
		foreach ($package_translationRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			
			$sql = "INSERT INTO reseller_package_translation SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM package_units WHERE package_uid='{$packageUid}'";
		$package_unitsRecord = database::arrQuery($sql);
		foreach ($package_unitsRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			
			$sql = "INSERT INTO reseller_package_units SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM package_years WHERE package_uid='{$packageUid}'";
		$package_yearsRecord = database::arrQuery($sql);
		foreach ($package_yearsRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			
			$sql = "INSERT INTO reseller_package_years SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
	}

	public function getPackageIds($resellerUid) {
		$sql = "SELECT package_uid FROM reseller_package WHERE reseller_uid='{$resellerUid}'";
		$result = database::query($sql);
		$uids = array();
		while ($data = mysql_fetch_assoc($result)) {
			$uids[] = $data["package_uid"];
		}
		return $uids;
	}

	public function getFields() {
		$response = array();

		foreach ($this->arrFields as $key => $val) {
			$response[$key] = $this->arrFields[$key]['Value'];
		}

		return $response;
	}

	public function getTranslationByUid($uid) {
		$response = false;

		$sql = "SELECT * FROM `reseller_package_translation`
		WHERE
		reseller_package_uid='{$uid}'";
		$res = database::query($sql);

		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			$response = array();
			while ($row = mysql_fetch_assoc($res)) {
				$response[$row['uid']] = array(
					'locale' => stripslashes($row['locale']),
					'language_uid' => stripslashes($row['language_uid']),
					'name' => stripslashes($row['name']),
					'primary_image_path' => stripslashes($row['primary_image_path']),
					'primary_image_caption' => stripslashes($row['primary_image_caption']),
					'secondary_image_path' => stripslashes($row['secondary_image_path']),
					'secondary_image_caption' => stripslashes($row['secondary_image_caption']),
					'introduction' => stripslashes($row['introduction'])
				);
			}
		}

		return $response;
	}

	public function getNewPackageList($resellerUid=0) {
		if ($resellerUid != 0) {

			$query = "SELECT ";
			$query.=" * ";
			$query.=" FROM ";
			$query.="`package` ";
			$query.=" WHERE ";
			$query.=" uid NOT IN ";
			$query.=" (SELECT DISTINCT(package_uid) FROM `reseller_package` WHERE reseller_uid='{$resellerUid}' AND deleted='0')";
			$query.="ORDER BY ";
			$query.="`name` ";

			return database::arrQuery($query);
		} else {
			return false;
		}
	}

	public function getAvailablePackageList($resellerUid=0) {
		if ($resellerUid != 0) {

			$query = "SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`package` ";
			$query.=" WHERE ";
			$query.=" uid IN ";
			$query.=" (SELECT DISTINCT(package_uid) FROM `reseller_package` WHERE reseller_uid='{$resellerUid}'  AND deleted='0')";
			$query.="ORDER BY ";
			$query.="`name` ";

			return database::arrQuery($query);
		} else {
			return false;
		}
	}

	public function getUpdatedAvailablePackageList($resellerUid=0) {
		if ($resellerUid != 0) {

			$query = "SELECT ";
			$query.=" p.uid ";
			$query.=" FROM ";
			$query.="`package` p";
			$query.=" INNER JOIN ";
			$query.="`reseller_package` rp";
			$query.=" ON ";
			$query.=" p.uid=rp.package_uid";
			$query.=" WHERE ";
			$query.=" p.updated_date>rp.created_date";
			$query.=" AND rp.deleted='0'";
			$query.=" AND rp.iupdated_date='0'";
			$query.=" ORDER BY ";
			$query.="p.`name` ";
			$result=database::query($query);
			
			$returnArray=array();
			while ($data=  mysql_fetch_assoc($result)){
				$returnArray[]=$data["uid"];
			}
			return $returnArray;
		} else {
			return array();
		}
	}
	
	public function getResellerPackage($reseller_uid=0){
		if(isset($reseller_uid) && $reseller_uid>0){
			$query="SELECT * FROM ";
			$query.=" `reseller_package`";
			$query.=" WHERE";
			$query.=" `reseller_uid` = '{$reseller_uid}'";
			$query.=" AND `deleted` = '0'";
			$query.=" AND `iupdated_date` = '0'";
			
			return database::arrQuery($query);
		}
		return false;
	}
	public function getResellerSubPackage($reseller_uid=0,$package_uid=0){
		if(isset($reseller_uid) && $reseller_uid>0 && isset($package_uid) && $package_uid>0 ){
			$query="SELECT * FROM ";
			$query.=" `reseller_sub_package`";
			$query.=" WHERE";
			$query.=" `reseller_uid` = '{$reseller_uid}'";
			$query.=" AND `package_uid` = '{$package_uid}'";
			$query.=" AND `deleted` = '0'";
			$query.=" AND `iupdated_date` = '0'";
			
			return database::arrQuery($query);
		}
		return false;
	}

/*****************************************************************************************
**** FOLLOWING FUNCTION IS DEVELOPED BY SHAILESH JOSHI ON 04/08/2011
******************************************************************************************/

	public function getNewpackages($reseller_uid=null) {
		if($reseller_uid!=null) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`package` ";
			$query.="WHERE ";
			$query.="`uid` ";
			$query.="NOT IN ( ";
				$query.="SELECT ";
				$query.="DISTINCT(package_uid) ";
				$query.="FROM ";
				$query.="`reseller_package` ";
				$query.="WHERE ";
				$query.="reseller_uid='".$reseller_uid."' ";
				$query.="AND ";
				$query.="deleted='0'";
			$query.=") ";
			$query.="ORDER BY ";
			$query.="`name` ";
			return database::arrQuery($query);
		}
		return array();
	}

	public function assignPckages($reseller_uid=null){
		if($reseller_uid!=null) {
			if(isset($_POST['packages']) && count($_POST['packages'])) {
				foreach($_POST['packages'] as $package_uid) {
					$this->assignPackageToResller($reseller_uid,$package_uid);
				}
				return true;
			} else {
				return array('message'=>'<ul><li>Please choose at least one package to assign.</li></ul>');
			}
		}
	}

	private function assignPackageToResller($reseller_uid=null,$package_uid=null) {
		if($reseller_uid!=null && $package_uid!=null) {
			$query ="INSERT INTO ";
			$query.="`reseller_package` ";
			$query.="(";
				$query.="`package_uid`, ";
				$query.="`reseller_uid`, ";
				$query.="`name`, ";
				$query.="`support_language_uid`, ";
				$query.="`created_date`, ";
				$query.="`learnable_language`, ";
				$query.="`pricing`, ";
				$query.="`sections`, ";
				$query.="`games` ";
			$query.=") ";
			$query.="SELECT ";
			$query.="'".$package_uid."', ";
			$query.="'".$reseller_uid."', ";
			$query.="`name`, ";
			$query.="`support_language_uid`, ";
			$query.="'".date('Y-m-d H:i:s')."', ";
			$query.="`learnable_language`, ";
			$query.="`pricing`, ";
			$query.="`sections`, ";
			$query.="`games` ";
			$query.="FROM ";
			$query.="`package` ";
			$query.="WHERE ";
			$query.="`uid`='".$package_uid."' ";
			database::query($query);
		}
	}

	public function getAvailablePackages($reseller_uid=null,$all=true) {
		if($reseller_uid!=null) {
			if(!$all) {
			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`reseller_package` ";
			$query.="WHERE ";
			$query.="`reseller_uid`='".$reseller_uid."' ";
			$query.="AND ";
			$query.="`deleted`='0' ";
			$query.="AND ";
			$query.="`package_history_uid`='0' ";
			
			$this->setPagination( $query );
			}
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`reseller_uid`, ";
			$query.="`name`, ";
			$query.="`created_date` ";
			$query.="FROM ";
			$query.="`reseller_package` ";
			$query.="WHERE ";
			$query.="`reseller_uid`='".$reseller_uid."' ";
			$query.="AND ";
			$query.="`deleted`='0' ";
			$query.="AND ";
			$query.="`package_history_uid`='0' ";
			if(!$all) {
				$query.= "LIMIT ".$this->get_limit();
			}
			return database::arrQuery($query);
		}
		return array();
	}

	public function isValidPackage($reseller_uid=null,$package_uid=null) {
		if($reseller_uid!=null && $package_uid!=null) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`reseller_package` ";
			$query.="WHERE ";
			$query.="`reseller_uid`='".$reseller_uid."' ";
			$query.="AND ";
			$query.="uid='".$package_uid."' ";
			$query.="AND ";
			$query.="`deleted`='0' ";
			$query.="LIMIT 0,1 ";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)==1) {
				parent::__construct($package_uid, __CLASS__);
				$this->load();
				$this->ParsePackage();
				return true;
			}
		}
		return false;
	}
	private function ParsePackage() {
		if($this->get_sections() != '') {
			$this->objJson = json_decode($this->get_sections());

			if(isset($this->objJson->sections)) {
				foreach($this->objJson->sections as $data) {
					$this->json_sections[] = $data->section_pair;
					$this->json_years[$data->learnable_language_uid][] = $data->year_uid;
					$this->json_units[$data->learnable_language_uid][$data->year_uid][] = $data->unit_uid;
					$this->json_section_uids[$data->learnable_language_uid][$data->unit_uid][] = $data->section_uid;
				}
			}
		}

		if($this->get_learnable_language() != '') {
			$this->objJson = json_decode($this->get_learnable_language());
			if(isset($this->objJson->language_uids) && is_array($this->objJson->language_uids)) {
				$this->json_languages = $this->objJson->language_uids;
			}
		}

		if($this->get_games() != '') {
			$this->objJson = json_decode($this->get_games());
			if(isset($this->objJson->games)) {
				foreach($this->objJson->games as $data) {
					$this->json_games[] = $data->game_pair;
					$this->games[$data->learnable_language_uid][$data->unit_uid][$data->section_uid][] = $data->game_uid;
					//$this->json_section_uids[$data->learnable_language_uid][$data->unit_uid][] = $data->section_uid;
				}
			}
		}
		//echo '<pre>';
		//print_r($this->games);
		//echo '</pre>';
		//exit;
	}
}

?>