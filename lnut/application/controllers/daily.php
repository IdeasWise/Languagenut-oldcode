<?php

/**
 * daily.php
 */

class Daily extends Controller {

	public function __construct () {
		parent::__construct();
		$this->index();
	}

	protected function index () {
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
//					if($details['end'] - $today >= -86400 && $details['end'] - $today < 0) {
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
		//			$sql = "UPDATE `user` SET `active`=0 WHERE `uid` IN (".$in.")";
		//			$db->query($sql);
				}
			}
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