<?php

class page_subscribe_select_translations extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {

		parent::__construct($uid, __CLASS__);
	}

	public function doSave() {


		$response = $this->isValidate();

		if (count($response) == 0) {
			$this->save();
		} else {
			$msg = NULL;
			foreach ($response as $idx => $val) {
				$this->arrForm[$idx] = 'label_error';
				$msg .= '<li>' . $val . '</li>';
			}
			if ($msg != NULL) {
				$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $msg . '</ul>';
			}
		}
		if (count($response) > 0) {
			return false;
		} else {
			return true;
		}
	}

	public function isValidate() {
		
	}

	public function getByLocale($locale='en') {
		$response = false;
		$query = "SELECT ";
		$query.= "`background_url`, ";
		$query.= "`title_url`, ";
		$query.= "`title_alt`, ";
		$query.= "`intro_text`, ";
		$query.= "`select_school`, ";
		$query.= "`select_homeuser` ";
		$query.= "FROM ";
		$query.= "`page_subscribe_select_translations` ";
		$query.= "WHERE ";
		$query.= "`locale`='" . mysql_real_escape_string($locale) . "' ";
		$query.= "LIMIT 1";
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$response = mysql_fetch_assoc($result);
		}
		return $response;
	}

	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();
		$arrValues[] = array(
			"field" => "locale",
			"value" => $locale
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND locale='en'" : "";
		$groupBy = " LIMIT 1";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>