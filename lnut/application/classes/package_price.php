<?php
class package_price extends generic_object {
	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}
	public function isValidPriceandVat() {
		$arrPrice	= array();
		$arrVat		= array();
		if(isset($_POST['price']) && is_array($_POST['price'])) {
			foreach($_POST['price'] as $index => $value) {
				if(trim($value)!='' && (!is_numeric($value) || strlen(trim($value))>11)) {
					$arrPrice[] = '<i><b>'.$index.'</b></i>';
				}
			}
		}
		if(isset($_POST['vat']) && is_array($_POST['vat'])) {
			foreach($_POST['vat'] as $index => $value) {
				if(trim($value)!='' && (!is_numeric($value) || strlen(trim($value))>5)) {
					$arrVat[] = '<i><b>'.$index.'</b></i>';
				}
			}
		}
		return array(
			'arrPrice'	=>$arrPrice,
			'arrVat'	=>$arrVat
		);
	}
	public function SavePackagePriceandVat($package_uid=null) {
		if($package_uid!=null && isset($_POST['price']) && is_array($_POST['price'])) {
			foreach($_POST['price'] as $index => $value) {
				$query ="SELECT ";
				$query.="`uid` ";
				$query.="FROM ";
				$query.="`package_price` ";
				$query.="WHERE ";
				$query.="`package_uid` = '".mysql_real_escape_string($package_uid)."' ";
				$query.="AND ";
				$query.="`locale`='".mysql_real_escape_string($index)."' ";
				$query.="LIMIT 1";
				$result = database::query($query);
				if(mysql_error() == '' && mysql_num_rows($result)) {
					$row = mysql_fetch_array($result);
					parent::__construct($row['uid'],__CLASS__);
					$this->load();
					$this->set_price($value);
					if(isset($_POST['vat'][$index])) {
						$this->set_vat($_POST['vat'][$index]);
					}
					$this->save();
				} else {
					$this->set_price($value);
					$this->set_package_uid($package_uid);
					if(isset($_POST['vat'][$index])) {
						$this->set_vat($_POST['vat'][$index]);
					}
					$this->set_locale($index);
					$this->insert();
				}
			}
		}
	}
	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();
		$arrValues[] = array(
			"field" => "locale",
			"value" => $locale
		);
		$arrValues[] = array(
			"field" => "price",
			"value" => 0.00
		);
		$arrValues[] = array(
			"field" => "vat",
			"value" => 0.00
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND locale='en'" : "";
		$groupBy = " GROUP BY package_uid";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues,$groupBy);
	}
}
?>