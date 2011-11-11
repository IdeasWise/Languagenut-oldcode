<?php

class classes extends generic_object {

    public $arrForm = array();

    public function __construct($uid = 0) {
        parent::__construct($uid, __CLASS__);
    }

    public function classesSelectBox($inputName, $selctedValue = NULL) {
        $query = "SELECT ";
        $query.="`uid`, ";
        $query.="`name` ";
        $query.="FROM ";
        $query.="`classes` ";
        $query.="ORDER BY `name`";
        $result = database::query($query);
        $data = array();
        $data[0] = 'Classes';
        if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $data[$row['uid']] = $row['name'];
            }
        }
        return format::to_select(array("name" => $inputName, "id" => $inputName, "style" => "width:180px;", "options_only" => false), $data, $selctedValue);
    }

    public function getList($data = array(), $OrderBy = "`C`.`name` ", $all = false) {
        $where = ' WHERE `S`.`uid` = `C`.`school_id`';
        if (isset($_SESSION['user']['localeRights'])) {
            $where.=" AND ";
            $where.="`school_id` IN ( ";
            $where.="SELECT ";
            $where.="`SC`.`uid` ";
            $where.="FROM ";
            $where.="`users_schools` AS SC, ";
            $where.="`user` AS U ";
            $where.="WHERE ";
            $where.="`SC`.`school` != '' ";
            $where.="AND ";
            $where.="`U`.`uid` = `SC`.`user_uid` ";
            $where.="AND ";
            $where.="`locale` IN (" . $_SESSION['user']['localeRights'] . ") ";
            $where.=")";
        } else if (isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] != 1) {
            if (isset($_SESSION['user']['school_uid'])) {
                $where .= ' AND `school_id` = "' . $_SESSION['user']['school_uid'] . '"';
            }
        }
        if (isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights'] == 'schoolteacher')
            $where .= ' AND `C`.`class_user_uid` = "' . $_SESSION['user']['uid'] . '"';
        foreach ($data as $idx => $val) {
            $where .= " AND " . $idx . "='" . $val . "'";
        }
        if (!$all) {
            $query = "SELECT ";
            $query.="COUNT(`C`.`uid`) ";
            $query.="FROM ";
            $query.="`classes` AS `C`, ";
            $query.="`users_schools` AS `S` ";
            $query.=$where;
            $this->setPagination($query);
        }
        $query = "SELECT ";
        $query.="`C`.*, ";
        $query.="`S`.`school` ";
        $query.="FROM ";
        $query.="`classes` AS `C`, ";
        $query.="`users_schools` AS `S` ";
        $query.=$where . " ";
        $query.=" ORDER BY " . $OrderBy;
        if ($all == false) {
            $query.= "LIMIT " . $this->get_limit();
        }
        return database::arrQuery($query);
    }

    public function doSave() {
        if ($this->isValidateFormData() == true) {
            if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
                $this->save();
            } else {
                $insert = $this->insert();
                $this->arrForm['uid'] = $insert;
            }
            return true;
        } else {
            return false;
        }
    }

    private function isValidateFormData() {
        if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
            parent::__construct($_POST['uid'], __CLASS__);
            $this->load();
        }
        $arrFields = array(
            'name' => array(
                'value' => (isset($_POST['name'])) ? trim($_POST['name']) : '',
                'checkEmpty' => false,
                'errEmpty' => '',
                'minChar' => 2,
                'maxChar' => 255,
                'errMinMax' => 'Class name must be 2 to 255 characters in length.',
                'dataType' => 'text',
                'errdataType' => 'Please enter valid Class name.',
                'errIndex' => 'error_name'
            ),
            'school_id' => array(
                'value' => (isset($_POST['school_id']) && $_POST['school_id'] != '0') ? trim($_POST['school_id']) : '',
                'checkEmpty' => true,
                'errEmpty' => 'Please choose a school.',
                'minChar' => 0,
                'maxChar' => 0,
                'errMinMax' => '',
                'dataType' => 'int',
                'errdataType' => 'Please choose valid school.',
                'errIndex' => 'error_school_id'
            ),
            'description' => array(
                'value' => (isset($_POST['description'])) ? trim($_POST['description']) : '',
                'checkEmpty' => false,
                'errEmpty' => '',
                'minChar' => 0,
                'maxChar' => 0,
                'errMinMax' => '',
                'dataType' => 'text',
                'errdataType' => 'Please enter valid description.',
                'errIndex' => 'error_description'
            )
        );
        // $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
        if ($this->isValidarrFields($arrFields, $this) === true) {
            $this->set_name($arrFields['name']['value']);
            $this->set_school_id($arrFields['school_id']['value']);
            $this->set_description($arrFields['description']['value']);
            $this->set_class_user_uid($_SESSION['user']['uid']);
            return true;
        } else {
            return false;
        }
    }

    public static function generateLogins($class_uid=null) {
        $response = array();
        $arrStudentsInstances = profile_student::getByClassUidAsObjects($class_uid);
        if ($arrStudentsInstances && count($arrStudentsInstances) > 0) {
            foreach ($arrStudentsInstances as $objStudent) {
                if (true === ($success = $objStudent->generateLogin())) {
                    $response[] = $objStudent->get_uid();
                } else {
                    //echo $success;
                }
            }
        }
        return $response;
    }

    public function getClassPackageBySchoolPackage($class_uid=0, $school_uid=0, $school_package_uid=0) {
        if ($class_uid > 0 && $school_uid > 0) {
            $query = "SELECT count(uid) as total FROM `class_package`";
            $query .= " WHERE";
//            $query .= " school_package_uid='{$school_package_uid}'";
            $query .= " class_uid='{$class_uid}'";
            $query .= " AND iupdated_date='0'";
            $query .= " AND deleted='0'";
            $query .= " LIMIT 1";
            $result = database::arrQuery($query);
            return (isset($result[0]["total"])) ? $result[0]["total"] : false;
        }
        return false;
    }

    public function getAvailablePackage($class_uid=0) {
        $query = "SELECT * FROM `class_package`";
        $query .= " WHERE";
        $query .= " class_uid='{$class_uid}'";
        $query .= " AND iupdated_date='0'";
        $query .= " AND deleted='0'";
        return database::arrQuery($query);
    }

    public function getUpdateAvailable($classUid, $school_package_uid=0) {

        if (isset($classUid) && $classUid > 0) {
            $query = "SELECT ";
            $query.=" count(cp.uid) as total ";
            $query.=" FROM ";
            $query.="`class_package` cp";
            $query.=" LEFT OUTER JOIN ";
            $query.="`schooladmin_package` sp";
            $query.=" ON ";
            $query.=" cp.school_package_uid=sp.uid";
            $query.=" WHERE ";
            $query.=" sp.created_date>cp.created_date";
            $query.=" AND cp.deleted='0'";
            $query.=" AND cp.school_package_uid='{$school_package_uid}'";
            $query.=" AND cp.iupdated_date='0'";
            $query.=" AND cp.class_uid='{$classUid}'";
            $query.=" LIMIT 1";

            return database::arrQuery($query);
        }
        return false;
    }

    public function insertOrUpdate($classUid=0, $arrData=array()) {

        if ($classUid != 0 && !empty($arrData["packages"])) {
            foreach ($arrData["packages"] as $package) {
                $this->isValidCreate($classUid, $package);
            }
            return TRUE;
        } else {
            $arrData['message'] = 'Please select any package';
            return FALSE;
        }
    }

    public function deleteBeforeUpdate($classUid=0, $packageUid=0) {
        $sql = "UPDATE class_package SET
					deleted='1'
					WHERE 
					class_uid='{$classUid}'
					AND
					uid='{$packageUid}'";
        database::query($sql);
    }

    public function isValidCreate($classUid, $packageUid) {

        $packageRecord = array();

        $query = "SELECT count(uid) as total FROM `class_package`";
        $query .= " WHERE";
//      $query .= " school_package_uid='{$school_package_uid}'";
        $query .= " class_uid='{$classUid}'";
        $query .= " AND iupdated_date='0'";
        $query .= " AND deleted='0'";
        $query .= " LIMIT 1";
        $result = database::arrQuery($query);
        if($result[0]['total']>0){
            return ;
        }

        $table_name = "schooladmin_package";
        $pUid = $packageUid;

        $sql = "SELECT * FROM `schooladmin_package` WHERE uid='{$pUid}'";
        $packageRecord = database::arrQuery($sql);

        $sql = "SELECT uid FROM class_package
					WHERE
                    iupdated_date='0' 
                    AND
                    class_uid='{$classUid}'
                    AND
                    school_package_uid='{$packageUid}'";

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
                    school_package_uid='{$pUid}',
                    class_uid='{$classUid}',
                    name='{$record["name"]}',
					created_date='" . date("Y-m-d H:i:s") . "',
                    support_language_uid='{$record["support_language_uid"]}'
                ";
            $newPackageUid = database::insert($sql);
        }
        // for STUDENT package

        $sql = "UPDATE `student_package`";
        $sql.=" SET ";
        $sql.=" class_package_uid='{$newPackageUid}'";
        $sql.=" WHERE";
        $sql.=" class_package_uid='{$pUid}'";
        database::query($sql);
        // end for STUDENT package
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

}

?>