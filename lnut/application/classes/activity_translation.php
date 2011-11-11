<?php

class activity_translation extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public static function getTranslationByLocale($articleUid,$lUid){
		$sql="SELECT * FROM `activity_translation` ";
		$sql.=" WHERE";
		$sql.=" activity_uid='{$articleUid}'";
		$sql.=" AND language_uid='{$lUid}'";
		$sql.=" LIMIT 1";

		$data=database::arrQuery($sql);

		return (isset($data[0]))?$data[0]:false;
	}
	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();
		$arrValues[] = array(
			"field" => "locale",
			"value" => $locale
		);
		$arrValues[] = array(
			"field" => "language_uid",
			"value" => $languageUid
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND language_uid='" . $enUid . "'" : "";
		$groupBy = " GROUP BY activity_uid";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>