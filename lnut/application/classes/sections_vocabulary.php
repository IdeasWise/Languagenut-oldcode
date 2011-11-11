<?php

class sections_vocabulary extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($data = array(), $all = false) {
		$where = ' WHERE `SV`.`section_uid` = `S`.`uid` ';
		foreach ($data as $idx => $val) {
			$where .= " AND " . $idx . "='" . $val . "'";
		}
		if (!$all) {
			$query = "SELECT ";
			$query.="COUNT(`SV`.`uid`) ";
			$query.="FROM ";
			$query.="`sections_vocabulary` AS `SV`, ";
			$query.="`sections` AS `S` ";
			$query.= $where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.="`SV`.*, ";
		$query.="`S`.`name` AS `SectionName` ";
		$query.="FROM ";
		$query.="`sections_vocabulary` AS `SV`, ";
		$query.="`sections` AS `S` ";
		$query.=$where . " ";
		$query.="ORDER BY `SV`.`name` ";
		if (!all) {
			$query.=" LIMIT " . $this->get_limit();
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
		$section_uid = (isset($_POST['section_uid'])) ? $_POST['section_uid'] : '0';
		$active = (isset($_POST['active'])) ? $_POST['active'] : 0;
		$arrMessages = array();
		if (trim(strlen($name)) < 3 || trim(strlen($name)) > 255) {
			$arrMessages['error_name'] = "Section translation must be 3 to 255 characters in length.";
		} else if (!validation::isValid('text', $name)) {
			$arrMessages['error_name'] = "Please enter valid section name.";
		}
		if (trim($section_uid) == '' || trim($section_uid) == "0") {
			$arrMessages['error_section_uid'] = "Please choose section.";
		} else if (!validation::isValid('int', $section_uid)) {
			$arrMessages['error_section_uid'] = "Please choose valid section.";
		}
		if (!validation::isValid('int', $active)) {
			$arrMessages['error_active'] = "Please choose valid section active option.";
		}
		if (count($arrMessages) == 0) {
			$this->set_name($name);
			$this->set_section_uid($section_uid);
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

	public function SectionsVocabularyTranslationsList($term_uid=null) {
		$arrRows = array();
		if (is_numeric($term_uid) && $term_uid > 0) {
			$query = "SELECT ";
			$query.="`ST`.*, ";
			$query.="`L`.`name` AS `language` ";
			$query.="FROM ";
			$query.="`language` AS `L`, ";
			$query.="`sections_vocabulary_translations` AS `ST` ";
			$query.="WHERE ";
			$query.="`L`.`uid` = `ST`.`language_id` ";
			$query.="AND ";
			$query.="`term_uid` = '" . $term_uid . "'";
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

	public function getIdNameArray($section_id=null) {
		$arrTerms = array();
		$arrTermIds = array();
		if (is_numeric($section_id) && $section_id > 0) {
			$query = "SELECT ";
			$query.= "`uid`, ";
			$query.= "`name` ";
			$query.= "FROM ";
			$query.= "`sections_vocabulary` ";
			$query.= "WHERE ";
			$query.= "`section_uid`=" . $section_id;
			$result = database::query($query);
			if ($result) {
				if (mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_assoc($result)) {
						$arrTerms[$row['uid']] = array('term' => stripslashes($row['name']));
						$arrTermIds[] = $row['uid'];
					}
				}
			}
		}
		return array($arrTerms, $arrTermIds);
	}

	public function getVocabTranslation($section_uid=null) {
		if (is_numeric($section_uid) && $section_uid > 0) {
			$query = "SELECT ";
			$query.="`uid`, ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`sections_vocabulary` ";
			$query.="WHERE ";
			$query.="`section_uid`='" . $section_uid . "' ";
			$query.="ORDER BY ";
			$query.="`name` ASC ";
			return database::query($query);
		}
	}

}

?>