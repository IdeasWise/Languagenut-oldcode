<?php

class sections extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function SectionList($unit_uid = null, $OrderBy = 'section_number') {
		$where = '';
		if ($unit_uid != null) {
			$where = " and `unit_uid` = '" . $unit_uid . "'";
		}
		$query = "SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`sections` ";
		$query.="WHERE ";
		$query.="1 = 1 ";
		$query.=$where;
		$query.=" ORDER BY ";
		$query.=$OrderBy;
		return database::arrQuery($query);
	}

	public function getList($data = array(), $all = false) {
		$parts = config::get('paths');
		$where = ' WHERE `S`.`unit_uid` = `U`.`uid` ';
		foreach ($data as $idx => $val) {
			$where .= " AND " . $idx . "='" . $val . "'";
		}
		if (!$all) {
			$query = 'SELECT ';
			$query.= 'COUNT(`S`.`uid`) ';
			$query.= 'FROM ';
			$query.= '`units` AS `U` ';
			$query.= ",`sections` AS `S` ";
			$query.=$where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.= "`S`.*, ";
		$query.= "`U`.`name` AS `UnitName` ";
		$query.= "FROM ";
		$query.= '`units` AS `U` ';
		$query.= ",`sections` AS `S` ";
		$query.=$where;
		$query.= "ORDER BY `S`.`name`";
		if (!$all) {
			$query.= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function yearTranslationsList($year_id=null) {
		$arrRows = array();
		if (is_numeric($year_id) && $year_id > 0) {
			$query = "SELECT ";
			$query.="`YT`.*, ";
			$query.="`L`.`name` AS `language` ";
			$query.="FROM ";
			$query.="`language` AS `L`, ";
			$query.="`years_translations` AS `YT` ";
			$query.="WHERE ";
			$query.="`L`.`uid` = `YT`.`language_id` ";
			$query.="AND ";
			$query.="`year_id` = '" . $year_id . "'";
			$result = database::query($query);
			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					if ($row['active']) {
						$row['active_yes_no'] = 'Yes';
					} else {
						$row['active_yes_no'] = 'No';
					}
					$arrRows[] = $row;
				}
			}
		}
		return $arrRows;
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

	public function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$name = (isset($_POST['name'])) ? $_POST['name'] : '';
		$unit_uid = (isset($_POST['unit_uid'])) ? $_POST['unit_uid'] : '0';
		$active = (isset($_POST['active'])) ? $_POST['active'] : 0;
		$arrMessages = array();
		if (trim(strlen($name)) < 3 || trim(strlen($name)) > 255) {
			$arrMessages['error_name'] = "Section name must be 3 to 255 characters in length.";
		} else if (!validation::isValid('text', $name)) {
			$arrMessages['error_name'] = "Please enter valid section name.";
		}
		if ($unit_uid == "0") {
			$arrMessages['error_unit_uid'] = "Please choose unit.";
		} else if (!validation::isValid('int', $unit_uid)) {
			$arrMessages['error_unit_uid'] = "Please choose valid unit.";
		}
		if (!validation::isValid('int', $active)) {
			$arrMessages['error_active'] = "Please choose valid section active option.";
		}
		if (count($arrMessages) == 0) {
			$this->set_name($name);
			$this->set_unit_uid($unit_uid);
			$this->set_active($active);
		} else {
			$strMessage = '';
			foreach ($arrMessages as $index => $value) {
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>' . $value . '</li>';
			}
			$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $strMessage . '</ul>';
		}
		foreach ($_POST as $index => $value) {
			$this->arrForm[$index] = $value;
		}
		if (count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}
	}

	public function sectionTranslationsList($section_uid=null) {
		$arrRows = array();
		if (is_numeric($section_uid) && $section_uid > 0) {
			$query = "SELECT ";
			$query.="`ST`.* ";
			$query.="`L`.`name` AS `language` ";
			$query.="FROM ";
			$query.="`language` AS `L`, ";
			$query.="`sections_translations` AS `ST` ";
			$query.="WHERE ";
			$query.="`L`.`uid` = `ST`.`language_id` ";
			$query.="AND ";
			$query.="`section_uid` = '" . $section_uid . "' ";
			$result = database::query($query);
			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					if ($row['active']) {
						$row['active_yes_no'] = 'Yes';
					} else {
						$row['active_yes_no'] = 'No';
					}
					$arrRows[] = $row;
				}
			}
		}
		return $arrRows;
	}

	public function SectionSelectBox($inputName="section_uid", $selctedValue = NULL) {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`sections` ";
		$query.="ORDER BY ";
		$query.="`name` ";
		$result = database::query($sql);
		$data = array();
		$data[0] = 'Section Name';
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$data[$row['uid']] = $row['name'];
			}
		}
		return format::to_select(array("name" => $inputName, "id" => $inputName, "options_only" => false), $data, $selctedValue);
	}

	public function getSectionTranslations($language_id, $unit_ids) {
		$sections = array();
		$query = "SELECT ";
		$query.="`st`.`section_uid`, ";
		$query.="`st`.`name`, ";
		$query.="`sections`.`unit_uid` ";
		$query.="FROM ";
		$query.="`sections`, ";
		$query.="`sections_translations` AS `st` ";
		$query.="WHERE ";
		$query.="`st`.`language_id`=$language_id ";
		$query.="AND `st`.`section_uid`=`sections`.`uid` ";
		$query.="AND `sections`.`unit_uid` IN (" . implode(',', $unit_ids) . ") ";
		$query.="ORDER BY ";
		$query.="`st`.`section_uid` ASC";
		$result = database::query($query);
		if ($result && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$sections[$row['section_uid']] = array(
					'unit_id' => $row['unit_uid'],
					'name' => stripslashes($row['name'])
				);
			}
		} else {
			$query = "SELECT ";
			$query.="`st`.`section_uid`, ";
			$query.="`st`.`name`, ";
			$query.="`sections`.`unit_uid` ";
			$query.="FROM ";
			$query.="`sections`, ";
			$query.="`sections_translations` AS `st` ";
			$query.="WHERE ";
			$query.="`st`.`language_id`=14 ";
			$query.="AND `st`.`section_uid`=`sections`.`uid` ";
			$query.="AND `sections`.`unit_uid` IN (" . implode(',', $unit_ids) . ") ";
			$query.="ORDER BY ";
			$query.="`st`.`section_uid` ASC";
			$result = database::query($query);
			if ($result && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$sections[$row['section_uid']] = array(
						'unit_id' => $row['unit_uid'],
						'name' => stripslashes($row['name'])
					);
				}
			}
		}
		return $sections;
	}

	// used on printable controller
	public function getSectionUnitandId($section_uid=null) {
		$row = array();
		$query = "SELECT ";
		$query.="`units`.`uid`, ";
		$query.="`sections`.`name` AS `section_name`, ";
		$query.="`units`.`name` AS `unit_name` ";
		$query.="FROM ";
		$query.="`sections`, ";
		$query.="`units` ";
		$query.="WHERE ";
		$query.="`sections`.`unit_uid`=`units`.`uid` ";
		$query.="AND ";
		$query.="`sections`.`uid`=$section_uid";
		$result = database::query($query);
		if ($result) {
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
			}
		}
		return $row;
	}

	public function getFilteredSections($unit_uid=null,$arrFilter=array()) {
		$query = "SELECT ";
		$query.="`uid`,";
		$query.="`name`,";
		$query.="`section_number` ";
		$query.="FROM ";
		$query.="`sections` ";
		$query.="WHERE ";
		$query.="`active` = '1' ";
		$query.="AND ";
		$query.="`unit_uid` = '" . $unit_uid . "' ";		
		if(count($arrFilter)) {
			$query.="AND ";
			$query.="`uid` IN (".implode(',',$arrFilter).") ";
		}
		$query.="ORDER BY `section_number` ";
		return database::arrQuery($query);
	}

}

?>