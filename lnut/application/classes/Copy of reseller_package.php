<?php
class reseller_package extends generic_object {
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
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
			$sql = "UPDATE reseller_package_activity_reading SET                    
                    {$values}
					WHERE
                    package_uid='{$packageRecordForUpdateUid}'
                ";
			// database::query($sql);
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
			$sql = "UPDATE reseller_package_activity_speaklisten SET                    
                    {$values}
					WHERE
                    package_uid='{$packageRecordForUpdateUid}'
                ";
			// database::query($sql);
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
			$sql = "UPDATE reseller_package_activity_writing SET                    
                    {$values}
					WHERE
                    package_uid='{$packageRecordForUpdateUid}'
                ";
			// database::query($sql);
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
			$sql = "UPDATE reseller_package_language SET                    
                    {$values}
					WHERE
                    package_uid='{$packageRecordForUpdateUid}'
                ";
			// database::query($sql);
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
			$sql = "UPDATE reseller_package_price SET                    
                    {$values}
					WHERE
                    package_uid='{$packageRecordForUpdateUid}'
                ";
			// database::query($sql);
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
			$sql = "UPDATE reseller_package_sections SET                    
                    {$values}
					WHERE
                    package_uid='{$packageRecordForUpdateUid}'
                ";
			// database::query($sql);
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
			$sql = "UPDATE reseller_package_translation SET                    
                    {$values}
					WHERE
                    package_uid='{$packageRecordForUpdateUid}'
                ";
			// database::query($sql);
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
			$sql = "UPDATE reseller_package_units SET                    
                    {$values}
					WHERE
                    package_uid='{$packageRecordForUpdateUid}'
                ";
			// database::query($sql);
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
			$sql = "UPDATE reseller_package_years SET                    
                    {$values}
					WHERE
                    package_uid='{$packageRecordForUpdateUid}'
                ";
			// database::query($sql);
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
}
?>