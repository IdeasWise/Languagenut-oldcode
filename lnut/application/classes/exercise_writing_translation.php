<?php

class exercise_writing_translation extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();
		
		$arrValues[] = array(
			"field" => "language_uid",
			"value" => $languageUid
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND language_uid='" . $enUid . "'" : "";
		$groupBy = " GROUP BY exercise_writing_uid";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>