<?php

/**
 * reminder.php
 */

class Reminder extends Controller {

	public function __construct () {
		parent::__construct();
		$this->index();
	}

	protected function index () {
		$message = '';
		$query = "SELECT ";
		$query.= "`user`.`uid`, ";
		$query.= "`user`.`locale`, ";
		$query.= "`user`.`email`, ";
		$query.= "`user`.`registered_dts`, ";
		$query.= "`users_schools`.`uid` AS `school_uid`, ";
		$query.= "`users_schools`.`name` AS `contact`, ";
		$query.= "`user`.`username_open`, ";
		$query.= "`user`.`password_open`, ";
		$query.= "`users_schools`.`name`, ";
		$query.= "`users_schools`.`school`, ";
		$query.= "`users_schools`.`address`, ";
		$query.= "`users_schools`.`postcode`, ";
		$query.= "`user`.`locale` AS `language_prefix` ";
		$query.= "FROM ";
		$query.= "`user`, ";
		$query.= "`users_schools` ";
		$query.= "WHERE ";
		$query.= "`user`.`active`=1 ";
		$query.= "AND `users_schools`.`user_uid`=`user`.`uid`";

		//$message.= $query.'<br />';

		$result = database::query($query);

		if($result && mysql_error()=='') {
			//$message.= 'No Errors in Query<br />';
			if(mysql_num_rows($result) > 0) {
				//$message.= 'Number of Matches:'.mysql_num_rows($result).'<br />';
				$schools = array ();
				while($row = mysql_fetch_assoc($result)) {
					$schools[$row['uid']] = array (
						'locale'		=> $row['locale'],
						'school_uid'	=> $row['school_uid'],
						'email'			=> $row['email'],
						'reg_date'		=> $row['registered_dts'],
						'contact'		=> stripslashes($row['contact']),
						'username'		=> stripslashes($row['username_open']),
						'password'		=> stripslashes($row['password_open']),
						'name'			=> stripslashes($row['name']),
						'school'		=> stripslashes($row['school']),
						'address'		=> stripslashes($row['address']),
						'postcode'		=> stripslashes($row['postcode']),
						'language'		=> stripslashes($row['language_prefix']),
						'substart'		=> ''
					);
				}
				if(count($schools) > 0) {
					//$message.='Find Subscriptions<br />';

					$query = "SELECT ";
					$query.= "`user_uid`, ";
					$query.= "`start_dts` ";
					$query.= "FROM ";
					$query.= "`subscriptions` ";
					$query.= "WHERE ";
					$query.= "`user_uid` IN (".implode(',',array_keys($schools)).") ";
					$query.= "AND `verified`=0 ";
					$query.= "ORDER BY ";
					$query.= "`user_uid` ASC, `start_dts` DESC";

					//$message.= $query.'<br />';

					$result = database::query($query);

					if($result && mysql_error()=='') {
						$message.= 'No Errors<br />';
						if(mysql_num_rows($result) > 0) {
							//$message.= 'Number of Matches:'.mysql_num_rows($result).'<br />';

							$last_user = '';
							$last_date = '';

							while($row = mysql_fetch_assoc($result)) {
								$this_user = $row['user_uid'];
								$this_date = $row['start_dts'];

								if($this_user != $last_user) {
									$schools[$this_user]['substart'] = $this_date;
								}

								$last_user = $this_user;
								$last_date = $this_date;
							}

							// If Today - substart == 7, we send a formatted email
							$today = date('Y-m-d H:i:s');

							foreach($schools as $user_uid=>$array) {

								$diff=$this->get_time_difference($array['substart'],$today);

								//$message.= 'User: '.$user_uid.'<br />';
								//$message.= 'Start:'.$array['substart'].'<br />';
								//$message.= 'Today:'.$today.'<br />';
								//$message.= 'Diff:'.(is_array($diff)?'Valid':'Invalid').'<br />';
								if(is_array($diff)) {
									foreach($diff as $key=>$val) {
										//$message.= 'Key:'.$key.' = Val:'.$val.'<br />';
									}
								} else {
									//$message.= 'DUD<br />';
								}

								if($diff['days']==6) {
									//$message.= '7 days<br />';

									$this->sendOneWeekReminder($user_uid, $array);

								} else if($diff['days']==11) {
									//$message.= '12 days<br />';

									$this->sendTwoDayReminder($user_uid, $array);

								}
							}
						} else {
							$message.= 'Number of Matches:'.mysql_num_rows($result).'<br />';
						}
					} else {
						$message.= 'Query Error:'.mysql_error().'<br />';
					}/**/
				} else {
					$message.= 'No Schools<br />';
				}
			} else {
				$message.= 'Number of Matches:'.mysql_num_rows($result).'<br />';
			}

		} else {
			$message.= 'Query Error:'.mysql_error().'<br />';
		}
	}
	
	private function get_time_difference( $start='', $end='' ) {
		$uts['start']	= strtotime( $start );
		$uts['end']		= strtotime( $end );
	
		if( $uts['start']!==-1 && $uts['end']!==-1 ) {
			if( $uts['end'] >= $uts['start'] ) {
				$diff = $uts['end'] - $uts['start'];
				if( $days=intval((floor($diff/86400))) ) {
					$diff = $diff % 86400;
				}
				if( $hours=intval((floor($diff/3600))) ) {
					$diff = $diff % 3600;
				}
				if( $minutes=intval((floor($diff/60))) ) {
					$diff = $diff % 60;
				}
				$diff    =    intval( $diff );            
				return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
			} else {
				//trigger_error( "Ending date/time is earlier than the start date/time", E_USER_WARNING );
			}
		} else {
			//trigger_error( "Invalid date/time data detected", E_USER_WARNING );
		}
		return( false );
	}
	
	private function sendOneWeekReminder($user_uid=0,$user_data=array()) {

		$objEmailTemplates = new email_templates();
		$arrEmail = array();
		$arrEmail = $objEmailTemplates->getEmailTemplate(
			'sendoneweekreminder',
			$user_data['locale']
		);

		if(is_array($arrEmail) && count($arrEmail)) {
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
					'{{ registration_key }}'
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
					$user_data['school_uid']
				),
				$arrEmail['body']
			);
		
			$this->mail_html(
				$user_data['email'],
				$arrEmail['subject'],
				$template,
				$arrEmail['from'],
				'',
				'',
				'',
				''
			);
			$this->mail_html(
				'workstation@mystream.co.uk',
				$arrEmail['subject'],
				$template,
				$arrEmail['from'],
				'',
				'',
				'',
				''
			);
		}
	}
	
	function sendTwoDayReminder($user_uid=0, $user_data=array()) {
		$objEmailTemplates = new email_templates();
		$arrEmail = array();
		$arrEmail = $objEmailTemplates->getEmailTemplate(
			'sendtwodayreminder',
			$user_data['locale']
		);

		if(is_array($arrEmail) && count($arrEmail)) {
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
					'{{ registration_key }}'
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
					$user_data['school_uid']
				),
				$arrEmail['body']
			);
		
			$this->mail_html(
				$user_data['email'],
				$arrEmail['subject'],
				$template,
				$arrEmail['from'],
				'',
				'',
				'',
				''
			);
			$this->mail_html(
				'workstation@mystream.co.uk',
				$arrEmail['subject'],
				$template,
				$arrEmail['from'],
				'',
				'',
				'',
				''
			);
		}
	}


	private function sendOneWeekReminder_old($user_uid=0,$user_data=array()) {
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
		$template[] = 'Dear {{ name }},';
		$template[] = '<br /><br />';
		$template[] = 'If you have already chosen to subscribe, thank you! Please accept our apologies for this email and disregard - your subscription is being processed';
		$template[] = '<br /><br />';
		$template[] = 'We hope that you are enjoying your languagenut trial. You have 1 week left, so make sure that you have a look at the interactive stories and songs, as well as the games!';
		$template[] = '<br /><br />';
		$template[] = 'Your \'whole school\' access details are:<br />';
		$template[] = 'Username: {{ username_open }}<br />';
		$template[] = 'Password: {{ password_open }}<br />';
		$template[] = '<br /><br />';
		$template[] = 'We have your details as:<br />';
		$template[] = '{{ address }}<br />';
		$template[] = '{{ postcode }}<br />';
		$template[] = '<br /><br />';
		$template[] = 'To confirm your subscription now simply click the link below to download your form and send it off with your payment. You will benefit from: <br /> <br />';
		$template[] = '- all languages<br /><br />';
		$template[] = '- 24/7 access for all teachers and students in your school<br /><br />';
		$template[] = '- a growing range of resources';
		$template[] = '<br /><br />';
		$template[] = 'Please click on the link below for your subscription form:<br />';
		//$template[] = '<a href="http://www.languagenut.com/year_subscription_form.pdf">Download Subscription Form</a>';
		$template[] = '<a href="http://www.languagenut.com/send-application.php?sub=1582-{{ registration_key }}">Invoice me now</a>';
		$template[] = '<br /><br />';
		$template[] = 'A yearly subscription to languagenut.com costs:<br /><br />';
		$template[] = 'UK<br />';
		$template[] = '&pound;80 for primaries, &pound;120 for secondaries<br /><br />';
		$template[] = 'Australia<br />';
		$template[] = 'AUD$160 for schools with less than 300 students, AUD$200 for schools with more than 300 students. <br /><br />';
		$template[] = 'New Zealand<br />';
		$template[] = 'NZ$199 + GST ($223.88) for schools with less than 300 students, NZ$250 + GST ($281.25) for schools with more than 300 students. <br /><br />';
		$template[] = 'EU (NOT UK)<br />';
		$template[] = '&euro;120 for primary or primary equivalent, &euro;175 for secondary or secondary equivalent.<br />';
		$template[] = '<br /><br />';
		$template[] = 'Please click on the link below if you wish to end your registration:<br />';
		$template[] = '<a href="{{ base }}cancel/school/{{ registration_key }}">end my registration</a>';
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
	
		$template = implode("\n",$template);
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
				'{{ registration_key }}'
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
				$user_data['school_uid']
			),
			$template
		);
	
		$this->mail_html ($user_data['email'],'LanguageNut: Subscription Reminder',$template,'Subscriptions <jamie@languagenut.com>','','','','');
		$this->mail_html ('workstation@mystream.co.uk','LanguageNut: Subscription Reminder',$template,'Subscriptions <jamie@languagenut.com>','','','','');
	}
	
	function sendTwoDayReminder_old($user_uid=0, $user_data=array()) {
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
		$template[] = 'Dear {{ name }},';
		$template[] = '<br /><br />';
		$template[] = 'If you have already chosen to subscribe, thank you! Please accept our apologies for this email and disregard - your subscription is being processed';
		$template[] = '<br /><br />';
		$template[] = 'You now have 48 hours left of your languagenut trial. <br /> ';
		$template[] = 'Confirm your subscription now. Simply click the link below to download your subscription form, and send off the completed form together with your payment.';
		$template[] = '<br /><br />';
		//$template[] = '<a href="http://www.languagenut.com/year_subscription_form.pdf">Download Subscription Form</a>';
		$template[] = '<a href="http://www.languagenut.com/send-application.php?sub=1582-{{ registration_key }}">Invoice me now</a>';
		$template[] = '<br /><br />';
		$template[] = 'Your \'whole school\' access details are:<br />';
		$template[] = 'Username: {{ username_open }}<br />';
		$template[] = 'Password: {{ password_open }}<br />';
		$template[] = '<br /><br />';
		$template[] = 'We have your details as:<br />';
		$template[] = '{{ address }}<br />';
		$template[] = '{{ postcode }}<br />';
		$template[] = '<br /><br />';
		$template[] = 'A yearly subscription to languagenut.com costs:<br /> <br />';
		$template[] = 'UK<br />';
		$template[] = '&pound;80 for primaries, &pound;120 for secondaries<br /><br />';
		$template[] = 'Australia<br />';
		$template[] = 'AUD$160 for schools with less than 300 students, AUD$200 for schools with more than 300 students. <br /><br />';
		$template[] = 'New Zealand<br />';
		$template[] = 'NZ$199 + GST ($223.88) for schools with less than 300 students, NZ$250 + GST ($281.25) for schools with more than 300 students. <br /><br />';
		$template[] = 'EU (NOT UK)<br />';
		$template[] = '&euro;120 for primary or primary equivalent, &euro;175 for secondary or secondary equivalent.<br />';
		$template[] = '<br /><br />';
		$template[] = 'To end your registration click the link below:<br />';
		$template[] = '<a href="{{ base }}cancel/school/{{ registration_key }}">end my registration</a>';
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
	
		$template = implode("\n",$template);
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
				'{{ registration_key }}'
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
				$user_data['school_uid']
			),
			$template
		);
	
		$this->mail_html ($user_data['email'],'LanguageNut: Subscription Reminder',$template,'Subscriptions <jamie@languagenut.com>','','','','');
		$this->mail_html ('workstation@mystream.co.uk','LanguageNut: Subscription Reminder',$template,'Subscriptions <jamie@languagenut.com>','','','','');
	}

	private function mail_html ($to='',$subject='',$message='',$from='',$receiptname='',$receiptmail='',$cc='',$bcc='') {
		$header ="Content-Transfer-Encoding: 8bit";
		$header .="\nContent-Type: text/html; charset=iso-8859-15";
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
			$headers .= "Disposition-Notification-To: Your Name<info@languagenut.com>\n";
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