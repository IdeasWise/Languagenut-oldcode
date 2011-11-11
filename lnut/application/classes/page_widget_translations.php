<?php

class page_widget_translations extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();
		$arrValues[] = array(
			"field" => "locale",
			"value" => $locale
		);

		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND locale='en'" : "";
		$groupBy = " ";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>