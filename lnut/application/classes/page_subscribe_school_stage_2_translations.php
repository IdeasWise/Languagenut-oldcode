<?php

class page_subscribe_school_stage_2_translations extends generic_object {

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

	public function subscribeValidation($redirect='subscribe/school/') {
		$message = '';
		$errors = array();
		$form2 = array(
			'school_name' => array('value' => '', 'error' => false),
			'school_address' => array('value' => '', 'error' => false),
			'school_postcode' => array('value' => '', 'error' => false)
		);
		if (count($_POST) > 0) {
			/**
			 * Capture
			 */
			$school_name = (isset($_POST['school_name']) && strlen(trim($_POST['school_name'])) > 0) ? trim($_POST['school_name']) : '';
			$school_address = (isset($_POST['school_address']) && strlen(trim($_POST['school_address'])) > 0) ? trim($_POST['school_address']) : '';
			$school_postcode = (isset($_POST['school_postcode']) && strlen(trim($_POST['school_postcode'])) > 0) ? trim($_POST['school_postcode']) : '';
			/**
			 * Validate
			 */
			if (strlen($school_name) < 5 || strlen($school_name) > 255) {
				$errors[] = config::translate('field.school_name.error.5-255');
				$form2['school_name']['error'] = true;
			} else {
				$form2['school_name']['value'] = $school_name;
			}
			if (strlen($school_address) < 5 || strlen($school_address) > 255) {
				$errors[] = config::translate('field.school_address.error.5-255');
				$form2['school_address']['error'] = true;
			} else {
				$form2['school_address']['value'] = $school_address;
			}
			if (strlen($school_postcode) < 4 || strlen($school_postcode) > 255) {
				$errors[] = config::translate('field.school_postcode.error.5-255');
				$form2['school_postcode']['error'] = true;
			} else {
				$form2['school_postcode']['value'] = $school_postcode;
			}
			/**
			 * Process
			 */
			if (count($errors) > 0) {
				$message = '<div class="errors">';
				$message.= '<p><img src="' . config::images('problem.png') . '" alt="' . config::translate('form.invalid') . '" /></p>';
				$message.= '<p>' . config::translate('form.correct-errors') . '</p>';
				$message.= '<ul>';
				$message.= '<li>' . implode('</li><li>', $errors) . '</li>';
				$message.= '</ul>';
				$message.= '<p><a href="#" class="errorClose"><img src="' . config::images('back_to_form.png') . '" alt="' . config::translate('form.back') . '" /></a></p>';
				$message.= '</div>';
				$_SESSION['stage'] = 2;
				$_SESSION['message'] = $message;
			} else {
				$_SESSION['stage'] = 3;
			}
			$_SESSION['form2'] = $form2;
			output::redirect(config::url($redirect));
		} else {
			if (isset($_SESSION['form2'])) {
				$form2 = $_SESSION['form2'];
				unset($_SESSION['form2']);
			}
		}
		/**
		 * Get any error message
		 */
		if (
				isset($_SESSION['message'])
				&& strlen($_SESSION['message']) > 0
		) {
			$message = $_SESSION['message'];
			unset($_SESSION['message']);
		}
		return array(
			$errors,
			$message,
			$form2
		);
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