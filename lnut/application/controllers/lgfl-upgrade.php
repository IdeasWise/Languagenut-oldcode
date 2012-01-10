<?php

/**
 * lgfl-upgrade.php
 */
class lgfl_upgrade extends Controller {

	private $locale		= 'en';

	public function __construct() {
		parent::__construct();
		$this->locale = config::get('locale');
		$arrPaths = config::get('paths');
		if(isset($arrPaths[1]) && $arrPaths[1]=='eal') {
			$this->eal_upgrade();
		} else if(isset($arrPaths[1]) && $arrPaths[1]=='mfl') {
			$this->mfl_upgrade();
		} else if(isset($arrPaths[1]) && $arrPaths[1]=='thankyou') {
			$this->thankyou();
		} else {
			output::redirect(config::url());
		}
	}

	protected function eal_upgrade() {
		if(isset($_SESSION['user']['uid']) && isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights']!='student') {
			$arrPackages			= user::get_user_packages($_SESSION['user']['uid']);
			if(in_array('eal',$arrPackages)) {
				output::redirect(config::url());
			}
			if(isset($_GET['confirm']) && $_GET['confirm']=='true') {
				// check if eal subscription is exist with user package 
				if(!in_array('eal',$arrPackages)) {
					// invoice this school now
					$objSubscription = new subscriptions();
					$objSubscription->upgrade_lgfl_package(
						'eal',
						$_SESSION['user']['uid']
					);
					$this->send_notification_mail_to_lnut_admin('EAL');
				}
				output::redirect(config::url('lgfl-upgrade/thankyou/'));
			}
			$page = new page('upgrade');
			$title_url	= '';
			$title_alt	= '';
			$intro_text = '';
			$body = make::tpl('body.lgfl.upgrade.eal');
			$body->assign(
				array(
					'type'						=> 'school',
					'translate.back_to_homepage'=> '',
					'title_url'					=> $title_url,
					'title_alt'					=> $title_alt,
					'intro_text'				=> $intro_text,
					'locale'					=> $this->locale . '/'
				)
			);

			/**
			 * Fetch the standard public xhtml page template
			 */
			$skeleton = make::tpl('skeleton.landing');
			$skeleton->assign('pageID','selection');
			$skeleton->assign (
				array (
					'title'			=> $page->title(),
					'keywords'		=> $page->keywords(),
					'description'	=> $page->description(),
					'body'			=> $body,
					'selectionHeader' => ' | online langages in a nutshell',
					'locale'		=> config::get('locale')
				)
			);

			output::as_html($skeleton, true);
		} else {
			output::redirect(config::url());
		}
	}
	protected function mfl_upgrade() {
		if(isset($_SESSION['user']['uid']) && isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights']!='student') {
			$arrPackages			= user::get_user_packages($_SESSION['user']['uid']);
			if(in_array('standard',$arrPackages)) {
				output::redirect(config::url());
			}
			if(isset($_GET['confirm']) && $_GET['confirm']=='true') {
				// check if standard subscription is exist with user package 
				if(!in_array('standard',$arrPackages)) {
					// invoice this school now
					$objSubscription = new subscriptions();
					$objSubscription->upgrade_lgfl_package(
						'standard',
						$_SESSION['user']['uid']
					);
					$this->send_notification_mail_to_lnut_admin('mfl');
				}
				output::redirect(config::url('lgfl-upgrade/thankyou/'));
			}
			$page = new page('upgrade');
			$title_url	= '';
			$title_alt	= '';
			$intro_text = '';
			$body = make::tpl('body.lgfl.upgrade.mlf');
			$body->assign(
				array(
					'type'						=> 'school',
					'translate.back_to_homepage'=> '',
					'title_url'					=> $title_url,
					'title_alt'					=> $title_alt,
					'intro_text'				=> $intro_text,
					'locale'					=> $this->locale . '/'
				)
			);

			/**
			 * Fetch the standard public xhtml page template
			 */
			$skeleton = make::tpl('skeleton.landing');
			$skeleton->assign('pageID','selection');
			$skeleton->assign (
				array (
					'title'			=> $page->title(),
					'keywords'		=> $page->keywords(),
					'description'	=> $page->description(),
					'body'			=> $body,
					'selectionHeader' => ' | online langages in a nutshell',
					'locale'		=> config::get('locale')
				)
			);

			output::as_html($skeleton, true);
		} else {
			output::redirect(config::url());
		}
	}
	private function send_notification_mail_to_lnut_admin($package='mfl') {
		if(isset($_SESSION['user']['school_uid']) && is_numeric($_SESSION['user']['school_uid']) && $_SESSION['user']['school_uid']>0) {
			$subject	= 'INVOICE REQUEST';
			$objSchool	= new users_schools();
			$arrDetails = $objSchool->getSchoolFullDetails($_SESSION['user']['school_uid']);
			if(is_array($arrDetails) && count($arrDetails)) {
				$body ="<p>A LGFL user has requested invoice for ".strtoupper($package)." package.</p>";
				$body.="<p><strong>School:</strong>";
				$body.="<a href='".config::url('admin/users/profile/school/'.$arrDetails['uid'].'/')."'>".$arrDetails['school']."</a></p>";
				$body.="<p><strong>Email:</strong>".$arrDetails['email']."";
				$this->mail_html('jamie@languagenut.com',$subject,$body,'subs@languagenut.com');
			}
		}
		
	}
	
	private function thankyou() {
		/**
		 * Fetch the standard public xhtml page template
		 */

		$page = new page('upgrade');
		$title_url	= '';
		$title_alt	= '';
		$intro_text = '';
		$body = make::tpl('body.lgfl.upgrade.thankyou');
		$body->assign(
			array(
				'type'						=> 'school',
				'translate.back_to_homepage'=> '',
				'title_url'					=> $title_url,
				'title_alt'					=> $title_alt,
				'intro_text'				=> $intro_text,
				'locale'					=> $this->locale . '/'
			)
		);

		$skeleton = make::tpl('skeleton.landing');
		$skeleton->assign('pageID','selection');
		$skeleton->assign (
			array (
				'title'			=> $page->title(),
				'keywords'		=> $page->keywords(),
				'description'	=> $page->description(),
				'body'			=> $body,
				'selectionHeader' => ' | online langages in a nutshell',
				'locale'		=> config::get('locale')
			)
		);

		output::as_html($skeleton, true);
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