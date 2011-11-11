<?php

class lnutCron {

	private $localeOverride = '';

	public function __construct($override='') {
		$localeOverride = $override;
	}

	public function runDailyCron($arrLocale=array()) {
		$query ="SELECT ";
		$query.="`U`.`uid`, ";
		$query.="`U`.`locale`, ";
		$query.="`U`.`email`, ";
		$query.="`SC`.`name`, ";
		$query.="`SC`.`school`, ";
		$query.="`subscriptions`.`start_dts`, ";
		$query.="`subscriptions`.`expires_dts` ";
		$query.="FROM ";
		$query.="`user` AS `U`, ";
		$query.="`users_schools` AS `SC`, ";
		$query.="`subscriptions` ";
		$query.="WHERE ";
		$query.="`subscriptions`.`user_uid`=`SC`.`user_uid` ";
		$query.="AND ";
		$query.="`U`.`uid`=`SC`.`user_uid` ";
		$query.="AND ";
		$query.="expires_dts > '".date('Y-m-d 23:59:59',strtotime('+28 days'))."' ";
		$query.="AND ";
		$query.="`subscriptions`.`verified`=1 ";
		
		if(is_array($arrLocale) && count($arrLocale)) {
			$query.="AND ";
			$query.="`U`.`locale` IN('".implode("','",$arrLocale)."') ";
		}
	/*
		if($this->localeOverride != '') {
			$query.= "AND ";
			$query.= "`U`.`locale`='".mysql_real_escape_string(strtolower($this->localeOverride))."' ";
		}
		*/
		$query.="GROUP BY `subscriptions`.`user_uid` ";
		$query.="ORDER BY `subscriptions`.`start_dts` DESC";
		$result = database::query($query);

		$objEmailTemplates = new email_templates();

		if($result && mysql_error() == '') {
			if(mysql_num_rows($result) > 0) {
				while($array = mysql_fetch_assoc($result)) {
					$schools[] = array(
						'id'			=> $array['uid'],
						'locale'		=> $array['locale'],
						'name'			=> stripslashes($array['name']),
						'email'			=> $array['email'],
						'school_name'	=> stripslashes($array['school']),
						'start'			=> $array['start_dts'],
						'end'			=> $array['expires_dts']
					);
				}
				$deactivate = array ();
				// send them all an email informing them that they should now return to the site and purchase another subscription
				foreach($schools as $index=>$details) {
					if(date('Y-m-d',strtotime($details['end'])) == date('Y-m-d',strtotime('+28 days')) ) {
						// for those where the date is between 28 and 27 days from now, tell them they have 4 weeks to renew
						/*
						$this->mail_html(
							$details['email'],
							'Language Nut Renewal Reminder',
							"<p>Dear ".$details['name'].", </p>\n<p>Your Subscription is due to expire within the next 28 days.</p><p>Please contact us to renew your subscription for another year</p>",
							'subscriptions@languagenut.com'
						);*/
						$arrEmail = array();
						$arrEmail = $objEmailTemplates->getEmailTemplate(
							'daily.28.days.reminder',
							$details['locale']
						);
						$message = '';
						if(is_array($arrEmail) && count($arrEmail)) {
							$message = str_replace(
								array('{{ name }}'),
								array($details['name']),
								$arrEmail['body']
							);
							$this->mail_html(
								$details['email'],
								$arrEmail['subject'],
								$message,
								$arrEmail['from']
							);
						}
					}
					if(date('Y-m-d',strtotime($details['end'])) == date('Y-m-d',strtotime('+14 days')) ) {
						// for those where the date is between 14 and 13 days from now, tell them they have 2 weeks to renew
						/*
						$this->mail_html(
							$details['email'],
							'Language Nut Renewal Reminder',
							"<p>Dear ".$details['name'].", </p>\n<p>Your Subscription is due to expire within the next 14 days.</p><p>Please contact us to renew your subscription for another year</p>",
							'subscriptions@languagenut.com'
						);*/

						$arrEmail = array();
						$arrEmail = $objEmailTemplates->getEmailTemplate(
							'daily.14.days.reminder',
							$details['locale']
						);
						$message = '';
						if(is_array($arrEmail) && count($arrEmail)) {
							$message = str_replace(
								array('{{ name }}'),
								array($details['name']),
								$arrEmail['body']
							);
							$this->mail_html(
								$details['email'],
								$arrEmail['subject'],
								$message,
								$arrEmail['from']
							);
						}

					}
					if(date('Y-m-d',strtotime($details['end'])) == date('Y-m-d',strtotime('+7 days')) ) {
						// for those where the date is between 7 and 6 days from now, tell them they have 1 week to renew
						/*
						$this->mail_html(
							$details['email'],
							'Language Nut Renewal Reminder',
							"<p>Dear ".$details['name'].", </p>\n<p>Your Subscription is due to expire within the next 7 days.</p><p>Please contact us to renew your subscription for another year</p>",
							'subscriptions@languagenut.com'
						);
						*/

						$arrEmail = array();
						$arrEmail = $objEmailTemplates->getEmailTemplate(
							'daily.7.days.reminder',
							$details['locale']
						);
						$message = '';
						if(is_array($arrEmail) && count($arrEmail)) {
							$message = str_replace(
								array('{{ name }}'),
								array($details['name']),
								$arrEmail['body']
							);
							$this->mail_html(
								$details['email'],
								$arrEmail['subject'],
								$message,
								$arrEmail['from']
							);
						}

					}
					if(date('Y-m-d',strtotime($details['end'])) == date('Y-m-d',strtotime('1 day')) ) {
						// for those where the date is between 86400 and now, tell them they have only 1 day left to renew
						/*
						$this->mail_html(
							$details['email'],
							'Language Nut Renewal Reminder',
							"<p>Dear ".$details['name'].", </p>\n<p>Your Subscription is due to expire within a day.</p><p>Please contact us to renew your subscription for another year</p>",
							'subscriptions@languagenut.com'
						);
						*/
						$arrEmail = array();
						$arrEmail = $objEmailTemplates->getEmailTemplate(
							'daily.1.day.reminder',
							$details['locale']
						);
						$message = '';
						if(is_array($arrEmail) && count($arrEmail)) {
							$message = str_replace(
								array('{{ name }}'),
								array($details['name']),
								$arrEmail['body']
							);
							$this->mail_html(
								$details['email'],
								$arrEmail['subject'],
								$message,
								$arrEmail['from']
							);
						}
					}
					if(date('Y-m-d',strtotime($details['end'])) == date('Y-m-d',strtotime('-1 days')) ) {
					//if($details['end'] - $today >= -86400 && $details['end'] - $today < 0) {
						// find all of those schools that have a subscription that has ended in the last 1 day
						// let them know their account has been deactivated
						/*
						$this->mail_html(
							$details['email'],
							'Language Nut Deactivation Notice',
							"<p>Dear ".$details['name'].", </p>\n<p>Your Subscription has expired and your account is no longer active. If you would like to renew your subscription, please contact us.</p>",
							'subscriptions@languagenut.com'
						);*/
						$arrEmail = array();
						$arrEmail = $objEmailTemplates->getEmailTemplate(
							'user.deactivationnotice',
							$details['locale']
						);
						$message = '';
						if(is_array($arrEmail) && count($arrEmail)) {
							$message = str_replace(
								array('{{ name }}'),
								array($details['name']),
								$arrEmail['body']
							);
							$this->mail_html(
								$details['email'],
								$arrEmail['subject'],
								$message,
								$arrEmail['from']
							);
						}
						$deactivate[] = $details['id'];
					}
				}
				if(sizeof($deactivate) > 0) {
					$in = implode(', ',$deactivate);
					//$sql = "UPDATE `user` SET `active`=0 WHERE `uid` IN (".$in.")";
					//$db->query($sql);
				}
			}
		}
	}

	public function runDailyReminderCron($arrLocale=array()) {
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
		$query.= "`user`.`locale` AS `language_prefix`, ";
		$query.= "`user`.`reminder_oneweek_sent`, ";
		$query.= "`user`.`reminder_twoday_sent` ";
		$query.= "FROM ";
		$query.= "`user`, ";
		$query.= "`users_schools` ";
		$query.= "WHERE ";
		$query.= "`user`.`active`=1 ";
		if(is_array($arrLocale) && count($arrLocale)) {
			$query.="AND ";
			$query.="`user`.`locale` IN('".implode("','",$arrLocale)."') ";
		}
		$query.= "AND `users_schools`.`user_uid`=`user`.`uid`";
		// echo $query; exit;
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
						'substart'		=> '',
						'oneweek_sent'	=> $row['reminder_oneweek_sent'],
						'twoday_sent'	=> $row['reminder_twoday_sent']
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

								if($diff['days']==6 && $array['oneweek_sent']==0) {
									//$message.= '7 days<br />';

									$this->sendOneWeekReminder($user_uid, $array);

								} else if($diff['days']==11 && $array['twoday_sent']==0) {
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
			$query = "UPDATE `user` SET `reminder_oneweek_sent`=1 WHERE `uid`=".$user_uid." LIMIT 1";
			$result = database::query($query);
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
			$query = "UPDATE `user` SET `reminder_twoday_sent`=1 WHERE `uid`=".$user_uid." LIMIT 1";
			$result = database::query($query);
		}
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
			$headers .= "Disposition-Notification-To: subs<subs@languagenut.com>\n";
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