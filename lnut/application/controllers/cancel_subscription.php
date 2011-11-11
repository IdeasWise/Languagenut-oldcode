<?php

	/**
	* cancel_subscription.php
	*/
	class cancel_subscription extends Controller {

		public function __construct() {
			parent::__construct();
			$this->LoadIndex();
		}

		protected function LoadIndex() {
			/**
			* Fetch the page data from the database for this given locale
			*/

			$translate	= 'msg.invalid.subscription.cancel.link';
			$message	= '';
			$arrErrors	= array ();
			$arrForm	= array (
			'email'	=> array (
			'value'=>'',
			'error'=>false
			)
			);

			if(count($_POST) > 0) {
				$email= (isset($_POST['email']) && strlen(trim($_POST['email'])) > 0)? trim($_POST['email']):'';

				if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$arrErrors[]				= config::translate('field.email.error.invalid');
					$arrForm['email']['error']	= true;
				} else {
					$objUser = new user();
					if($objUser->email_exist($email) == false) {
						$arrErrors[]				= config::translate('msg.invalid.subscription.cancel.email');
						$arrForm['email']['error']	= true;
					} else {
						$arrForm['email']['value']	= $email;
					}
				}
				// process
				if (count($arrErrors) > 0) {
					$xhtmlCancelSubscription = new xhtml('cancel_subscription');
					$xhtmlCancelSubscription->load();

					$xhtmlCancelSubscription->assign("problem_img_src", config::images('problem.png'));
					$xhtmlCancelSubscription->assign("problem_img_alt", config::translate('form.invalid'));
					$xhtmlCancelSubscription->assign("correct_errors", config::translate('form.correct-errors'));
					$xhtmlCancelSubscription->assign("error_li", '<li>' . implode('</li><li>', $arrErrors) . '</li>');
					$xhtmlCancelSubscription->assign("back_to_form_src", config::images('back_to_form.png'));
					$xhtmlCancelSubscription->assign("back_to_form_alt", config::translate('form.back'));

					$message = $xhtmlCancelSubscription->get_content();
				} else {
					$objUser = new user();
					$arrFields = array('uid','email','user_type','registration_key');
					$arrSearch = array('email'=>$email);
					$arrResult = $objUser->search( $arrFields , $arrSearch );
					if(count($arrResult) > 0 ) {
						if(in_array('school',explode(',',$arrResult['user_type']))) {
							$objSchool = new users_schools();
							$arrFields = array('name','school');
							$arrSearch = array('user_uid'=>$arrResult['uid']);
							$arrSchool = $objSchool->search( $arrFields , $arrSearch );
							$this->sendCancelSubscription($arrResult['email'], $arrResult['registration_key'], $arrSchool['name']);
						} else if(in_array('homeuser',@explode(',',$arrResult['user_type']))) {
								$objHomeUser = new profile_homeuser();
								$arrFields = array('vfirstname');
								$arrSearch = array('iuser_uid'=>$arrResult['uid']);
								$arrHomeUser = $objHomeUser->search( $arrFields , $arrSearch );
								$this->sendCancelSubscription($arrResult['email'], $arrResult['registration_key'], $arrHomeUser['vfirstname']);
							}
					}

					$xhtmlCancelSubscription = new xhtml('cancel_subscription');
					$xhtmlCancelSubscription->load();

					$xhtmlCancelSubscription->assign("msg.subscription.cancel.email.success", config::translate('msg.subscription.cancel.email.success'));
					$xhtmlCancelSubscription->assign("back_to_form", config::images('back_to_form.png'));
					$xhtmlCancelSubscription->assign("form.back", config::translate('form.back'));

					$message = $xhtmlCancelSubscription->get_content();
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
					$objUser	= new user();
					$arrFields	= array('uid','email','locale');
					$arrSearch	= array('registration_key'=>$_GET['k']);
					$row		= $objUser->search( $arrFields , $arrSearch );

					if(count($row) > 0 ) {
						if(isset($row['locale']) && trim($row['locale']) != '' ) {
							config::set('locale',$row['locale']);
						}

						$objUser = new user($row['uid']);
					$objUser->load();
					$objUser->set_active(0);
					$objUser->set_access_allowed(0);
					$objUser->save();

					$query ="UPDATE ";
					$query.="`subscriptions` ";
					$query.="SET ";
					$query.="`subscription_cancellation_date` = '".date('Y-m-d H:i:s')."' ";
					$query.="WHERE ";
					$query.="`user_uid` = '".$row['uid']."' ";
					$query.="LIMIT 1 ";
					database::query( $query );

					$this->mail_html(
					'jamie@languagenut.com,lucy@languagenut.com',
					'Subscriber cancelled!',
					'Oh dear! someone has lost their marbles...<br /><br /><b>Email:</b> '.$row['email']."<br />",
					'subs@languagenut.com',
					'',
					'',
					'',
					''
					);
					if($row['locale'] == 'dk') {
						$this->mail_html(
						'ec@englishcenter.dk',
						'Subscriber cancelled!',
						'Oh dear! someone has lost their marbles...<br /><br /><b>Email:</b> '.$row['email']."<br />",
						'subs@languagenut.com',
						'',
						'',
						'',
						''
						);
					}

					$translate = 'msg.subscription.cancel.success';
					$display = "display:none;";
				}


			}

			$body = make::tpl ('body.cancel.subscription')->assign(
			array(
			'display' => $display,
			'errors' => $message,
			'translate.back_to_homepage' => '',
			'intro_text' => config::translate($translate),
			'locale' => config::get('locale') . '/',
			'email' => $arrForm['email']['value'],
			'highlight:email' => ($arrForm['email']['error'] ? ' class="highlighted"' : '')
			)
			);

			/**
			* Fetch the standard public xhtml page template
			*/
			$skeleton = make::tpl ('skeleton.subscribe')->assign(
			array(
			'title' => $page->title(),
			'keywords' => $page->keywords(),
			'description' => $page->description(),
			'body' => $body,
			'background_url' => 'registration_bg.en.jpg',
			'locale' => config::get('locale')
			)
			);

			output::as_html($skeleton, true);
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

				$subject	= stripslashes($row['subject']);
				$from		= stripslashes($row['from']);
				$body		= stripslashes($row['body']);

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
			$header .="\nContent-Type: text/html; charset=iso-8859-15";
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