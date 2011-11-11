<?php

class currencies extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function CurrencySelectBox($inputName, $selctedValue = null, $ID = null) {
		$query = "SELECT ";
		$query.="`uid`, ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`currencies` ";
		$query.="ORDER BY ";
		$query.="`name` ASC";
		$result = database::query($query);
		$data = array();
		$data[0] = 'Currency';
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$data[$row['uid']] = $row['name'];
			}
		}
		return format::to_select(array("name" => $inputName, "id" => $ID, "style" => "width:180px;", "options_only" => false), $data, $selctedValue);
	}

	public function getList($data = array(), $OrderBy = "name ", $all = false) {
		$where = ' WHERE 1 = 1';
		foreach ($data as $idx => $val) {
			$where .= " AND " . $idx . "='" . $val . "'";
		}
		if (!$all) {
			$query = "SELECT ";
			$query.="COUNT(`uid`) ";
			$query.="FROM ";
			$query.="`currencies` ";
			$query.=$where;
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`currencies` ";
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
				'maxChar' => 100,
				'errMinMax' => 'Currency name must be 2 to 100 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid currency name.',
				'errIndex' => 'error_name'
			),
			'symbol' => array(
				'value' => (isset($_POST['symbol'])) ? trim($_POST['symbol']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 1,
				'maxChar' => 30,
				'errMinMax' => 'Currency symbol must be 2 to 30 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid currency symbol.',
				'errIndex' => 'error_symbol'
			),
			'position' => array(
				'value' => (isset($_POST['position'])) ? trim($_POST['position']) : 'before',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please choose valid position.',
				'errIndex' => 'error_symbol'
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			$this->set_name($arrFields['name']['value']);
			$this->set_symbol($arrFields['symbol']['value']);
			$this->set_position($arrFields['position']['value']);
			return true;
		} else {
			return false;
		}
	}

	public function getPriceAndCurrency($PriceFor='') {
		$priceArray = array();
		$locale = config::get('locale');
		$objLanguage = new language();
		$where = array('prefix' => $locale);
		$objLanguage->load(array(), $where);
		$uid = 0;
		$uid = $objLanguage->TableData['currency_uid']['Value'];
		if (is_numeric($uid) && $uid > 0) {
			parent::__construct($uid, __CLASS__);
			$this->load();
			$priceArray['name'] = $this->TableData['name']['Value'];
			$priceArray['vat'] = $objLanguage->TableData['vat']['Value'];
			if ($PriceFor == 'homeuser') {
				$priceArray['price'] = $objLanguage->TableData['home_user_price']['Value'];
			}
			if ($PriceFor == 'school') {
				$priceArray['price'] = $objLanguage->TableData['school_price']['Value'];
			}
			$priceArray['price_format'] = '';
			if ($this->TableData['position']['Value'] == 'before') {
				$priceArray['price_format'] = $this->TableData['symbol']['Value'];
			}
			$priceArray['price_format'] .= $priceArray['price'];
			if ($this->TableData['position']['Value'] == 'after') {
				$priceArray['price_format'] .= $this->TableData['symbol']['Value'];
			}
		}
		return $priceArray;
	}

	public function getCurrencyFormat($locale='', $price='') {
		$price_format = '';
		$objLanguage = new language();
		$where = array('prefix' => $locale);
		$objLanguage->load(array(), $where);
		$uid = 0;
		$uid = $objLanguage->TableData['currency_uid']['Value'];
		if (is_numeric($uid) && $uid > 0) {
			parent::__construct($uid, __CLASS__);
			$this->load();
			if ($this->TableData['position']['Value'] == 'before') {
				$price_format = $this->TableData['symbol']['Value'];
			}
			$price_format .= $price;
			if ($this->TableData['position']['Value'] == 'after') {
				$price_format .= $this->TableData['symbol']['Value'];
			}
		}
		return $price_format;
	}

	public function getCurrencySymbol($locale='') {
		$price_format = '';
		$objLanguage = new language();
		$where = array('prefix' => $locale);
		$objLanguage->load(array(), $where);
		$uid = 0;
		$uid = $objLanguage->TableData['currency_uid']['Value'];
		if (is_numeric($uid) && $uid > 0) {
			parent::__construct($uid, __CLASS__);
			$this->load();
			return $this->get_symbol();
		}
		return null;
	}

}

?>