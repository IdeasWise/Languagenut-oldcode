<?php

class page_subscribe_school_stage_3_translations extends generic_object {

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
		$form3 = array(
			'username_open' => array('value' => '', 'error' => false),
			'password_open' => array('value' => '', 'error' => false)
		);

		if (count($_POST) > 0) {
			/**
			 * Capture
			 */
			$username_open = (isset($_POST['username_open']) && strlen(trim($_POST['username_open'])) > 0) ? trim($_POST['username_open']) : '';

			$password_open = (isset($_POST['password_open']) && strlen(trim($_POST['password_open'])) > 0) ? trim($_POST['password_open']) : '';

			/**
			 * Validate
			 */
			if (trim($username_open) == $_SESSION['form1']['email']['value']) {
				$errors[] = config::translate('field.openusername.match.email.error');
				$form3['username_open']['error'] = true;
			} elseif (strlen($username_open) < 5 || strlen($username_open) > 255) {
				$errors[] = config::translate('field.username.error.5-255');
				$form3['username_open']['error'] = true;
			} else {
				$userObject = new user();
				if ($userObject->username_exist($username_open)) {
					$errors[] = config::translate('field.username.error.unavailable');
					$form3['username_open']['error'] = true;
				} else {
					$form3['username_open']['value'] = $username_open;
				}
			}
			if (strlen($password_open) < 5 || strlen($password_open) > 255) {
				$errors[] = config::translate('field.password.error.5-255');
				$form3['password_open']['error'] = true;
			} else {
				$form3['password_open']['value'] = $password_open;
			}

			// process
			if (count($errors) > 0) {
				$message = '<div class="errors">';
				$message.= '<p><img src="' . config::images('problem.png') . '" alt="' . config::translate('form.invalid') . '" /></p>';
				$message.= '<p>' . config::translate('form.correct-errors') . '</p>';
				$message.= '<ul>';
				$message.= '<li>' . implode('</li><li>', $errors) . '</li>';
				$message.= '</ul>';
				$message.= '<p><a href="#" class="errorClose"><img src="' . config::images('back_to_form.png') . '" alt="' . config::translate('form.back') . '" /></a></p>';
				$message.= '</div>';

				$_SESSION['stage'] = 3;
				$_SESSION['message'] = $message;
			} else {
				$_SESSION['stage'] = 4;
			}
			$_SESSION['form3'] = $form3;
			output::redirect(config::url($redirect));
		} else {
			if (isset($_SESSION['form3'])) {
				$form3 = $_SESSION['form3'];
				unset($_SESSION['form3']);
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
			$form3
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