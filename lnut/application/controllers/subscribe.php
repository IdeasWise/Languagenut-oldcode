<?php
/**
 * subscribe.php
 */
class Subscribe extends Controller {

	private $locale		= 'en';
	private $type		= 'homeuser';
	private $typegiven	= false;

	public function __construct() {
		parent::__construct();
		$paths = config::get('paths');
		$this->locale = config::get('locale');
		if(isset($_GET['package']) && in_array($_GET['package'],array('standard','home','gaelic','eal'))) {
			$_SESSION['sess_package'] = $_GET['package'];
		}
		if(isset($paths[1]) && $paths[1] == 'cancel-subscription') {
			$this->doCancelSubscription();
			exit;
		}
		if(isset($paths[1]) && $paths[1] == 'send-application') {
			$this->load_controller('send_application');
			exit;
		}
		if(isset($this->locale) && strlen($this->locale) > 0) {
			if (isset($paths[1]) && in_array($paths[1], array('homeuser', 'school'))) {
				$this->type = $paths[1];
				$this->typegiven = true;
			}
			if (isset($paths[2]) && $paths[2] == 'ipn') {
				$this->paypal();
			}
			$this->set_locale();
		} else {
			output::redirect(config::url('/en/subscribe/'));
		}
	}

	protected function doCancelSubscription() {
		/**
		 * Fetch the page data from the database for this given locale
		 */
		$translate	= 'msg.invalid.subscription.cancel.link';
		$message	= '';
		$arrErrors		= array ();
		$frm		= array (
			'email'	=> array (
				'value'=>'',
				'error'=>false
			)
		);
		if(count($_POST) > 0) {
			$email	=(isset($_POST['email']) && strlen(trim($_POST['email'])) > 0)? trim($_POST['email']):'';

			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$arrErrors[] = config::translate('field.email.error.invalid');
				$frm['email']['error'] = true;
			} else {
				$objUser = new user();
				if($objUser->email_exist($email) == false) {
					$arrErrors[] = config::translate('msg.invalid.subscription.cancel.email');
					$frm['email']['error'] = true;
				} else {
						$frm['email']['value'] = $email;
				}
			}
		// process
			if(count($arrErrors) > 0) {
				$message = '<div class="errors">';
				$message.= '<p><img src="'.config::images('problem.png').'" alt="'.config::translate('form.invalid').'" /></p>';
				$message.= '<p>'.config::translate('form.correct-errors').'</p>';
				$message.= '<ul>';
				$message.= '<li>'.implode('</li><li>',$arrErrors).'</li>';
				$message.= '</ul>';
				$message.= '<p><a href="#" class="errorClose"><img src="'.config::images('back_to_form.png').'" alt="'.config::translate('form.back').'" /></a></p>';
				$message.= '</div>';
			} else {
				$objUser = new user();
				$fields = array('uid','email','user_type','registration_key');
				$search = array('email'=>$email);
				$result = $objUser->search( $fields , $search );
				if(count($result) > 0 ) {
					if(in_array('school',@explode(',',$result['user_type']))) {
						$schoolObj = new users_schools();
						$fields = array('name','school');
						$search = array('user_uid'=>$result['uid']);
						$SRow = $schoolObj->search( $fields , $search );
						$this->sendCancelSubscription($result['email'], $result['registration_key'], $SRow['name']);
					} else if(in_array('homeuser',@explode(',',$result['user_type']))) {
						$HmObj = new profile_homeuser();
						$fields = array('vfirstname');
						$search = array('iuser_uid'=>$result['uid']);
						$HRow = $HmObj->search( $fields , $search );
						$this->sendCancelSubscription($result['email'], $result['registration_key'], $HRow['vfirstname']);
					}
				}

				$message = '<div class="errors">';
				$message.= '<ul>';
				$message.= '<li>'.config::translate('msg.subscription.cancel.email.success').'</li>';
				$message.= '</ul>';
				$message.= '<p><a href="#" class="errorClose"><img src="'.config::images('back_to_form.png').'" alt="'.config::translate('form.back').'" /></a></p>';
				$message.= '</div>';
			}
		}

		/**
		 * Fetch the page details
		 */
		$page = new page('subscribe');
		/**
		 * Fetch the body content
		 */
		$display = "";
		if(isset($_GET['k']) && is_numeric($_GET['k'])) {
			$translate = 'msg.invalid.subscription.cancel.link';
		} else if( isset($_GET['k']) && !empty($_GET['k']) ) {
			$objUser = new user();
			$fields = array('uid','email');
			$search = array('registration_key'=>$_GET['k']);
			$row = $objUser->search( $fields , $search );
			if( count($row) > 0 ) {
				$objUser = new user($row['uid']);
				$objUser->load();
				$objUser->set_active(0);
				$objUser->set_access_allowed(0);
				$objUser->save();

				$this->mail_html(
					'jamie@languagenut.com',
					'Subscriber cancelled!',
					'Oh dear! someone has lost their marbles...<br /><br /><b>Email:</b> '.$row['email']."<br />",
					'subs@languagenut.com',
					'',
					'',
					'',
					''
				);
				$translate = 'msg.subscription.cancel.success';
				$display = "display:none;";
			}
		}

		$body = make::tpl ('body.cancel.subscription');
		$body->assign(
			array(
				'display'						=> $display,
				'errors'						=> $message,
				'translate.back_to_homepage'	=> '',
				'intro_text'					=> config::translate($translate),
				'locale'						=> $this->locale . '/',
				'email'							=> $frm['email']['value'],
				'highlight:email'				=> ($frm['email']['error'] ? ' class="highlighted"' : '')
			)
		);

		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = make::tpl ('skeleton.subscribe');
		$skeleton->assign(
			array(
				'title'			=> $page->title(),
				'keywords'		=> $page->keywords(),
				'description'	=> $page->description(),
				'body'			=> $body,
				'locale'		=> $this->locale,
				'background_url'=> 'registration_bg.en.jpg'
			)
		);
		output::as_html($skeleton, true);
	}

	protected function page_log($msg) {
		$this->msgs[] = $msg;
	}

	protected function page_report() {
		$string = "";
		foreach ($this->msgs as $index => $msg) {
			$string.= $index . ':' . $msg . "\n";
		}
	}

	protected function set_locale() {
		/**
		 * Check the path exists as a valid language in the DB
		 */
		$objLanguage = new language();

		if ($objLanguage->CheckLocale($this->locale,false) == false) {
			$this->locale = 'en';
		}
		$this->showpage();
	}

	protected function showpage() {
		if ($this->typegiven) {
			/**
			 * Show the appropriate Sign Up form
			 */
			if ($this->type == 'homeuser') {
				$this->show_homeuser();
			} else {
				$this->show_school();
			}
		} else {
			/**
			 * Show a screen to select whether you want a homeuser or school account
			 */
			$this->show_select();
		}
	}

	protected function show_select() {
		/**
		 * Get the subscription background and intro text
		 */
		$background_url = '';

		$objPageTrans = new page_subscribe_select_translations();
		$arrPageTrans = $objPageTrans->getByLocale($this->locale);

		if($arrPageTrans && count($arrPageTrans) > 0) {
			/**
			 * Fetch the body content
			 */
			$body = make::tpl('body.subscribe');
			$body->assign(array(
				'title_url'			=> $arrPageTrans['title_url'],
				'title_alt'			=> stripslashes($arrPageTrans['title_alt']),
				'intro_text'		=> stripslashes($arrPageTrans['intro_text']),
				'select_school'		=> stripslashes($arrPageTrans['select_school']),
				'select_homeuser'	=> stripslashes($arrPageTrans['select_homeuser']),
				'locale'			=> $this->locale
			));

			/**
			 * Fetch the page details
			 */
			$page = new page('subscribe');

			/**
			 * Fetch the standard public xhtml page template
			 */
			$skeleton = make::tpl('skeleton.subscribe');
			$skeleton->assign(array(
				'background_url'=> $arrPageTrans['background_url'],
				'title'			=> $page->title(),
				'keywords'		=> $page->keywords(),
				'description'	=> $page->description(),
				'body'			=> $body,
				'locale'		=> $this->locale
			));
			// echo $body->get_content(); exit;
			output::as_html($skeleton, true);
		} else {
			output::redirect(config::url());
		}
	}

	protected function show_school() {
		/**
		 * Determine which stage of the school registration process we're trying to complete
		 */
		$stage = 1;
		if (isset($_SESSION['stage']) && in_array($_SESSION['stage'], array(1, 2, 3, 4))) {
			$stage = $_SESSION['stage'];
		} else {
			$_SESSION['stage'] = $stage;
		}
		$method = 'show_school_stage_' . $stage;
		$this->$method();
	}
	
	protected function show_school_stage_1() {
		//mail('andrew.whitfield@yahoo.co.uk','test','test','From: dev@mystream.co.uk');
		
		/**
		 * Bring in ReCaptcha
		 */
		$recaptcha = new component_recaptchalib();
		/**
		 * Fetch the page data from the database for this given locale
		 */
		$objSchoolStage = new page_subscribe_school_stages_translations();
		list($arrErrors, $arrMessage, $form) = $objSchoolStage->subscribeValidation();

		$objSchoolStage1 = new page_subscribe_school_stage_1_translations();
		$objSchoolStage1->load(array(), array('locale' => $this->locale));
		$objSchoolStage2 = new page_subscribe_school_stage_2_translations();
		$objSchoolStage2->load(array(), array('locale' => $this->locale));
		$objSchoolStage3 = new page_subscribe_school_stage_3_translations();
		$objSchoolStage3->load(array(), array('locale' => $this->locale));

		$arrStageInfo = array();
		if (isset($objSchoolStage2->TableData['locale']['Value']) && $objSchoolStage2->TableData['locale']['Value'] != '') {
			foreach ($objSchoolStage2->TableData as $IDX => $VAL) {
				$arrStageInfo[$IDX] = $VAL['Value'];
			}
		}
		if (isset($objSchoolStage3->TableData['locale']['Value']) && $objSchoolStage3->TableData['locale']['Value'] != '') {
			foreach ($objSchoolStage3->TableData as $IDX => $VAL) {
				$arrStageInfo[$IDX] = $VAL['Value'];
			}
		}
		// keep Stage1 assignment last because some variable is common like introtext, explaination etc..
		// and there is no explaination in stage 2 and 3 so if we wan to take explaination from stage 1 need to keep it last

		if (isset($objSchoolStage1->TableData['locale']['Value']) && $objSchoolStage1->TableData['locale']['Value'] != '') {
			foreach ($objSchoolStage1->TableData as $IDX => $VAL) {
				$arrStageInfo[$IDX] = $VAL['Value'];
			}
		}

		$background_url = '';
		$title_url = '';
		$title_alt = '';
		$intro_text = '';
		$recaptcha_text = '';
		$promo_text = '';
		$accept_terms = '';

		if (count($arrStageInfo) > 0) {
			$background_url = $arrStageInfo['background_url'];
			$title_url = $arrStageInfo['title_url'];
			$title_alt = stripslashes($arrStageInfo['title_alt']);
			$intro_text = stripslashes($arrStageInfo['intro_text']);
			$recaptcha_text = stripslashes($arrStageInfo['recaptcha_text']);
			$promo_text = stripslashes($arrStageInfo['promo_text']);
			$accept_terms = str_replace(
							array(
								'{link:terms}',
								'{link:privacy}'
							),
							array(
								config::url('terms/'),
								config::url('privacy/')
							),
							stripslashes($arrStageInfo['accept_terms']
					));

			$send_updates = (isset($arrStageInfo['send_updates']))?stripslashes($arrStageInfo['send_updates']):'';
			$emails_not_to_3rd_party = (isset($arrStageInfo['emails_not_to_3rd_party']))?stripslashes($arrStageInfo['emails_not_to_3rd_party']):'';

			$label_name = (isset($arrStageInfo['label_your_name']))?stripslashes($arrStageInfo['label_your_name']):"";
			$label_phone_number = (isset($arrStageInfo['label_phone_number']))?stripslashes($arrStageInfo['label_phone_number']):'';
			$label_email = (isset($arrStageInfo['label_email']))?stripslashes($arrStageInfo['label_email']):'';
			$label_password = (isset($arrStageInfo['label_password']))?stripslashes($arrStageInfo['label_password']):'';
			$label_repeat_password = (isset($arrStageInfo['label_repeat_password']))?stripslashes($arrStageInfo['label_repeat_password']):'';
			$label_which_reseller = (isset($arrStageInfo['which_reseller']))?stripslashes($arrStageInfo['which_reseller']):'';
			$label_please_retype_captcha = (isset($arrStageInfo['label_please_retype_captcha']))?stripslashes($arrStageInfo['label_please_retype_captcha']):'';
			$title_problems_with_form = (isset($arrStageInfo['title_problems_with_form']))?stripslashes($arrStageInfo['title_problems_with_form']):'';

			$label_school_name = (isset($arrStageInfo['label_school_name']))?stripslashes($arrStageInfo['label_school_name']):'';
			$label_school_detail = (isset($arrStageInfo['label_school_detail']))?stripslashes($arrStageInfo['label_school_detail']):'School Details';
			$label_school_address = (isset($arrStageInfo['label_school_address']))?stripslashes($arrStageInfo['label_school_address']):'';
			$label_school_postcode = (isset($arrStageInfo['label_school_postcode']))?stripslashes($arrStageInfo['label_school_postcode']):'';

			$label_whole_school_username = (isset($arrStageInfo['label_whole_school_username']))?stripslashes($arrStageInfo['label_whole_school_username']):'';
			$label_whole_school_password = (isset($arrStageInfo['label_whole_school_password']))?stripslashes($arrStageInfo['label_whole_school_password']):'';

			$reseller_code_uids = '';

/*
			$query = "SELECT `uid`,`show_reseller_codes` FROM `language` WHERE `prefix`='".$this->locale."' LIMIT 1";
			$result = database::query($query);

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				if($row['show_reseller_codes']==1) {
					$reseller_code_uids.='<option>Please Select</option>';
					$query = "SELECT `uid`, `name` FROM `language_reseller_codes` WHERE `language_uid`='".$row['uid']."' ORDER BY `name` ASC";
					$result = database::query($query);

					if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
						while($row = mysql_fetch_assoc($result)) {
							$reseller_code_uids.='<option value="'.$row['uid'].'">'.stripslashes($row['name']).'</option>';
						}
					}
				} else {
					$label_which_reseller = '';
				}
			}
*/
			/**
			 * Fetch the page details
			 */
			$page = new page('subscribe.'.$this->locale);

			/**
			 * Fetch the body content
			 */
			$signType = ($this->type == "schoolsubscribe") ? "1" : "0";

			//$body = new xhtml('body.subscribe.school.stages');
			// new template
			$tpl = 'body.subscribe.school.stages';
			$skeleton_tpl = 'skeleton.subscribe';
			if(in_array($this->locale,array('en','us','ca','nz','au','in'))) {
				$tpl = 'body.subscribe.school.'.$this->locale;
				$skeleton_tpl = 'skeleton.landing';
			}
			$package_image = 'mfl-button.png';
			$package_image_alt = 'Modern Foreign Languages';
			if(isset($_SESSION['sess_package']) && $_SESSION['sess_package']=='eal') {
				$package_image = 'eal-button.png';
				$package_image_alt = 'EAL';
			} else if(isset($_SESSION['sess_package']) && $_SESSION['sess_package']=='gaelic') {
				$package_image = 'nut_Scotland_small.png';
				$package_image_alt = 'Gaelic';
			}
			$body = new xhtml($tpl);
			$body->load();
			$body->assign(
				array(
					'errors'								=> $arrMessage,
					'type'									=> $this->type,
					'translate.back_to_homepage'			=> '',
					'title_url'								=> $title_url,
					'title_alt'								=> $title_alt,
					'intro_text'							=> $intro_text,
					'locale'								=> $this->locale . '/',
					'name'									=> $form['name']['value'],
					'highlight:name'						=> ($form['name']['error'] ? ' class="highlighted"' : ''),
					'phone_number'							=> $form['phone_number']['value'],
					'highlight:phone_number'				=> ($form['phone_number']['error'] ? ' class="highlighted"' : ''),
					'email'									=> $form['email']['value'],
					'highlight:email'						=> ($form['email']['error'] ? ' class="highlighted"' : ''),
					'highlight:password1'					=> ($form['password1']['error'] ? ' class="highlighted"' : ''),
					'highlight:password2'					=> ($form['password2']['error'] ? ' class="highlighted"' : ''),
					'captcha'								=> $recaptcha->recaptcha_get_html(null),
					'translate.captchatext'					=> $recaptcha_text,
					'translate.use_promo_code'				=> $promo_text,
					'promo_code'							=> $form['promo_code']['value'],
					'highlight:promo_code'					=> '',
					'translate.accept_terms'				=> $accept_terms,
					'translate.send_updates'				=> $send_updates,
					'translate.emails_not_to_3rd_party'		=> $emails_not_to_3rd_party,
					'school_name'							=> ($form['school_name']['value']) ? $form['school_name']['value'] : "",
					'highlight:school_name'					=> ($form['school_name']['error'] ? ' class="highlighted"' : ''),
					'school_address'						=> ($form['school_address']['value']) ? $form['school_address']['value'] : "",
					'highlight:school_address'				=> ($form['school_address']['error'] ? ' class="highlighted"' : ''),
					'school_postcode'						=> ($form['school_postcode']['value']) ? $form['school_postcode']['value'] : "",
					'highlight:school_postcode'				=> ($form['school_postcode']['error'] ? ' class="highlighted"' : ''),
					'username_open'							=> ($form['username_open']['value']) ? $form['username_open']['value'] : "",
					'highlight:username_open'				=> ($form['username_open']['error'] ? ' class="highlighted"' : ''),
					'password_open'							=> ($form['password_open']['value']) ? $form['password_open']['value'] : "",
					'highlight:password_open'				=> ($form['password_open']['error'] ? ' class="highlighted"' : ''),
					'translate.label_name'					=> $label_name,
					'translate.label_phone_number'			=> $label_phone_number,
					'translate.label_email'					=> $label_email,
					'translate.label_password'				=> $label_password,
					'translate.label_repeat_password'		=> $label_repeat_password,
					'translate.label_please_retype_captcha'	=> $label_please_retype_captcha,
					'translate.title_problems_with_form'	=> $title_problems_with_form,
					'translate.label_school_name'			=> $label_school_name,
					'translate.label_school_address'		=> $label_school_address,
					'translate.label_school_postcode'		=> $label_school_postcode,
					'translate.label_whole_school_username' => $label_whole_school_username,
					'translate.label_whole_school_password' => $label_whole_school_password,
					'translate.which_reseller'				=> $label_which_reseller,
					'reseller_code_uids'					=> $reseller_code_uids,
					'signtype'								=> $signType,
					'translate.label_school_detail'			=> $label_school_detail,
					'package_image_alt'						=> $package_image_alt,
					'package_image'							=> $package_image
				)
			);


			/**
			 * Fetch the standard public xhtml page template
			 */
			//$skeleton = new xhtml('skeleton.subscribe');
			// new skelton
			$skeleton = new xhtml($skeleton_tpl);
			$skeleton->load();
			$skeleton->assign(
					array(
						'title' => $page->title(),
						'keywords' => $page->keywords(),
						'description' => $page->description(),
						'body' => $body,
//						'body' => $bodyContent . $body2Content . $body3Content,
						'background_url' => 'registration_bg.en.jpg',
						'pageID' => 'register'
					)
			);
			output::as_html($skeleton, true);
		} else {
			output::redirect(config::url());
		}
	}


	protected function show_school_stage_1_old() {
		//mail('andrew.whitfield@yahoo.co.uk','test','test','From: dev@mystream.co.uk');
		/**
		 * Bring in ReCaptcha
		 */
		$recaptcha = new component_recaptchalib();
		/**
		 * Fetch the page data from the database for this given locale
		 */
		$objSchoolStage1 = new page_subscribe_school_stage_1_translations();
		list($arrErrors, $arrMessage, $form1) = $objSchoolStage1->subscribeValidation();
		$objSchoolStage1->load(array(), array('locale' => $this->locale));

		$arrStageInfo = array();
		if (isset($objSchoolStage1->TableData['locale']['Value']) && $objSchoolStage1->TableData['locale']['Value'] != '') {
			foreach ($objSchoolStage1->TableData as $IDX => $VAL) {
				$arrStageInfo[$IDX] = $VAL['Value'];
			}
		}

		$background_url		= '';
		$title_url			= '';
		$title_alt			= '';
		$intro_text			= '';
		$recaptcha_text		= '';
		$promo_text			= '';
		$accept_terms		= '';

		if (count($arrStageInfo) > 0) {
			$background_url		= $arrStageInfo['background_url'];
			$title_url			= $arrStageInfo['title_url'];
			$title_alt			= stripslashes($arrStageInfo['title_alt']);
			$intro_text			= stripslashes($arrStageInfo['intro_text']);
			$recaptcha_text		= stripslashes($arrStageInfo['recaptcha_text']);
			$promo_text			= stripslashes($arrStageInfo['promo_text']);
			$accept_terms		= str_replace(
				array(
					'{link:terms}',
					'{link:privacy}'
				),
				array(
					config::url('terms/'),
					config::url('privacy/')
				),
				stripslashes($arrStageInfo['accept_terms']
			));

			$send_updates = stripslashes($arrStageInfo['send_updates']);
			$emails_not_to_3rd_party = stripslashes($arrStageInfo['emails_not_to_3rd_party']);

			$label_name = stripslashes($arrStageInfo['label_your_name']);
			$label_phone_number = stripslashes($arrStageInfo['label_phone_number']);
			$label_email = stripslashes($arrStageInfo['label_email']);
			$label_password = stripslashes($arrStageInfo['label_password']);
			$label_repeat_password = stripslashes($arrStageInfo['label_repeat_password']);
			$label_which_reseller = stripslashes($arrStageInfo['which_reseller']);
			$label_please_retype_captcha = stripslashes($arrStageInfo['label_please_retype_captcha']);
			$title_problems_with_form = stripslashes($arrStageInfo['title_problems_with_form']);

			$reseller_code_uids = '';
			$show_reseller_codes = false;

			$query = "SELECT `uid`,`show_reseller_codes` FROM `language` WHERE `prefix`='".$this->locale."' LIMIT 1";
			$result = database::query($query);

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				if($row['show_reseller_codes']==1) {
					$reseller_code_uids.='<option>Please Select</option>';
					$query = "SELECT `uid`, `name` FROM `language_reseller_codes` WHERE `language_uid`='".$row['uid']."' ORDER BY `name` ASC";
					$result = database::query($query);

					if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
						$show_reseller_codes = true;
						while($row = mysql_fetch_assoc($result)) {
							$reseller_code_uids.='<option value="'.$row['uid'].'">'.stripslashes($row['name']).'</option>';
						}
					}/**/
				} else {
					$label_which_reseller = '';
				}
			}

			$reseller_visibility = 'display:block';
			if(!$show_reseller_codes) {
				$reseller_visibility = 'display:none';
			}

			/**
			 * Fetch the page details
			 */
			$page = new page('subscribe');

			/**
			 * Fetch the body content
			 */
			$body = new xhtml('body.subscribe.school.stage1');
			$body->load();
			$body->assign(
				array(
					'errors'					=> str_replace('[title_problems_with_form]',$title_problems_with_form,$arrMessage),
					'type'						=> 'school',
					'translate.back_to_homepage'=> '',
					'title_url'					=> $title_url,
					'title_alt'					=> $title_alt,
					'intro_text'				=> $intro_text,
					'locale'					=> $this->locale . '/',
					'name'						=> $form1['name']['value'],
					'highlight:name'			=> ($form1['name']['error'] ? ' class="highlighted"' : ''),
					'phone_number'				=> $form1['phone_number']['value'],
					'highlight:phone_number'	=> ($form1['phone_number']['error'] ? ' class="highlighted"' : ''),
					'email'						=> $form1['email']['value'],
					'highlight:email'			=> ($form1['email']['error'] ? ' class="highlighted"' : ''),
					'highlight:password1'		=> ($form1['password1']['error'] ? ' class="highlighted"' : ''),
					'highlight:password2'		=> ($form1['password2']['error'] ? ' class="highlighted"' : ''),
					'captcha'					=> $recaptcha->recaptcha_get_html(null),
					'translate.captchatext'		=> $recaptcha_text,
					'translate.use_promo_code'	=> $promo_text,
					'promo_code'				=> $form1['promo_code']['value'],
					'highlight:promo_code'		=> '',
					'translate.accept_terms'	=> $accept_terms,
					'translate.send_updates'	=> $send_updates,
					'translate.emails_not_to_3rd_party'	=> $emails_not_to_3rd_party,

					'translate.label_name'		=> $label_name,
					'translate.label_phone_number'	=> $label_phone_number,
					'translate.label_email'			=> $label_email,
					'translate.label_password'		=> $label_password,
					'translate.label_repeat_password'=> $label_repeat_password,
					'translate.label_please_retype_captcha'	=> $label_please_retype_captcha,
					'translate.title_problems_with_form' => $title_problems_with_form,
					'translate.which_reseller'				=> $label_which_reseller,
					'reseller_code_uids'					=> $reseller_code_uids,
					'reseller_code_visibility'				=> $reseller_visibility
				)
			);

			/**
			 * Fetch the standard public xhtml page template
			 */
			$skeleton = new xhtml('skeleton.subscribe');
			$skeleton->load();
			$skeleton->assign(
				array(
					'title' => $page->title(),
					'keywords' => $page->keywords(),
					'description' => $page->description(),
					'body' => $body,
					'locale'=> $this->locale,
					'background_url' => 'registration_bg.en.jpg'
				)
			);
			output::as_html($skeleton, true);
		} else {
			output::redirect(config::url());
		}
	}

	protected function show_school_stage_2() {
		/**
		 * Set up Defaults for the Stage 1 Data Capture Form
		 */
		$objSchoolStage2 = new page_subscribe_school_stage_2_translations();

		list($arrErrors, $arrMessage, $form2) = $objSchoolStage2->subscribeValidation();
		/**
		 * Fetch the page data from the database for this given locale
		 */
		$objSchoolStage2->load(array(), array('locale' => $this->locale));

		$arrStageInfo = array();
		if (isset($objSchoolStage2->TableData['locale']['Value']) && $objSchoolStage2->TableData['locale']['Value'] != '') {
			foreach ($objSchoolStage2->TableData as $IDX => $VAL) {
				$arrStageInfo[$IDX] = $VAL['Value'];
			}
		}
		$background_url	= '';
		$title_url		= '';
		$title_alt		= '';
		$intro_text		= '';

		if(count($arrStageInfo) > 0) {
			$background_url	= $arrStageInfo['background_url'];
			$title_url		= $arrStageInfo['title_url'];
			$title_alt		= stripslashes($arrStageInfo['title_alt']);
			$label_school_name = stripslashes($arrStageInfo['label_school_name']);
			$label_school_address = stripslashes($arrStageInfo['label_school_address']);
			$label_school_postcode = stripslashes($arrStageInfo['label_school_postcode']);
			/**
			 * Fetch the body content
			 */
			$body= make::tpl ('body.subscribe.school.stage2');
			$body->assign(
				array(
					'errors'					=> $arrMessage,
					'type'						=> 'school',
					'translate.back_to_homepage'=> '',
					'title_url'					=> $title_url,
					'title_alt'					=> $title_alt,
					'locale'					=> $this->locale . '/',
					'school_name'				=> $form2['school_name']['value'],
					'highlight:school_name'		=> ($form2['school_name']['error'] ? ' class="highlighted"' : ''),
					'school_address'			=> $form2['school_address']['value'],
					'highlight:school_address'	=> ($form2['school_address']['error'] ? ' class="highlighted"' : ''),
					'school_postcode'			=> $form2['school_postcode']['value'],
					'highlight:school_postcode'	=> ($form2['school_postcode']['error'] ? ' class="highlighted"' : ''),
					'translate.label_school_name'	=> $label_school_name,
					'translate.label_school_address'	=> $label_school_address,
					'translate.label_school_postcode'	=> $label_school_postcode
				)
			);

			/**
			 * Fetch the page details
			 */
			$page = new page('subscribe');

			/**
			 * Fetch the standard public xhtml page template
			 */
			$skeleton = make::tpl('skeleton.subscribe');
			$skeleton->assign(
				array(
					'title'			=> $page->title(),
					'keywords'		=> $page->keywords(),
					'description'	=> $page->description(),
					'body'			=> $body,
					'locale'		=> $this->locale,
					'background_url'=> 'registration_bg.en.jpg'
				)
			);
			output::as_html($skeleton, true);
		} else {
			output::redirect(config::url());
		}
	}

	protected function show_school_stage_3() {
		$objSchoolStage3 = new page_subscribe_school_stage_3_translations();
		/**
		 * Check Posted Variables
		 */
		list($arrErrors, $arrMessage, $form3) = $objSchoolStage3->subscribeValidation();
		/**
		 * Fetch the page data from the database for this given locale
		 */
		$objSchoolStage3->load(array(), array('locale' => $this->locale));
		$arrStageInfo = array();
		if (isset($objSchoolStage3->TableData['locale']['Value']) && $objSchoolStage3->TableData['locale']['Value'] != '') {
			foreach ($objSchoolStage3->TableData as $IDX => $VAL) {
				$arrStageInfo[$IDX] = $VAL['Value'];
			}
		}
		$background_url	= '';
		$title_url		= '';
		$title_alt		= '';
		$intro_text		= '';
		$explanation	= '';

		if (count($arrStageInfo) > 0) {
			$background_url	= $arrStageInfo['background_url'];
			$title_url		= $arrStageInfo['title_url'];
			$title_alt		= stripslashes($arrStageInfo['title_alt']);
			$explanation	= stripslashes($arrStageInfo['explanation']);
			$label_whole_school_username	= stripslashes($arrStageInfo['label_whole_school_username']);
			$label_whole_school_password	= stripslashes($arrStageInfo['label_whole_school_password']);

			/**
			 * Fetch the body content
			 */
			$body = make::tpl ('body.subscribe.school.stage3');
			$body->assign(
				array(
					'errors'					=> $arrMessage,
					'type'						=> 'school',
					'translate.back_to_homepage'=> '',
					'title_url'					=> $title_url,
					'title_alt'					=> $title_alt,
					'explanation'				=> $explanation,
					'locale'					=> $this->locale . '/',
					'username_open'				=> $form3['username_open']['value'],
					'highlight:username_open'	=> ($form3['username_open']['error'] ? ' class="highlighted"' : ''),
					'password_open'				=> $form3['password_open']['value'],
					'highlight:password_open'	=> ($form3['password_open']['error'] ? ' class="highlighted"' : ''),
					'translate.label_whole_school_username'	=> $label_whole_school_username,
					'translate.label_whole_school_password'	=> $label_whole_school_password
				)
			);

			/**
			 * Fetch the page details
			 */
			$page = new page('subscribe');

			/**
			 * Fetch the standard public xhtml page template
			 */
			$skeleton = make::tpl ('skeleton.subscribe');
			$skeleton->assign(
				array(
					'title'			=> $page->title(),
					'keywords'		=> $page->keywords(),
					'description'	=> $page->description(),
					'body' => $body,
					'locale'=> $this->locale,
					'background_url'=> 'registration_bg.en.jpg'
				)
			);
			output::as_html($skeleton, true);
		} else {
			output::redirect(config::url());
		}
	}

	protected function show_school_stage_4() {
		/**
		 * Check SESSION variables
		 */
		$query		= '';
		$arrMessage	= '';
		$arrErrors	= array();

		$form1		= isset($_SESSION['form1']) ? $_SESSION['form1'] : array();
		$form2		= isset($_SESSION['form2']) ? $_SESSION['form2'] : array();
		$form3		= isset($_SESSION['form3']) ? $_SESSION['form3'] : array();
		if (count($form1) > 0 && count($form2) > 0 && count($form3) > 0) {
			$objUser = new user(); // initializing user class object
			// Save data to user table
			$user_uid			= 0;
			$user_uid			= $objUser->SubscribeSave();
			$time				= time();
			$ip_address			= addslashes(substr($_SERVER['REMOTE_ADDR'], 0, 32));
			$registration_key	= md5($form2['school_name']['value'] . $ip_address);
			if ($user_uid > 0) {
				$objSchool = new users_schools(); // initializing users_schools object
				// Save data to users_schools table
				$school_uid = 0;
				$school_uid = $objSchool->SubscribeSchoolSave($user_uid);
				if($school_uid > 0) {
					if (true == $form1['optin']['value']) {
						$this->addEmailList(
							$form1['name']['value'],
							$form1['email']['value']
						);
						
					}

					$_SESSION['login_email']	= $form1['email']['value'];
					$_SESSION['login_password']	= $form1['password']['value'];

					$this->sendSchoolWelcomeEmail(
						$school_uid,
						$user_uid,
						$form1['email']['value'],
						$form1['password']['value'],
						$form3['username_open']['value'],
						$form3['password_open']['value'],
						$form1['name']['value'],
						$form2['school_name']['value'] . ',<br />' . $form2['school_address']['value'] . '<br />' . $form2['school_postcode']['value'],
						$form1['promo_code']['value']
					);

					/**
					 * Fetch the page data from the database for this given locale
					 */
					$price = 0.0;

					$arrPrice = array();
					$objCurrency = new currencies();
					$arrPrice = $objCurrency->getPriceAndCurrency('school');

					if (count($arrPrice)) {
						$price = $arrPrice['price'];
					}
					if(is_numeric($price) == false || $price == NULL) {
						$price = 80;
					}
					if($price != '') {
						/**
						 * Add a subscription from 'now'+ 1 year + 2 weeks
						 */
						$objSubscription = new subscriptions();
						$subscribe_uid = 0;
						$subscribe_uid = $objSubscription->CreateSchoolSubscription($user_uid, $price);
						if ($subscribe_uid > 0) {
							/**
							 * CLEAR FORM SESSIONS
							 */
							unset($_SESSION['form1']);
							unset($_SESSION['form2']);
							unset($_SESSION['form3']);
							unset($_SESSION['stage']);

							/**
							* Following code will create user login session so when they click on enter link they'll
							automatically get log-in
							*/
							$ObjUser = new user($user_uid);
							$ObjUser->load();
							if( $ObjUser->get_uid() > 0 ){
								$ObjUser->login(true);
								if(isset($_SESSION['user'])) {
									$_SESSION['user']['ByOpenUserName'] = 1;
								}
							}
							// login code ends here..
						} else {
							mail('dev@mystream.co.uk', 'LanguageNut Error - school subscription failed', mysql_error() . $query, 'From: errors@languagenut.com');
						}
					} else {
						mail('dev@mystream.co.uk', 'LanguageNut Error - price not found', mysql_error() . $query, 'From: errors@languagenut.com');
					}
				} else {
					mail('dev@mystream.co.uk', 'LanguageNut Error - school id not created', mysql_error() . $query, 'From: errors@languagenut.com');
				}
			} else {
				mail('dev@mystream.co.uk', 'LanguageNut Error - user id not created', mysql_error() . $query, 'From: errors@languagenut.com');
			}
		}

		/**
		 * Fetch the page data from the database for this given locale
		 */
		$objSchoolStage4 = new page_subscribe_school_stage_4_translations();
		$objSchoolStage4->load(array(), array('locale' => $this->locale));
		$arrStageInfo = array();
		if(isset($objSchoolStage4->TableData['locale']['Value']) && $objSchoolStage4->TableData['locale']['Value'] != '') {
			foreach ($objSchoolStage4->TableData as $IDX => $VAL) {
				$arrStageInfo[$IDX] = $VAL['Value'];
			}
		}

		$background_url	= '';
		$title_url		= '';
		$title_alt		= '';
		$intro_text		= '';

		if (count($arrStageInfo) > 0) {
			$background_url	= $arrStageInfo['background_url'];
			$title_url		= $arrStageInfo['title_url'];
			$title_alt		= stripslashes($arrStageInfo['title_alt']);
			$intro_text		= nl2br(stripslashes($arrStageInfo['intro_text']));

			/**
			 * Fetch the body content
			 */
			$body = make::tpl ('body.subscribe.school.stage4');
			$body->assign(
				array(
					'type'						=> 'school',
					'translate.back_to_homepage'=> '',
					'title_url'					=> $title_url,
					'title_alt'					=> $title_alt,
					'intro_text'				=> $intro_text,
					'locale'					=> $this->locale . '/',
				)
			);

			/**
			 * Fetch the page details
			 */
			$page = new page('subscribe');

			/**
			 * Fetch the standard public xhtml page template
			 */
			$skeleton = make::tpl('skeleton.subscribe');
			$skeleton->assign(
				array(
					'title'			=> $page->title(),
					'keywords'		=> $page->keywords(),
					'description'	=> $page->description(),
					'body'			=> $body,
					'locale'=> $this->locale,
					'background_url'=> 'registration_bg.en.jpg'
				)
			);

			output::as_html($skeleton, true);
		} else {
			output::redirect(config::url());
		}
	}

	protected function show_homeuser() {
		$paths = config::get('paths');
		if (isset($paths[3]) && $paths[3] == 'ipn') {
			$stage = 2;
		} else if(isset($paths[2]) && $paths[2] == 'purchase') {
			$stage = 3;
		} else {
			$stage = 1;
		}

		/**
		 * Determine which stage of the user registration process we're trying to complete
		 */
		if ($stage == 1) {
			if (isset($_SESSION['stage']) && is_numeric($_SESSION['stage'])) {
				$stage = $_SESSION['stage'];
			} else {
				$_SESSION['stage'] = $stage;
			}
		}

		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton	= make::tpl ('skeleton.subscribe');
		$recaptcha	= new component_recaptchalib();

		switch ($stage) {
			case 1:
				// name/phone/email/password/terms/optin/promo_code
				/**
				 * Fetch the body content
				 */
				$body = make::tpl ('body.subscribe.homeuser.stage1');

				/**
				 * Fetch the page data from the database for this given locale
				 */
				$objHomeUserStage1 = new page_subscribe_homeuser_stage_1_translations();
				list($arrErrors, $arrMessage, $form1) = $objHomeUserStage1->subscribeValidation($this);
				$objHomeUserStage1->load(array(), array('locale' => $this->locale));

				$arrStageInfo = array();
				if (isset($objHomeUserStage1->TableData['locale']['Value']) && $objHomeUserStage1->TableData['locale']['Value'] != '') {
					foreach ($objHomeUserStage1->TableData as $IDX => $VAL) {
						$arrStageInfo[$IDX] = $VAL['Value'];
					}
				}

				$background_url	= '';
				$title_url		='';
				$title_alt		= '';
				$intro_text		= '';
				$recaptcha_text	= '';
				$promo_text		= '';
				$accept_terms	= '';

				if (count($arrStageInfo) > 0) {
					$background_url	= $arrStageInfo['background_url'];
					$title_url		= $arrStageInfo['title_url'];
					$title_alt		= stripslashes($arrStageInfo['title_alt']);
					$intro_text		= stripslashes($arrStageInfo['intro_text']);
					$recaptcha_text	= stripslashes($arrStageInfo['recaptcha_text']);
					$promo_text		= stripslashes($arrStageInfo['promo_text']);
					$accept_terms	= str_replace(
						array(
							'{link:terms}',
							'{link:privacy}'
						),
						array(
							config::url('terms/'),
							config::url('privacy/')
						),
						stripslashes($arrStageInfo['accept_terms'])
					);
					$send_updates				= stripslashes($arrStageInfo['send_updates']);
					$emails_not_to_3rd_party	= stripslashes($arrStageInfo['emails_not_to_3rd_party']);

					$label_name					= stripslashes($arrStageInfo['label_your_name']);
					$label_phone_number			= stripslashes($arrStageInfo['label_phone_number']);
					$label_email				= stripslashes($arrStageInfo['label_email']);
					$label_password				= stripslashes($arrStageInfo['label_password']);
					$label_repeat_password		= stripslashes($arrStageInfo['label_repeat_password']);
					$label_please_retype_captcha= stripslashes($arrStageInfo['label_please_retype_captcha']);
					$title_problems_with_form	= stripslashes($arrStageInfo['title_problems_with_form']);
					$title_home_user_subscription = stripslashes($arrStageInfo['title_home_user_subscription']);
				} else {
					echo mysql_error() . $query;
				}
				/**
				 * Populate the template
				 */
				$body->assign(
					array(
						'errors'					=> str_replace('[title_problems_with_form]',$title_problems_with_form,$arrMessage),
						'type'						=> 'homeuser',
						'translate.back_to_homepage'=> '',
						'title_url'					=> $title_url,
						'title_alt'					=> $title_alt,
						'intro_text'				=> $intro_text,
						'locale'					=> $this->locale . '/',
						'name'						=> $form1['name']['value'],
						'highlight:name'			=> ($form1['name']['error'] ? ' class="highlighted"' : ''),
						'phone_number'				=> $form1['phone_number']['value'],
						'highlight:phone_number'	=> ($form1['phone_number']['error'] ? ' class="highlighted"' : ''),
						'email'						=> $form1['email']['value'],
						'highlight:email'			=> ($form1['email']['error'] ? ' class="highlighted"' : ''),
						'highlight:password1'		=> ($form1['password1']['error'] ? ' class="highlighted"' : ''),
						'highlight:password2'		=> ($form1['password2']['error'] ? ' class="highlighted"' : ''),
						'captcha'					=> $recaptcha->recaptcha_get_html(null),
						'translate.captchatext'		=> $recaptcha_text,
						'translate.use_promo_code'	=> $promo_text,
						'promo_code'				=> $form1['promo_code']['value'],
						'highlight:promo_code'		=> '',
						'translate.accept_terms'	=> $accept_terms,
						'translate.send_updates'	=> $send_updates,
						'translate.emails_not_to_3rd_party' => $emails_not_to_3rd_party,

						'translate.label_name'		=> $label_name,
						'translate.label_phone_number'	=> $label_phone_number,
						'translate.label_email'			=> $label_email,
						'translate.label_password'		=> $label_password,
						'translate.label_repeat_password'=> $label_repeat_password,
						'translate.label_please_retype_captcha'	=> $label_please_retype_captcha,
						'translate.title_home_user_subscription'=> $title_home_user_subscription
					)
				);
			break;
			case 2:
				/**
				 * Fetch the body content
				 */
				$stage = 2;
				$objSubscription = new subscriptions(); // initializing subscriptions object
				$template = 'body.subscribe.homeuser.stage2';
				$paths = config::get('paths');
				if(isset($paths[2]) && strlen($paths[2]) > 0) {
					if($paths[2] == 'cancel') {
						$objSubscription->Paypalcancel();
						$template = 'body.subscribe.homeuser.stage3';
						$stage = 3;
					} else if ($paths[2] == 'success') {
						$objSubscription->PaypalSuccess();
						$template = 'body.subscribe.homeuser.stage4';
						$stage = 4;
					} else if ($paths[2] == 'ipn') {
						// call PaypalIPN function from subscription file
						$objSubscription->PaypalIPN();
					}
				}

				$body = make::tpl ($template);
				/**
				 * Fetch the page data from the database for this given locale
				 */
				$className = "page_subscribe_homeuser_stage_" . $stage . "_translations";

				$objClass = new $className();
				$objClass->load(array(), array('locale' => $this->locale));
				$arrStageInfo = array();
				if (isset($objClass->TableData['locale']['Value']) && $objClass->TableData['locale']['Value'] != '') {
					foreach ($objClass->TableData as $IDX => $VAL) {
						$arrStageInfo[$IDX] = $VAL['Value'];
					}
				}

				$background_url		= '';
				$title_url			= '';
				$title_alt			= '';
				$intro_text			= '';
				$price				= '';
				$currency_code		= 'GBP';

				if(count($arrStageInfo) > 0) {
					$background_url	= $arrStageInfo['background_url'];
					$title_url		= $arrStageInfo['title_url'];
					$title_alt		= stripslashes($arrStageInfo['title_alt']);
					$intro_text		= stripslashes($arrStageInfo['intro_text']);
					$price			= $arrStageInfo['sub_price'];
				} else {
					echo mysql_error() . $query;
				}

				$arrPrice	= array();
				$objCurrency= new currencies();
				$arrPrice	= $objCurrency->getPriceAndCurrency('homeuser');

				if (count($arrPrice) > 0) {
					$price = $arrPrice['price'];
					$currency_code = $arrPrice['name'];
					if(config::get('locale') == 'ge' && isset($_SESSION['form1']['promo_code']['value']) && $_SESSION['form1']['promo_code']['value'] == 'BELBOOKS')
					$price = 39.00;
				}

				/**
				 * Populate the template
				 */
				$body->assign(
					array(
						'type'						=> 'homeuser',
						'translate.back_to_homepage'=> '',
						'title_url'					=> $title_url,
						'title_alt'					=> $title_alt,
						'intro_text'				=> $intro_text,
						'locale'					=> $this->locale . '/',
						'user_uid'					=> (isset($_SESSION['form1']) && isset($_SESSION['form1']['user_uid'])) ? $_SESSION['form1']['user_uid'] : '',
						'price'						=> $price,
						'currency_code'				=> $currency_code,
						'item_name'					=> config::translate('form.subscription'),
						'item_number'				=> (isset($_SESSION['form1']) && isset($_SESSION['form1']['user_uid'])) ? $_SESSION['form1']['user_uid'] : '',
						'click_here'				=> config::translate('form.click-here')
					)
				);
			break;
			case 3:
				if(isset($_SESSION['user']) && isset($_SESSION['user']['user_type']) && in_array('homeuser',$_SESSION['user']['user_type'])) {
					$query = "SELECT `uid`, `name`, `price`, `vat` FROM `reseller_sub_package` WHERE `reseller_uid`=".$_SESSION['user']['reseller_uid']." AND `deleted`=0 AND `is_active`=1 AND `package_type`='homeuser' ORDER BY `name` ASC";
					$result = database::query($query);
					if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
						$arrPackges = array();
						while($row = mysql_fetch_assoc($result)) {
							$arrPackages[$row['uid']] = $row;
						}
						echo '<pre>';
						print_r($arrPackages);
						echo '</pre>';
					}
					echo $query.mysql_error();
				} else {
					echo '<pre>';
					print_r($_SESSION);
					echo '</pre>';
					#output::redirect(config::url());
				}
			break;
		}

		/**
		 * Get the subscription background and intro text
		 */
		/**
		 * Fetch the page details
		 */
		$page = new page('subscribe');

		/**
		 * Build the output
		 */
		$skeleton->assign(
			array(
				'title'			=> $page->title(),
				'keywords'		=> $page->keywords(),
				'description'	=> $page->description(),
				'body'			=> $body,
				'locale'=> $this->locale,
				'background_url'=> 'registration_bg.en.jpg'
			)
		);
		output::as_html($skeleton, true);
	}


	protected function paypal() {
		$objSubscription = new subscriptions(); // initializing subscriptions object
		$objSubscription->PaypalIPN();
	}

	protected function addEmailList($name, $email) {
		//Your API Key. Go to http://www.campaignmonitor.com/api/required/ to see where to find this and other required keys
		$api_key = '595c7c768c20a86383d81a7066f962d9';
		$client_id = null;
		$campaign_id = null;
		$list_id = '84b28341cf0209713c2dc232651c6bfd';
		$cm = new component_campaignmonitor($api_key, $client_id, $campaign_id, $list_id);

		//Optional statement to include debugging information in the result
		//$cm->debug_level = 1;
		//This is the actual call to the method, passing email address, name.
		$result = $cm->subscriberAdd($email, $name);
	}

	protected function sendSchoolWelcomeEmail($school_uid, $user_uid, $email, $password, $username_open, $password_open, $name, $school, $promo_code) {
		/**
		 * OLD SQL QUERY
		 */
		/*
		$query = "SELECT ";
		$query.= "`ett`.`subject`, ";
		$query.= "`ett`.`body`, ";
		$query.= "`ett`.`from` ";
		$query.= "FROM ";
		$query.= "`email_templates` AS `et`, ";
		$query.= "`email_templates_translations` AS `ett` ";
		$query.= "WHERE ";
		$query.= "`et`.`tag`='school.registration.notify.school' ";
		$query.= "AND `ett`.`locale`='" . config::get('locale') . "' ";
		$query.= "AND `ett`.`email_uid`=`et`.`uid` ";
		$query.= "LIMIT 1";
		*/
		$query = "SELECT ";
		$query.= "`ett`.`subject`, ";
		$query.= "`ett`.`body`, ";
		$query.= "`ett`.`from` ";
		$query.= "FROM ";
		$query.= "`school_registration_templates` AS `et`, ";
		$query.= "`school_registration_templates_translations` AS `ett` ";
		$query.= "WHERE ";
		$query.= "`et`.`slug`='school.registration.welcome.template' ";
		$query.= "AND `ett`.`locale`='" . config::get('locale') . "' ";
		$query.= "AND `ett`.`email_uid`=`et`.`uid` ";
		$query.= "LIMIT 1";

		$result = database::query($query);

		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);

			$subject = stripslashes($row['subject']);
			$from = stripslashes($row['from']);
			$body = str_replace(array('&#123;&#123;','&#125;&#125;'),array('{{','}}'), stripslashes($row['body']));

			$_email = new xhtml();
			$_email->load($body, true);
			$_email->assign(
				array(
					'images' => config::images(),
					'base' => config::base(),
					'name' => $name,
					'school' => $school,
					'school_address' => $school,
					'email' => $email,
					'password' => $password,
					'username_open' => $username_open,
					'password_open' => $password_open,
					'school_uid' => $school_uid,
					'registration_key' => md5($user_uid.'-'.$_SERVER['REMOTE_ADDR']),
					'promo_code' => (strlen($promo_code) > 0) ? 'You used Promocode:<br /><br />' . $promo_code . '<br /><br /><br />' : ''
				)
			);
			$message = $_email->get_content();
			$this->mail_html(
					$email,
					$subject,
					$message,
					'subs@languagenut.com',
					'',
					'',
					'',
					''
			);
			$this->mail_html(
					'jamie@languagenut.com,cshepherd@languagenut.com,sam@languagenut.com',
					'A new Languagenut subscriber! [' . @$_SESSION['aff'] . ']: '.$from,
					$message,
					'subs@languagenut.com',
					'',
					'',
					'',
					''
			);

			if(isset($this->locale) && $this->locale == 'dk') {
				$this->mail_html(
					'Vibeke <vibeke@englishcenter.dk>',
					'A new Languagenut subscriber! [' . @$_SESSION['aff'] . ']: '.$from,
					$message,
					'info@languagenut.com',
					'',
					'',
					'',
					''
				);
			}

			if(isset($this->locale) && $this->locale == 'nz') {
				$this->mail_html(
					'info@ilearn.co.nz',
					'A new Languagenut subscriber! [' . @$_SESSION['aff'] . ']: '.$from,
					$message,
					'subs@languagenut.com',
					'',
					'',
					'',
					''
				);
			}

		} else {
			mail('dev@mystream.co.uk', 'LanguageNut Error - school welcome email', mysql_error() . $query, 'From: info@languagenut.com');
		}
	}

	public function sendHomeUserWelcomeEmail($user_uid, $email, $password, $name) {
		$query = "SELECT ";
		$query.= "`ett`.`subject`, ";
		$query.= "`ett`.`body`, ";
		$query.= "`ett`.`from` ";
		$query.= "FROM ";
		$query.= "`email_templates` AS `et`, ";
		$query.= "`email_templates_translations` AS `ett` ";
		$query.= "WHERE ";
		$query.= "`et`.`tag`='user.registration.notify.user' ";
		$query.= "AND `ett`.`locale`='".config::get('locale')."' ";
		$query.= "AND `ett`.`email_uid`=`et`.`uid` ";
		$query.= "LIMIT 1";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);

			$subject = stripslashes($row['subject']);
			$from = stripslashes($row['from']);
			$body = stripslashes($row['body']);

			$_email = new xhtml();
			$_email->load($body,true);
			$_email->assign(
				array(
					'images'			=> config::images(),
					'base'				=> config::base(),
					'name'				=> $name,
					'email'				=> $email,
					'password'			=> $password,
					'registration_key'	=> md5($user_uid.'-'.$_SERVER['REMOTE_ADDR'])
				)
			);
			$message = $_email->get_content();
			$this->mail_html (
				$email,
				$subject,
				$message,
				'subs@languagenut.com',
				'',
				'',
				'',
				''
			);
			$this->mail_html (
				'jamie@languagenut.com',
				'A new Languagenut subscriber! ['.@$_SESSION['aff'].'] : '.$email,
				$message,
				'subs@languagenut.com',
				'',
				'',
				'',
				''
			);
		}
	}


	protected function sendCancelSubscription( $email, $registration_key, $name = "User") {
		$query = "SELECT ";
		$query.= "`ett`.`subject`, ";
		$query.= "`ett`.`body`, ";
		$query.= "`ett`.`from` ";
		$query.= "FROM ";
		$query.= "`email_templates` AS `et`, ";
		$query.= "`email_templates_translations` AS `ett` ";
		$query.= "WHERE ";
		$query.= "`et`.`tag`='subscription.cancel.email' ";
		$query.= "AND `ett`.`locale`='".config::get('locale')."' ";
		$query.= "AND `ett`.`email_uid`=`et`.`uid` ";
		$query.= "LIMIT 1";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);

			$subject = stripslashes($row['subject']);
			$from = stripslashes($row['from']);
			$body = stripslashes($row['body']);

			$_email = new xhtml();
			$_email->load($body,true);
			$_email->assign(
				array(
					'images'			=> config::images(),
					'uri'				=> config::url(),
					'name'				=> $name,
					'registration_key'	=> $registration_key
				)
			);
			$message = $_email->get_content();
			$this->mail_html (
				$email,
				$subject,
				$message,
				$from,
				'',
				'',
				'',
				''
			);
		}
	}

	private function mail_html($to='', $subject='', $message='', $from='', $receiptname='', $receiptmail='', $cc='', $bcc='') {
		$header = "Content-Transfer-Encoding: 8bit";
		$header .="\nContent-Type: text/html; charset=utf-8";
		if ($from != '') {
			$header .="\nFrom: " . $from;
		}
		if ($cc != '') {
			$header .= "\nCc: " . $cc;
		}
		if ($bcc != '') {
			$header .= "\nBcc: " . $bcc;
		}
		if ($receiptname != '' && $receiptmail != '') {
			//Read receipt
			$headers .= "Disposition-Notification-To: Subscriptions<jamie@languagenut.com>\n";
		}

		$message = str_replace(
			array("<br>", "<br />", "<p>"),
			array("<br>\n", "<br>\n", "<p>\n"),
			$message
		);

		mail($to, $subject, $message, $header);
	}

}

?>