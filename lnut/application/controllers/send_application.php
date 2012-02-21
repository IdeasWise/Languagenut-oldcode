<?php

class send_application extends Controller {

	public function __construct() {
		parent::__construct();
		$this->LoadIndex();
	}

	protected function LoadIndex() {
		$body		= '<div class="page-content"><p><strong>Thank you for choosing to subscribe to languagenut.</strong></p><p>We\'ll send an invoice to you via email in the next 2 days.</p><p>In the meantime, we will leave your login details open so that you can enjoy uninterrupted access to all the resources.</p><p>Best wishes, the Languagenut Team.</p></div>';

		$sub_uid	= (isset($_GET['sub']) && strlen($_GET['sub']) > 0) ? $_GET['sub'] : '';
		if(strlen($sub_uid) > 0) {
			$sub_uid = explode('-',$sub_uid);
			$sub_uid = $sub_uid[1];
		}
		if(is_numeric($sub_uid) && (int)$sub_uid > 0) {
			/**
			 * ONE WEEK REMINDER: Schools
			 */
			/**
			 * GETTING RECORD FROM USERS_SCHOOLS TABLE
			 */
			$objSchool	= new users_schools();
			$arrFields	= array('uid','contact','name','school','user_uid','address_id');
			$Where		= array('uid'=>$sub_uid);
			$arrSchool	= $objSchool->search($arrFields , $Where);
			if(count($arrSchool) > 0) {
				/**
				 * GETTING RECORD FROM USER TABLE
				 */
				$objUser	= new user();
				$arrFields	= array('uid', 'email', 'registered_dts', 'username_open', 'password_open', 'locale');
				$Where		= array('uid' => $arrSchool['user_uid'], 'active' => 1 );
				$arrUser = $objUser->search( $arrFields, $Where );

				if( isset($arrUser['locale']) && trim($arrUser['locale']) != '' ) {
					config::set('locale',$arrUser['locale']);
				}

				$email_template = '';
				if(isset($arrUser['locale']) && trim($arrUser['locale']) != '') {
					$application_translation = new send_application_translation();
					$translation = array();
					$arrFields = array('introduction_text', 'email_subject', 'email_notification_text');
					$Where  = array('locale' => $arrUser['locale']);
					$translation = $application_translation->search($arrFields, $Where);
					if(isset($translation['email_notification_text']) && trim($translation['email_notification_text']) != '') {
						$email_template = str_replace(array('&#123;&#123;','&#125;&#125;'),array('{{','}}'),$translation['email_notification_text']);
						$body = '<div class="page-content">';
						$body .= str_replace(array('&#123;&#123;','&#125;&#125;'),array('{{','}}'),$translation['introduction_text']);
						$body .= '</div>';
					}
				}

				if(count($arrUser) > 0) {

					if(isset($arrSchool['address_id']) && $arrSchool['address_id'] > 0) {
						/**
						 * GETTING RECORD FROM USER TABLE
						 */
						$objAddress = new lib_property_address_uk();
						$arrFields = array('street_name_1','postcode');
						$Where  = array('uid'=>$arrSchool['address_id']);
						$arrAddress = $objAddress->search( $arrFields, $Where);
					}

					/**
					 * GETTING RECORD FROM SUBSCRIPTIONS TABLE
					 */
					$objSubscription	= new subscriptions();
					$arrFields			= array('user_uid', 'start_dts');
					$Where				= array('user_uid' => $arrSchool['user_uid']);
					$subs				= $objSubscription->search($arrFields, $Where );

					/**
					 * NOTIFY THE RESELLER FOR THIS USER
					 */
					$finance_email = '';
					$query = "SELECT `reseller_user_uid` FROM `reseller_sale` WHERE `sold_user_uid`='".$arrUser['uid']."' LIMIT 1";
					$result = database::query($query);
					if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
						$row = mysql_fetch_assoc($result);
						$reseller_user_uid = $row['reseller_user_uid'];
						$query = "SELECT `finance_email` FROM `profile_reseller` WHERE `iuser_uid`=$reseller_user_uid LIMIT 1";
						$result = database::query($query);
						if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
							$row = mysql_fetch_assoc($result);
							$finance_email = $row['finance_email'];
						}
					}

					if(count($subs) > 0 ) {
						$schools[$arrUser['uid']] = array (
							'school_uid'	=> $arrSchool['uid'],
							'email'			=> $arrUser['email'],
							'reg_date'		=> $arrUser['registered_dts'],
							'contact'		=> stripslashes($arrSchool['contact']),
							'username'		=> stripslashes($arrUser['username_open']),
							'password'		=> stripslashes($arrUser['password_open']),
							'name'			=> stripslashes($arrSchool['name']),
							'school'		=> stripslashes($arrSchool['school']),
							'address'		=> stripslashes($arrAddress['street_name_1']),
							'postcode'		=> stripslashes($arrAddress['postcode']),
							'language'		=> stripslashes($arrUser['locale']),
							'substart'		=> $subs['start_dts']
						);
						foreach($schools as $user_uid=>$array) {
							$subscription = date('d/m/Y',(strtotime($array['substart'])+14*24*60*60));
							$this->sendSubscriptionRequest($user_uid, $array, $subscription, $email_template, $finance_email);
						}
					}
				}

			}
		}

		$skeleton = make::tpl('skeleton.subscribe');
		$page = new page('send_application');

		$skeleton->assign(
			array(
				'title'				=> $page->title(),
				'keywords'			=> $page->keywords(),
				'description'		=> $page->description(),
				'body'				=> stripslashes($body),
				'background_url'	=> 'registration_bg.en.jpg',
				'locale'			=> config::get('locale')
			)
		);
		output::as_html($skeleton, true);
	}

	protected function mail_html ($to='',$subject='',$message='',$from='',$receiptname='',$receiptmail='',$cc='',$bcc='') {
		$header ="Content-Transfer-Encoding: 8bit";
		$header .="\nContent-Type: text/html; charset=utf-8";
		if($from != '') {
			$header .="\nFrom: ".$from;
		}
		if($cc != '') {
			$header .= "\nCc: ".$cc;
		}
		if($bcc != '') {
			$header .= "\nBcc: ".$bcc;
		}
		if($receiptname != '' && $receiptmail != '') {
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

	protected function sendSubscriptionRequest($user_uid=0, $user_data=array(), $subscription='', $email_template = '', $finance_email = '') {
		$template[] = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
		$template[] = '<html>';
		$template[] = '<head></head>';
		$template[] = '<body bgcolor="#eee" style="background: url(\'{{ images }}email-bg.jpg\') center no-repeat;padding:50px;">';
		$template[] = '<table width="100%">';
		$template[] = '<tr>';
		$template[] = '<td align="center">';
		$template[] = '<table style="height:400px;background-color:fff;border:2px solid #d13300;">';
		$template[] = '<tr>';
		$template[] = '<td width="450" valign="top">';
		$template[] = '<div style="padding:40px;">';
		$template[] = '<img src="{{ images }}email-logo.gif" alt="Languagenut.com" />';
		$template[] = '<br /><br />';
		$template[] = 'Dear Jamie,';
		$template[] = '<br /><br />';
		$template[] = 'A school has requested a subscription form to be sent.<br /><br />';
		$template[] = 'Please send a form to {{ name }}, who has the following details:<br />';
		$template[] = '\'Whole school\' access details:<br />';
		$template[] = 'Username: {{ username_open }}<br />';
		$template[] = 'Password: {{ password_open }}<br />';
		$template[] = '<br /><br />';
		$template[] = 'School details:<br />';
		$template[] = '{{ address }}<br />';
		$template[] = '{{ postcode }}<br />';
		$template[] = '<br /><br />';
		$template[] = 'Subscription to start on: {{ subscription }}<br />';
		$template[] = '<br /><br />';
		$template[] = 'A yearly subscription to languagenut.com costs:<br /> <br />';
		$template[] = 'UK<br />';
		$template[] = '&pound;80 + vat for primaries, &pound;120 + vat for secondaries<br /><br />';
		$template[] = 'Australia<br />';
		$template[] = 'AUD$125 for schools with less than 300 students, AUD$160 for schools with more than 300 students. <br /><br />';
		$template[] = 'New Zealand<br />';
		$template[] = 'NZ$150 for schools with less than 300 students, NZ$199 for schools with more than 300 students. <br />  ';
		$template[] = '<br /><br />';
		$template[] = 'Yours,';
		$template[] = '<br /><br />';
		$template[] = 'The languagenut team';
		$template[] = '</div>';
		$template[] = '</td><td width="200" align="right" valign="top" cellpadding="20">';
		$template[] = '<div style="padding:80px 40px; 40px 80px;">';
		$template[] = '<img src="{{ images }}email-nut.jpg" alt="The Languagenut nut!" />';
		$template[] = '</div>';
		$template[] = '</td>';
		$template[] = '</tr>';
		$template[] = '</table>';
		$template[] = '</td>';
		$template[] = '</tr>';
		$template[] = '</table>';
		$template[] = '</body>';
		$template[] = '</html>';
		if( trim($email_template) != '') {
			$template = $email_template;
		} else {
			$template = implode("\n",$template);
		}
		$template = str_replace(
			array(
				'{{ images }}',
				'{{ name }}',
				'{{ email }}',
				'{{ username_open }}',
				'{{ password_open }}',
				'{{ address }}',
				'{{ postcode }}',
				'{{ base }}',
				'{{ subscription }}'
			),
			array(
				'http://www.languagenut.com/images/',
				$user_data['name'],
				$user_data['email'],
				$user_data['username'],
				$user_data['password'],
				$user_data['school'].',<br />'.$user_data['address'],
				$user_data['postcode'],
				'http://www.languagenut.com/',
				$subscription
			),
			$template
		);

		$to = 'jamie@languagenut.com';
		$to.= ($finance_email != '' ? ','.$finance_email : '');

		$this->mail_html ($to,'INVOICE REQUEST',$template,$user_data['email'],'','','','');
		//$this->mail_html ('jamie@languagenut.com','INVOICE REQUEST',$template,$user_data['email'],'','','','');
		//mail_html ('workstation@mystream.co.uk','INVOICE REQUEST',$template,$user_data['email'],'','','','');
	}

}
?>
