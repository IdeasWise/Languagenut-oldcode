<?php

class page_subscribe_homeuser_stage_1_translations extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function doSave() {

		if($this->isValidateFormData() === true) {
			$this->save(array(),1);
			return true;
		} else {
			return false;
		}
	}

	private function isValidateFormData() {
		if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'],__CLASS__);
			$this->load();
		}
		$arrFields = array(
				'background_url'=>array(
						'value'			=> (isset($_POST['background_url']))?trim($_POST['background_url']):'',
						'checkEmpty'	=> false,
						'errEmpty'		=> '',
						'minChar'		=> 5,
						'maxChar'		=> 255,
						'errMinMax'		=> 'Background url must be 5 to 255 characters in length.',
						'dataType'		=> 'text',
						'errdataType'	=> 'Please enter valid background url.',
						'errIndex'		=> 'error.background_url'
				),
				'title_url'=>array(
						'value'			=> (isset($_POST['title_url']))?trim($_POST['title_url']):'',
						'checkEmpty'	=> false,
						'errEmpty'		=> '',
						'minChar'		=> 5,
						'maxChar'		=> 255,
						'errMinMax'		=> 'Title url must be 5 to 255 characters in length.',
						'dataType'		=> 'text',
						'errdataType'	=> 'Please enter valid title url.',
						'errIndex'		=> 'error.title_url'
				),
				'title_alt'=>array(
						'value'			=> (isset($_POST['title_alt']))?trim($_POST['title_alt']):'',
						'checkEmpty'	=> false,
						'errEmpty'		=> '',
						'minChar'		=> 5,
						'maxChar'		=> 255,
						'errMinMax'		=> 'Title alternate text must be 5 to 255 characters in length.',
						'dataType'		=> 'text',
						'errdataType'	=> 'Please enter valid title alternate text.',
						'errIndex'		=> 'error.title_alt'
				),
				'intro_text'=>array(
						'value'			=> (isset($_POST['intro_text']))?trim($_POST['intro_text']):'',
						'checkEmpty'	=> true,
						'errEmpty'		=> 'Please enter introduction text.',
						'minChar'		=> 0,
						'maxChar'		=> 0,
						'errMinMax'		=> '',
						'dataType'		=> 'text',
						'errdataType'	=> 'Please enter valid introduction text.',
						'errIndex'		=> 'error.intro_text'
				),
				'recaptcha_text'=>array(
						'value'			=> (isset($_POST['recaptcha_text']))?trim($_POST['recaptcha_text']):'',
						'checkEmpty'	=> false,
						'errEmpty'		=> '',
						'minChar'		=> 5,
						'maxChar'		=> 255,
						'errMinMax'		=> 'Recaptcha text must be 5 to 255 characters in length.',
						'dataType'		=> 'text',
						'errdataType'	=> 'Please enter valid Recaptcha text.',
						'errIndex'		=> 'error.recaptcha_text'
				),
				'promo_text'=>array(
						'value'			=> (isset($_POST['promo_text']))?trim($_POST['promo_text']):'',
						'checkEmpty'	=> false,
						'errEmpty'		=> '',
						'minChar'		=> 5,
						'maxChar'		=> 32,
						'errMinMax'		=> 'Promo text must be 5 to 32 characters in length.',
						'dataType'		=> 'text',
						'errdataType'	=> 'Please enter valid promo text.',
						'errIndex'		=> 'error.promo_text'
				),
				'accept_terms'=>array(
						'value'			=> (isset($_POST['accept_terms']))?trim($_POST['accept_terms']):'',
						'checkEmpty'	=> false,
						'errEmpty'		=> '',
						'minChar'		=> 5,
						'maxChar'		=> 255,
						'errMinMax'		=> 'Accept terms text must be 5 to 255 characters in length.',
						'dataType'		=> 'text',
						'errdataType'	=> 'Please enter valid accept terms text.',
						'errIndex'		=> 'error.accept_terms'
				),
				'send_updates'=>array(
						'value'			=> (isset($_POST['send_updates']))?trim($_POST['send_updates']):'',
						'checkEmpty'	=> false,
						'errEmpty'		=> '',
						'minChar'		=> 5,
						'maxChar'		=> 64,
						'errMinMax'		=> 'Send update text must be 5 to 64 characters in length.',
						'dataType'		=> 'text',
						'errdataType'	=> 'Please enter valid send update text.',
						'errIndex'		=> 'error.send_updates'
				),
				'emails_not_to_3rd_party'=>array(
						'value'			=> (isset($_POST['emails_not_to_3rd_party']))?trim($_POST['emails_not_to_3rd_party']):'',
						'checkEmpty'	=> false,
						'errEmpty'		=> '',
						'minChar'		=> 5,
						'maxChar'		=> 64,
						'errMinMax'		=> 'Email security text must be 5 to 64 characters in length.',
						'dataType'		=> 'text',
						'errdataType'	=> 'Please enter valid email security text.',
						'errIndex'		=> 'error.emails_not_to_3rd_party'
				),
				'sub_price'=>array(
						'value'			=> (isset($_POST['sub_price']))?trim($_POST['sub_price']):'',
						'checkEmpty'	=> true,
						'errEmpty'		=> 'Please enter subscription price.',
						'minChar'		=> 1,
						'maxChar'		=> 7,
						'errMinMax'		=> 'Subscription price text must be 1 to 7 characters in length.',
						'dataType'		=> 'int',
						'errdataType'	=> 'Please enter valid subscription price.',
						'errIndex'		=> 'error.sub_price'
				),

			);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if($this->isValidarrFields($arrFields,$this) === true) {
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



	public function subscribeValidation($parantObj = null){

		$query		= '';
		$recaptcha	= new component_recaptchalib();
		$message	= '';
		$errors		= array ();

		$form1['name']['value']			= '';
		$form1['name']['error']			= false;
		$form1['phone_number']['value']	= '';
		$form1['phone_number']['error']	= false;
		$form1['email']['value']		= '';
		$form1['email']['error']		= false;
		$form1['password1']['value']	= '';
		$form1['password1']['error']	= false;
		$form1['password2']['value']	= '';
		$form1['password2']['error']	= false;
		$form1['recaptcha']['value']	= '';
		$form1['recaptcha']['error']	= false;
		$form1['terms']['value']		= '';
		$form1['terms']['error']		= false;
		$form1['optin']['value']		= '';
		$form1['promo_code']['value']	= '';

		if(count($_POST) > 0) {
			// capture
			$name			= (isset($_POST['name']) && strlen(trim($_POST['name'])) > 0) ? trim($_POST['name']) : '';
			$phone_number	= (isset($_POST['phone_number']) && strlen(preg_replace('/[^\d]/','',$_POST['phone_number'])) > 0) ? preg_replace('/[^\d]/','',$_POST['phone_number']) : '';
			$email			= (isset($_POST['email']) && strlen(trim($_POST['email'])) > 0) ? trim($_POST['email']) : '';
			$password1		= (isset($_POST['password1']) && strlen(trim($_POST['password1'])) > 0) ? $_POST['password1'] : '';
			$password2		= (isset($_POST['password2']) && strlen(trim($_POST['password2'])) > 0) ? $_POST['password2'] : '';
			$terms			= (isset($_POST['accept_terms']) && $_POST['accept_terms']=='yes') ? true : false;
			$optin			= (isset($_POST['optin']) && $_POST['optin']=='yes') ? true : false;
			$promo_code		= (isset($_POST['promo_code']) && strlen(trim($_POST['promo_code'])) > 0) ? strtoupper(trim($_POST['promo_code'])) : '';
			$product_uid = (isset($_POST['product_uid']) && $_POST['product_uid'] > 0) ? $_POST['product_uid'] : 0;

			// validate
			#if($product_uid == 0) {
			#	$errors[] = config::translate('field.product.select.error');
			#	$form1['product_uid']['error'] = true;
			#} else {
			#	$form1['product_uid']['value'] = $product_uid;
			#}

			if(strlen($name) < 5 || strlen($name) > 255) {
				$errors[] = config::translate('field.name.error.5-255');
					$form1['name']['error'] = true;
			} else {
					$form1['name']['value'] = $name;
			}
			if(strlen($phone_number) < 8 || strlen($phone_number) > 20) {
					$errors[] = config::translate('field.phone_number.error.8-20');
					$form1['phone_number']['error'] = true;
			} else {
				$form1['phone_number']['value'] = $phone_number;
			}
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errors[] = config::translate('field.email.error.invalid');
				$form1['email']['error'] = true;
			} else {
				$userObject = new user();
				if($userObject->email_exist($email)) {
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
			if($password1=='' || $password1 != $password2) {
				$errors[] = config::translate('field.password.error.missing-or-mismatch');
				$form1['password1']['error'] = true;
				$form1['password2']['error'] = true;
			} else {
				$form1['password']['value'] = $password1;
			}
			if(!$terms) {
				$errors[] = config::translate('field.terms.error.not-selected');
				$form1['terms']['error'] = true;
			} else {
				$form1['terms']['value'] = true;
			}

			if(strlen($promo_code) > 0) {
				if(strlen($promo_code) < 3 || strlen($promo_code) > 32) {
					$errors[] = config::translate('field.promo_code.error.invalid-format');
					$form1['promo_code']['error'] = true;
				} else {
					$form1['promo_code']['value'] = $promo_code;
				}
			}

			if(!isset($_POST['recaptcha_challenge_field'])) {
				$_POST['recaptcha_challenge_field']='';
			}
			if(!isset($_POST['recaptcha_response_field'])) {
				$_POST['recaptcha_response_field']='';
			}
			$recaptcha_response = $recaptcha->recaptcha_check_answer (
																		null,
																		$_SERVER["REMOTE_ADDR"],
																		$_POST["recaptcha_challenge_field"],
																		$_POST["recaptcha_response_field"]
																);

			if (!$recaptcha_response['is_valid']) {
				$errors[] = str_replace('{error}',$recaptcha_response['error'],config::translate('field.recaptcha.error.invalid'));
			}

			$_SESSION['form1'] = $form1;
			// process
			if(count($errors) > 0) {
				$message = '<div class="errors">';
				$message.= '<p><img src="' . config::images('problem.png') . '" alt="' . config::translate('form.invalid') . '" /></p>';
				//$message.= '<p style="font-size:15px;font-family:Arial;"><strong>[title_problems_with_form]</strong></p>';
				$message.= '<p>'.config::translate('form.correct-errors').'</p>';
				$message.= '<ul>';
				$message.= '<li>'.implode('</li><li>',$errors).'</li>';
				$message.= '</ul>';
				$message.= '<p><a href="#" class="errorClose"><img src="' . config::images('back_to_form.png') . '" alt="' . config::translate('form.back') . '" /></a></p>';
				//$message.= '<p><strong><a href="#" class="errorClose" style="text-decoration:none;font-size:15px;font-family:Arial;">'.config::translate('form.back').'</a></strong></p>';
				$message.= '</div>';

				$_SESSION['stage'] = 1;
				$_SESSION['message'] = $message;
				$_SESSION['form1'] = $form1;
			} else {

				/**
				 * Add user to database and add to subscriptions if necessary too
				 */
				$userObject = new user(); // initializing user class object
				// Save data to user table
				$user_uid = 0;
				$user_uid =  $userObject->SubscribeSaveHomeUser();

				if($user_uid > 0) {
					$HuserObject = new profile_homeuser(); // initializing profile_homeuser object
					// Save data to profile_homeuser table
					$homeuser_uid = 0;
					$homeuser_uid =  $HuserObject->SubscribeHomeuserSave($user_uid);

					$_SESSION['form1']['user_uid']	= $user_uid;
					$_SESSION['form1']['username']	= $form1['email']['value'];
					$_SESSION['form1']['password']	= $form1['password']['value'];
					$_SESSION['form1']['name']		= $form1['name']['value'];
					$_SESSION['form1']['optin']		= $optin;
					$_SESSION['stage'] = 2;


					if($homeuser_uid > 0) {

						/**
						 * Fetch the page data from the database for this given locale
						 */
						$price = 0;

						$priceArray = array();
						$Pricingobject = new currencies();
						$priceArray = $Pricingobject->getPriceAndCurrency('homeuser');

						if(count( $priceArray )) {
							$price = $priceArray['price'];
						}

						if(is_numeric($price) == false || $price == NULL) {
							$price = 35;
						}

						if( $price != '' ) {
							$subscribe = new subscriptions();
							$subscribe_uid = 0;
							$subscribe_uid = $subscribe->CreateHomeUserSubscription($user_uid, $price);
							if($subscribe_uid > 0) {
								if(isset($_SESSION['form1'])) {
									$_SESSION['form1']['subscribe_uid'] = $subscribe_uid;
								}


								// start subscriptions_products
								if(isset($form1['product_uid']['value']) && $form1['product_uid']['value'] > 0) {
									$product_uid = mysql_real_escape_string($form1['product_uid']['value']);
									$query ="SELECT ";
									$query.="`uid` ";
									$query.="FROM ";
									$query.="`product_locale` ";
									$query.="WHERE `uid`='".$product_uid."'";
									$result = database::query($query);
									if(mysql_error() == '' && mysql_num_rows($result)) {
										$arrProduct = mysql_fetch_array($result);
										// insert product details into subscriptions_products
										$query ="INSERT ";
										$query.="INTO ";
										$query.="`subscriptions_products` (";
										$query.="`subscriptions_uid`, ";
										$query.="`product_locale_uid`, ";
										$query.="`product_uid`";
										$query.=") VALUES( ";
										$query.="'".$subscribe_uid."',";
										$query.="'".$arrProduct['uid']."',";
										$query.="'".$arrProduct['product_uid']."'";
										$query.=")";
										database::query($query);
									}
								}
								// end subscriptions_products


								if(true==$optin) {
									/*
									$this->addEmailList(
													$form1['name']['value'],
													$form1['email']['value']
														);
									*/
								}
								$parantObj->sendHomeUserWelcomeEmail(
																$user_uid,
																$form1['email']['value'],
																$form1['password']['value'],
																$form1['name']['value']
																	);
								output::redirect(config::url('subscribe/homeuser/'));
							} else {
								echo mysql_error().$query;
							}
						} else {
							echo mysql_error().$query;
						}
					} else {
						echo mysql_error().$query;
					}
				} else {
					echo mysql_error().$query;
				}
			}
			output::redirect(config::url('subscribe/homeuser/'));
		} else {
			if(isset($_SESSION['form1'])) {
				$form1 = $_SESSION['form1'];
				unset($_SESSION['form1']);
			}
		}

		/**
		 * Get any error message
		 */
		if(isset($_SESSION['message']) && strlen($_SESSION['message']) > 0) {
				$message = $_SESSION['message'];
				unset($_SESSION['message']);
		}

		return array(
			$errors,
			$message,
			$form1
		);

	}

}
?>