<?php

class page_subscribe_school_stages_translations extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function doSave() {
		if ($this->isValidateFormData() === true) {
			$this->save(array(), 1);
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
			),
			'recaptcha_text' => array(
				'value' => (isset($_POST['recaptcha_text'])) ? trim($_POST['recaptcha_text']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 255,
				'errMinMax' => 'Recaptcha text must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid Recaptcha text.',
				'errIndex' => 'error.recaptcha_text'
			),
			'promo_text' => array(
				'value' => (isset($_POST['promo_text'])) ? trim($_POST['promo_text']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 32,
				'errMinMax' => 'Promo text must be 5 to 32 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid promo text.',
				'errIndex' => 'error.promo_text'
			),
			'accept_terms' => array(
				'value' => (isset($_POST['accept_terms'])) ? trim($_POST['accept_terms']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 255,
				'errMinMax' => 'Accept terms text must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid accept terms text.',
				'errIndex' => 'error.accept_terms'
			),
			'send_updates' => array(
				'value' => (isset($_POST['send_updates'])) ? trim($_POST['send_updates']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 64,
				'errMinMax' => 'Send update text must be 5 to 64 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid send update text.',
				'errIndex' => 'error.send_updates'
			),
			'emails_not_to_3rd_party' => array(
				'value' => (isset($_POST['emails_not_to_3rd_party'])) ? trim($_POST['emails_not_to_3rd_party']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 64,
				'errMinMax' => 'Email security text must be 5 to 64 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid email security text.',
				'errIndex' => 'error.emails_not_to_3rd_party'
			),
			'sub_price' => array(
				'value' => (isset($_POST['sub_price'])) ? trim($_POST['sub_price']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter subscription price.',
				'minChar' => 1,
				'maxChar' => 7,
				'errMinMax' => 'Subscription price text must be 1 to 7 characters in length.',
				'dataType' => 'int',
				'errdataType' => 'Please enter valid subscription price.',
				'errIndex' => 'error.sub_price'
			),
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			$this->set_background_url($arrFields['background_url']['value']);
			$this->set_title_url($arrFields['title_url']['value']);
			$this->set_title_alt($arrFields['title_alt']['value']);
			$this->set_intro_text($arrFields['intro_text']['value']);
			$this->set_recaptcha_text($arrFields['recaptcha_text']['value']);
			$this->set_promo_text($arrFields['promo_text']['value']);
			$this->set_accept_terms($arrFields['accept_terms']['value']);
			$this->set_send_updates($arrFields['send_updates']['value']);
			$this->set_emails_not_to_3rd_party($arrFields['emails_not_to_3rd_party']['value']);
			$this->set_sub_price($arrFields['sub_price']['value']);
			return true;
		} else {
			return false;
		}
	}

	public function subscribeValidation() {

		$recaptcha = new component_recaptchalib();
		$message = '';
		$errors = array();
		/**
		 * Set up Defaults for the Stage 1 Data Capture Form
		 */
		$form1 = array(
			'name' => array('value' => '', 'error' => false),
			'phone_number' => array('value' => '', 'error' => false),
			'email' => array('value' => '', 'error' => false),
			'password1' => array('value' => '', 'error' => false),
			'password2' => array('value' => '', 'error' => false),
			'recaptcha' => array('value' => '', 'error' => false),
			'terms' => array('value' => '', 'error' => false),
			'optin' => array('value' => '', 'error' => false),
			'promo_code' => array('value' => '')
		);
		$form2 = array(
			'school_name' => array('value' => '', 'error' => false),
			'school_address' => array('value' => '', 'error' => false),
			'school_postcode' => array('value' => '', 'error' => false)
		);
		$form3 = array(
			'username_open' => array('value' => '', 'error' => false),
			'password_open' => array('value' => '', 'error' => false)
		);
		if (count($_POST) > 0) {
			/**
			 * Capture
			 */
			$name = (isset($_POST['name']) && strlen(trim($_POST['name'])) > 0) ? trim($_POST['name']) : '';
			$phone_number = (isset($_POST['phone_number']) && strlen(preg_replace('/[^\d]/', '', $_POST['phone_number'])) > 0) ? preg_replace('/[^\d]/', '', $_POST['phone_number']) : '';
			$email = (isset($_POST['email']) && strlen(trim($_POST['email'])) > 0) ? trim($_POST['email']) : '';
			$password1 = (isset($_POST['password1']) && strlen(trim($_POST['password1'])) > 0) ? $_POST['password1'] : '';
			$password2 = (isset($_POST['password2']) && strlen(trim($_POST['password2'])) > 0) ? $_POST['password2'] : '';
			$terms = (isset($_POST['accept_terms']) && $_POST['accept_terms'] == 'yes') ? true : false;
			$optin = (isset($_POST['optin']) && $_POST['optin'] == 'yes') ? true : false;
			$promo_code = (isset($_POST['promo_code']) && strlen(trim($_POST['promo_code'])) > 0) ? strtoupper(trim($_POST['promo_code'])) : '';
			$school_name = (isset($_POST['school_name']) && strlen(trim($_POST['school_name'])) > 0) ? trim($_POST['school_name']) : '';
			$school_address = (isset($_POST['school_address']) && strlen(trim($_POST['school_address'])) > 0) ? trim($_POST['school_address']) : '';
			$school_postcode = (isset($_POST['school_postcode']) && strlen(trim($_POST['school_postcode'])) > 0) ? trim($_POST['school_postcode']) : '';
			$username_open = (isset($_POST['username_open']) && strlen(trim($_POST['username_open'])) > 0) ? trim($_POST['username_open']) : '';
			$password_open = (isset($_POST['password_open']) && strlen(trim($_POST['password_open'])) > 0) ? trim($_POST['password_open']) : '';

			$reseller_code_uid = (isset($_POST['reseller_code_uid']) ? (int)$_POST['reseller_code_uid'] : 0);

			/**
			 * Validate
			 */
			if (strlen($name) < 5 || strlen($name) > 255) {
				$errors[] = config::translate('field.name.error.5-255');
				$form1['name']['error'] = true;
			} else {
				$form1['name']['value'] = $name;
			}
			if (strlen($phone_number) < 8 || strlen($phone_number) > 20) {
				$errors[] = config::translate('field.phone_number.error.8-20');
				$form1['phone_number']['error'] = true;
			} else {
				$form1['phone_number']['value'] = $phone_number;
			}
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errors[] = config::translate('field.email.error.invalid');
				$form1['email']['error'] = true;
			} else {
				$userObject = new user();
				if ($userObject->email_exist($email)) {
					$errors[] = config::translate('field.username.error.taken');
					$form1['email']['error'] = true;
				} else {
					$form1['email']['value'] = $email;
				}
			}
			if (strlen($password1) < 5 || strlen($password1) > 255) {
				$errors[] = config::translate('field.password.error.5-255');
				$form1['password1']['error'] = true;
			}
			if ($password1 == '' || $password1 != $password2) {
				$errors[] = config::translate('field.password.error.missing-or-mismatch');
				$form1['password1']['error'] = true;
				$form1['password2']['error'] = true;
			}
			if (!$terms) {
				$errors[] = config::translate('field.terms.error.not-selected');
				$form1['terms']['error'] = true;
			} else {
				$form1['terms']['value'] = true;
			}
			$form1['optin']['value'] = $optin;
			if (strlen($promo_code) > 0) {
				if (strlen($promo_code) < 3 || strlen($promo_code) > 32) {
					$errors[] = config::translate('field.promo_code.error.invalid-format');
					$form1['promo_code']['error'] = true;
				} else {
					$form1['promo_code']['value'] = $promo_code;
				}
			}
			$form1['reseller_code_uid']['value'] = $reseller_code_uid;
			$form1['reseller_code_uid']['error'] = false;
//			 form 2

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

//			end form 2
//			form3
//
			if (isset($_SESSION['form1']['email']['value']) && trim($username_open) == $_SESSION['form1']['email']['value']) {
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
//
//			end form3
			$recaptcha_response = $recaptcha->recaptcha_check_answer(
							null, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]
			);
			if (!$recaptcha_response['is_valid']) {
				$errors[] = str_replace('{error}', $recaptcha_response['error'], config::translate('field.recaptcha.error.invalid'));
			}
			//mail('workstation@mystream.co.uk','dump',print_r($form,true),'From: developer@languagenut.com');
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
				$_SESSION['stage'] = 1;
				$_SESSION['message'] = $message;
			} else if ($_POST["signtype"] == '1') {
				$form1['password']['value'] = $password1;
				$_SESSION['stage'] = 'paymentoption';
			} else {
				$form1['password']['value'] = $password1;
				$_SESSION['stage'] = 4;
			}

			$_SESSION['form1'] = $form1;
			$_SESSION['form2'] = $form2;
			$_SESSION['form3'] = $form3;

			if ($_POST["signtype"] == '1') {
				output::redirect(config::url('subscribe/schoolsubscribe/'));
			}

			output::redirect(config::url('subscribe/school/'));
		} else {
			if (isset($_SESSION['form1'])) {
				$form1 = $_SESSION['form1'];
				unset($_SESSION['form1']);
			}
			if (isset($_SESSION['form2'])) {
				$form2 = $_SESSION['form2'];
				unset($_SESSION['form2']);
			}
			if (isset($_SESSION['form3'])) {
				$form3 = $_SESSION['form3'];
				unset($_SESSION['form3']);
			}
		}
		$form = array_merge($form1, $form2, $form3);
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
			$form
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