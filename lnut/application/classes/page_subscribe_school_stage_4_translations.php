<?php

class page_subscribe_school_stage_4_translations extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {

		parent::__construct($uid, __CLASS__);
	}

	public function doSave() {
		if ($this->isValidateFormData() == true) {
			$this->save(array(),1);
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
			'background_url' => array(
				'value' => (isset($_POST['background_url'])) ? trim($_POST['background_url']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 255,
				'errMinMax' => 'Background url must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid background url.',
				'errIndex' => 'error.background_url'
			),
			'title_url' => array(
				'value' => (isset($_POST['title_url'])) ? trim($_POST['title_url']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 255,
				'errMinMax' => 'Title url must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid title url.',
				'errIndex' => 'error.title_url'
			),
			'title_alt' => array(
				'value' => (isset($_POST['title_alt'])) ? trim($_POST['title_alt']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 255,
				'errMinMax' => 'Title alternate text must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid title alternate text.',
				'errIndex' => 'error.title_alt'
			),
			'intro_text' => array(
				'value' => (isset($_POST['intro_text'])) ? trim($_POST['intro_text']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter introduction text.',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid introduction text.',
				'errIndex' => 'error.intro_text'
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			$this->set_background_url($arrFields['background_url']['value']);
			$this->set_title_url($arrFields['title_url']['value']);
			$this->set_title_alt($arrFields['title_alt']['value']);
			$this->set_intro_text($arrFields['intro_text']['value']);
			return true;
		} else {
			return false;
		}
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