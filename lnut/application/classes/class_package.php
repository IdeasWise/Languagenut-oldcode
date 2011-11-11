<?php

class class_package extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function saveSections() {
		
		if (isset($_POST['submit'])) {
			$query = "DELETE ";
			$query.="FROM ";
			$query.="`class_package_sections` ";
			$query.="WHERE ";
			$query.="`package_uid` ='{$_POST['package_uid']}'";
			
			database::query($query);
			$objPackage = new class_package($_POST['package_uid']);
			$objPackage->load();
			$support_language_uid = $objPackage->get_support_language_uid();
			foreach ($_POST['section'] as $index => $value) {
				$language_uid = 0;
				$year_uid = 0;
				$unit_uid = 0;
				$section_uid = 0;
				list(
						$language_uid,
						$year_uid,
						$unit_uid,
						$section_uid
						) = explode('_', $index);
				$sql = "INSERT INTO `class_package_sections`";
				$sql.=" SET ";
				$sql.=" package_uid='{$_POST['package_uid']}',";
				$sql.=" support_language_uid='{$support_language_uid}',";
				$sql.=" learnable_language_uid='{$language_uid}',";
				$sql.=" year_uid='{$year_uid}',";
				$sql.=" unit_uid='{$unit_uid}',";
				$sql.=" section_uid='{$section_uid}'";
				database::query($sql);
			}
		}
	}

	public function softDelete($uid, $school_uid) {
		$sql = "UPDATE ";
		$sql.="`class_package` ";
		$sql.=" SET ";
		$sql.=" deleted='1' ";
		$sql.=" WHERE ";
		$sql.=" uid='{$uid}'";
		$sql.=" AND school_uid='{$school_uid}'";
		database::query($sql);
	}

	public function insertOrUpdate($schoolUid=0, $arrData=array()) {

		if ($schoolUid != 0 && !empty($arrData["packages"])) {
			foreach ($arrData["packages"] as $package) {
				$this->isValidCreate($schoolUid, $package);
			}
			return TRUE;
		} else {
			$arrData['message'] = 'Please select any package';
			return FALSE;
		}
	}

	public function deleteBeforeUpdate($schoolUid=0, $packageUid=0) {
		$sql = "UPDATE class_package SET
					deleted='1'
					WHERE 
					school_uid='{$schoolUid}'
					AND
					uid='{$packageUid}'";
		database::query($sql);
	}

	public function isValidCreate($schoolUid, $packageUid) {

		$realpackageUid = 0;
		$wherePackage = "";
		$resellerPackageUid = 0;
		$resellerSubPackageUid = 0;
		$packageRecord = array();
		$pType = explode("_", $packageUid);
		$table_name = "reseller_package";
		$pUid = 0;
		if ($pType[0] == 'package') {
			$sql = "SELECT * FROM `reseller_package` WHERE uid='{$pType[1]}'";
			$packageRecord = database::arrQuery($sql);
			$wherePackage = "reseller_package_uid='$pType[1]' AND reseller_sub_package_uid='0'";
			$pUid = $resellerPackageUid = $pType[1];
			$table_name = "reseller_package";
		} else {
			$sql = "SELECT * FROM `reseller_sub_package` WHERE uid='{$pType[2]}'";
			$packageRecord = database::arrQuery($sql);
			$wherePackage = "reseller_sub_package_uid='{$pType[2]}'";
			$resellerPackageUid = $pType[1];
			$pUid = $resellerSubPackageUid = $pType[2];
			$table_name = "reseller_sub_package";
		}

		$sql = "SELECT uid FROM class_package 
					WHERE
                    iupdated_date='0' 
                    AND
                    school_uid='{$schoolUid}'
                    AND
                    {$wherePackage}";
		$packageRecordForUpdate = database::arrQuery($sql);
		$packageRecordForUpdateUid = (isset($packageRecordForUpdate[0]["uid"])) ? $packageRecordForUpdate[0]["uid"] : "0";

		$newPackageUid = 0;
		foreach ($packageRecord as $record) {
			$sql = "UPDATE class_package SET
                    updated_date='" . date("Y-m-d H:i:s") . "',
                    iupdated_date='" . time() . "'
                    WHERE
                    uid='{$packageRecordForUpdateUid}'
                ";
			database::query($sql);
			$sql = "INSERT INTO class_package SET
                    reseller_package_uid='{$resellerPackageUid}',
                    reseller_sub_package_uid='{$resellerSubPackageUid}',
                    school_uid='{$schoolUid}',
                    name='{$record["name"]}',
					created_date='" . date("Y-m-d H:i:s") . "',
                    support_language_uid='{$record["support_language_uid"]}'
                ";
			$newPackageUid = database::insert($sql);
		}
		
		// for class package
			$sql="UPDATE `class_package`";
			$sql.=" SET ";
			$sql.=" school_package_uid='{$newPackageUid}'";
			$sql.=" WHERE";
			$sql.=" school_package_uid='{$packageRecordForUpdateUid}'";
			database::query($sql);
		// end for class package
			
		$sql = "SELECT * FROM {$table_name}_activity WHERE package_uid='{$pUid}'";
		$package_activity_Record = database::arrQuery($sql);
		foreach ($package_activity_Record as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			$sql = "INSERT INTO class_package_activity SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM {$table_name}_activity_reading WHERE package_uid='{$pUid}'";
		$package_activity_readingRecord = database::arrQuery($sql);
		foreach ($package_activity_readingRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			$sql = "INSERT INTO class_package_activity_reading SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM {$table_name}_activity_speaklisten WHERE package_uid='{$pUid}'";
		$package_activity_speaklistenRecord = database::arrQuery($sql);
		foreach ($package_activity_speaklistenRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");

			$sql = "INSERT INTO class_package_activity_speaklisten SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM {$table_name}_activity_writing WHERE package_uid='{$pUid}'";
		$package_activity_writingRecord = database::arrQuery($sql);
		foreach ($package_activity_writingRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");

			$sql = "INSERT INTO class_package_activity_writing SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM {$table_name}_games WHERE package_uid='{$pUid}'";
		$package_gamesRecord = database::arrQuery($sql);
		foreach ($package_gamesRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");
			$sql = "INSERT INTO class_package_games SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM {$table_name}_language WHERE package_uid='{$pUid}'";
		$package_languageRecord = database::arrQuery($sql);
		foreach ($package_languageRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");

			$sql = "INSERT INTO class_package_language SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM {$table_name}_price WHERE package_uid='{$pUid}'";
		$package_priceRecord = database::arrQuery($sql);
		foreach ($package_priceRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");

			$sql = "INSERT INTO class_package_price SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}

		$sql = "SELECT * FROM {$table_name}_sections WHERE package_uid='{$pUid}'";
		$package_sectionsRecord = database::arrQuery($sql);
		foreach ($package_sectionsRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");

			$sql = "INSERT INTO class_package_sections SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}

		$sql = "SELECT * FROM {$table_name}_translation WHERE package_uid='{$pUid}'";
		$package_translationRecord = database::arrQuery($sql);
		foreach ($package_translationRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");

			$sql = "INSERT INTO class_package_translation SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM {$table_name}_units WHERE package_uid='{$pUid}'";
		$package_unitsRecord = database::arrQuery($sql);
		foreach ($package_unitsRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");

			$sql = "INSERT INTO class_package_units SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
		$sql = "SELECT * FROM {$table_name}_years WHERE package_uid='{$pUid}'";
		$package_yearsRecord = database::arrQuery($sql);
		foreach ($package_yearsRecord as $record) {
			$values = "";
			foreach ($record as $key => $value) {
				if ($key != "uid" && $key != "package_uid") {
					$values.=$key . "='" . $value . "',";
				}
			}
			$values = trim($values, ",");

			$sql = "INSERT INTO class_package_years SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
			database::insert($sql);
		}
	}

	public function checkExist($package_uid=null, $language_uid=null, $section_uid=null) {
		if ($package_uid != null && $language_uid != null && $section_uid != null) {
			$query = "SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`class_package_sections` ";
			$query.="WHERE ";
			$query.="`package_uid` = '" . $package_uid . "' ";
			$query.="AND ";
			$query.="`learnable_language_uid` = '" . $language_uid . "' ";
			$query.="AND ";
			$query.="`section_uid` = '" . $section_uid . "'";
			$query.="LIMIT 1";
			$result = database::query($query);
			if (mysql_error() == '' && mysql_num_rows($result)) {
				return ' checked="checked" ';
			}
		}
		return ' ';
	}

	public function isValidUpdate($uid, $classUid, $schoolPackageUid, $arrData=array()) {

		if ($this->isValidateFormData()) {
			$this->load();

			$sql = "UPDATE `class_package` SET ";
			$sql.=" updated_date='" . date("Y-m-d H-i-s") . "',";
			$sql.=" iupdated_date='" . time() . "'";
			$sql.=" WHERE uid='{$uid}'";
			database::query($sql);
			$sql = "INSERT INTO `class_package` SET
					class_uid='{$classUid}',
					school_package_uid='{$schoolPackageUid}',					
					name='{$_POST["name"]}',
					support_language_uid='{$_POST["support_language_uid"]}',
					created_date='" . date("Y-m-d H:i:s") . "',
					updated_date='0000-00-00 00:00:00',
					iupdated_date='0'
					";

			$newPackageUid = database::insert($sql);

			$sql = "SELECT * FROM class_package_activity_reading WHERE package_uid='{$uid}'";
			$package_activity_readingRecord = database::arrQuery($sql);
			foreach ($package_activity_readingRecord as $record) {
				$values = "";
				foreach ($record as $key => $value) {
					if ($key != "uid" && $key != "package_uid") {
						$values.=$key . "='" . $value . "',";
					}
				}
				$values = trim($values, ",");
				$sql = "INSERT INTO class_package_activity_reading SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
				database::insert($sql);
			}
			$sql = "SELECT * FROM class_package_activity_speaklisten WHERE package_uid='{$uid}'";
			$package_activity_speaklistenRecord = database::arrQuery($sql);
			foreach ($package_activity_speaklistenRecord as $record) {
				$values = "";
				foreach ($record as $key => $value) {
					if ($key != "uid" && $key != "package_uid") {
						$values.=$key . "='" . $value . "',";
					}
				}
				$values = trim($values, ",");
				$sql = "INSERT INTO class_package_activity_speaklisten SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
				database::insert($sql);
			}
			$sql = "SELECT * FROM class_package_activity_writing WHERE package_uid='{$uid}'";
			$package_activity_writingRecord = database::arrQuery($sql);
			foreach ($package_activity_writingRecord as $record) {
				$values = "";
				foreach ($record as $key => $value) {
					if ($key != "uid" && $key != "package_uid") {
						$values.=$key . "='" . $value . "',";
					}
				}
				$values = trim($values, ",");
				$sql = "INSERT INTO class_package_activity_writing SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
				database::insert($sql);
			}
			$sql = "SELECT * FROM class_package_games WHERE package_uid='{$uid}'";
			$package_gamesRecord = database::arrQuery($sql);
			foreach ($package_gamesRecord as $record) {
				$values = "";
				foreach ($record as $key => $value) {
					if ($key != "uid" && $key != "package_uid") {
						$values.=$key . "='" . $value . "',";
					}
				}
				$values = trim($values, ",");
				$sql = "INSERT INTO class_package_games SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
				database::insert($sql);
			}
			$sql = "SELECT * FROM class_package_sections WHERE package_uid='{$uid}'";
			$package_sectionsRecord = database::arrQuery($sql);
			foreach ($package_sectionsRecord as $record) {
				$values = "";
				foreach ($record as $key => $value) {
					if ($key != "uid" && $key != "package_uid") {
						$values.=$key . "='" . $value . "',";
					}
				}
				$values = trim($values, ",");
				$sql = "INSERT INTO class_package_sections SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
				database::insert($sql);
			}
			$sql = "SELECT * FROM class_package_translation WHERE package_uid='{$uid}'";
			$package_translationRecord = database::arrQuery($sql);
			foreach ($package_translationRecord as $record) {
				$values = "";
				foreach ($record as $key => $value) {
					if ($key != "uid" && $key != "package_uid") {
						$values.=$key . "='" . $value . "',";
					}
				}
				$values = trim($values, ",");
				$sql = "INSERT INTO class_package_translation SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
				database::insert($sql);
			}
			$sql = "SELECT * FROM class_package_units WHERE package_uid='{$uid}'";
			$package_unitsRecord = database::arrQuery($sql);
			foreach ($package_unitsRecord as $record) {
				$values = "";
				foreach ($record as $key => $value) {
					if ($key != "uid" && $key != "package_uid") {
						$values.=$key . "='" . $value . "',";
					}
				}
				$values = trim($values, ",");
				$sql = "INSERT INTO class_package_units SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
				database::insert($sql);
			}
			$sql = "SELECT * FROM class_package_years WHERE package_uid='{$uid}'";
			$package_yearsRecord = database::arrQuery($sql);
			foreach ($package_yearsRecord as $record) {
				$values = "";
				foreach ($record as $key => $value) {
					if ($key != "uid" && $key != "package_uid") {
						$values.=$key . "='" . $value . "',";
					}
				}
				$values = trim($values, ",");
				$sql = "INSERT INTO class_package_years SET
                    package_uid='{$newPackageUid}',
                    {$values}
                ";
				database::insert($sql);
			}
			if ($newPackageUid != null && isset($_POST['learnable_language_uid']) && count($_POST['learnable_language_uid'])) {
				foreach ($_POST['learnable_language_uid'] as $learnable_language_uid) {
					$sql = "INSERT INTO `class_package_language`";
					$sql.= " SET ";
					$sql.= " package_uid='{$newPackageUid}',";
					$sql.= " learnable_language_uid='{$learnable_language_uid}'";
					database::query($sql);
				}
			}
			if ($newPackageUid != null && isset($_POST['price']) && is_array($_POST['price'])) {
				foreach ($_POST['price'] as $index => $value) {
					$query = "SELECT ";
					$query.="`uid` ";
					$query.="FROM ";
					$query.="`class_package_price` ";
					$query.="WHERE ";
					$query.="`package_uid` = '" . mysql_real_escape_string($newPackageUid) . "' ";
					$query.="AND ";
					$query.="`locale`='" . mysql_real_escape_string($index) . "' ";
					$query.="LIMIT 1";
					$result = database::query($query);
					if (mysql_error() == '' && mysql_num_rows($result)) {
						$row = mysql_fetch_array($result);
						parent::__construct($row['uid'], 'class_package_price');
						$this->load();
						$this->set_price($value);
						if (isset($_POST['vat'][$index])) {
							$this->set_vat($_POST['vat'][$index]);
						}
						$this->save();
					} else {
						$sql = "INSERT INTO class_package_price ";
						$sql.=" SET";
						$sql.=" package_uid='{$newPackageUid}',";
						$sql.=" price='{$value}',";
						if (isset($_POST['vat'][$index])) {
							$sql.=" vat='{$_POST['vat'][$index]}',";
						}
						$sql.=" locale='{$index}'";
						database::insert($sql);
					}
					parent::__construct($newPackageUid, __CLASS__);
				}
			}
			return true;
		}
		return false;
	}

	private function isValidateFormData() {
		if (isset($_POST['package_uid']) && is_numeric($_POST['package_uid']) && $_POST['package_uid'] > 0) {
			parent::__construct($_POST['package_uid'], __CLASS__);
			$this->load();
		} else {
			$this->set_created_date(date('Y-m-d H:i:s'));
		}
		$name = (isset($_POST['name']) ) ? $_POST['name'] : '';
		$support_language_uid = (isset($_POST['support_language_uid']) ) ? $_POST['support_language_uid'] : 0;
		$arrMessages = array();
		if (trim(strlen($name)) < 5 || trim(strlen($name)) > 255) {
			$arrMessages['error_name'] = "Name must be 5 to 255 characters in length.";
		} else if (!validation::isValid('text', $name)) {
			$arrMessages['error_name'] = "Please enter valid name.";
		}
		if ($support_language_uid == 0) {
			$arrMessages['error_support_language'] = "Please select support language.";
		} else if (!validation::isValid('int', $support_language_uid)) {
			$arrMessages['error_support_language'] = "Please select valid support language.";
		}
		if (!isset($_POST['learnable_language_uid'])) {
			$arrMessages['error_learnable_language'] = "Please select at-least one available language.";
		}
		$objPackagePrice = new package_price();
		$result = $objPackagePrice->isValidPriceandVat();
		if (is_array($result) && count($result)) {
			if (is_array($result['arrPrice']) && count($result['arrPrice'])) {
				$arrMessages['error_price'] = "Please enter valid price in " . implode(',', $result['arrPrice']) . " locale.";
			}
			if (is_array($result['arrVat']) && count($result['arrVat'])) {
				$arrMessages['error_vat'] = "Please enter valid VAT% in " . implode(',', $result['arrVat']) . " locale.";
			}
		}
		if (count($arrMessages) == 0) {
			$this->set_name($name);
			$this->set_support_language_uid($support_language_uid);
			$this->set_updated_date(date('Y-m-d H:i:s'));
			$this->set_iupdated_date(time());
		} else {
			$strMessage = '';
			foreach ($arrMessages as $index => $value) {
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>' . $value . '</li>';
			}
			$this->arrForm['message'] = '<p>Please correct the errors below:</p><ul>' . $strMessage . '</ul>';
		}
		foreach ($_POST as $index => $value) {
			if (!is_array($value)) {
				$this->arrForm[$index] = $value;
			}
		}
		if (count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getSchoolPackageByResellerPackage($resellerPackageUid=0, $schoolUid=0) {
		if (isset($resellerPackageUid) && $resellerPackageUid > 0) {
			$query = "SELECT count(uid) as total FROM ";
			$query .= " `class_package`";
			$query .= " WHERE";
			$query .= " `reseller_package_uid`='{$resellerPackageUid}'";
			$query .= " AND `reseller_sub_package_uid`='0'";
			$query .= " AND `deleted`='0'";
			$query .= " AND `iupdated_date`='0'";
			$query .= " AND `school_uid`='{$schoolUid}'";
			$query .= " LIMIT 1";
			return database::arrQuery($query);
		}
		return false;
	}

	public function getSchoolPackageByResellerSubPackage($resellerSubPackageUid=0, $schoolUid=0) {

		if (isset($resellerSubPackageUid) && $resellerSubPackageUid > 0) {
			$query = "SELECT count(uid) as total FROM ";
			$query .= " `class_package`";
			$query .= " WHERE";
			$query .= " `reseller_sub_package_uid`='{$resellerSubPackageUid}'";
			$query .= " AND `deleted`='0'";
			$query .= " AND `iupdated_date`='0'";
			$query .= " AND `school_uid`='{$schoolUid}'";
			$query .= " LIMIT 1";
			return database::arrQuery($query);
		}
		return false;
	}

	public function getAvailablePackage($schoolUid=0) {

		if ($schoolUid > 0) {
			$query = "SELECT * FROM ";
			$query .= " `class_package`";
			$query .= " WHERE";
			$query .= " `deleted`='0'";
			$query .= " AND `iupdated_date`='0'";
			$query .= " AND `school_uid`='{$schoolUid}'";
			return database::arrQuery($query);
		}
		return false;
	}

	public function getAvailablePackageList($schoolUid=0) {

		if ($schoolUid > 0) {

			$query = "SELECT count(uid) FROM ";
			$query .= " `class_package`";
			$query .= " WHERE";
			$query .= " `deleted`='0'";
			$query .= " AND `iupdated_date`='0'";
			$query .= " AND `school_uid`='{$schoolUid}'";
			$this->setPagination($query);

			$query = "SELECT * FROM ";
			$query .= " `class_package`";
			$query .= " WHERE";
			$query .= " `deleted`='0'";
			$query .= " AND `iupdated_date`='0'";
			$query .= " AND `school_uid`='{$schoolUid}'";
			$query .= " LIMIT " . $this->get_limit();

			return database::arrQuery($query);
		}
		return false;
	}

	public function getUpdateAvailable($schoolUid, $reseller_package_uid=0, $reseller_sub_package_uid=0) {

		if (isset($schoolUid) && $schoolUid > 0) {
			$query = "SELECT ";
			$query.=" count(sp.uid) as total ";
			$query.=" FROM ";
			$query.="`class_package` sp";
			$query.=" LEFT OUTER JOIN ";
			$query.="`reseller_package` rp";
			$query.=" ON ";
			$query.=" sp.reseller_package_uid=rp.uid";
			$query.=" LEFT OUTER JOIN ";
			$query.="`reseller_sub_package` rsp";
			$query.=" ON ";
			$query.=" sp.reseller_sub_package_uid=rsp.uid";
			$query.=" WHERE ";
			$query.=" (rp.created_date>sp.created_date OR rsp.created_date>sp.created_date)";
			$query.=" AND sp.deleted='0'";
			$query.=" AND sp.reseller_package_uid='{$reseller_package_uid}'";
			$query.=" AND sp.reseller_sub_package_uid='{$reseller_sub_package_uid}'";
			$query.=" AND sp.iupdated_date='0'";
			$query.=" AND sp.school_uid='{$schoolUid}'";
			$query.=" LIMIT 1";

			return database::arrQuery($query);
		}
		return false;
	}

	public function getLearnableLanguages($packageUid) {
		$sql = "SELECT learnable_language_uid FROM ";
		$sql.=" `class_package_language`";
		$sql.=" WHERE ";
		$sql.=" package_uid='{$packageUid}' ";
		$result = database::arrQuery($sql);
		$arrResult = array();
		foreach ($result as $row) {
			$arrResult[] = $row["learnable_language_uid"];
		}
		return $arrResult;
	}

	public function getSchoolPackage($school_uid=0) {
		if ($school_uid > 0) {
			$query = "SELECT * FROM ";
			$query.=" `class_package`";
			$query.=" WHERE ";
			$query.=" school_uid='{$school_uid}'";
			$query.=" AND iupdated_date='0'";
			$query.=" AND deleted='0'";
			return database::arrQuery($query);
		}
		return false;
	}

}

?>