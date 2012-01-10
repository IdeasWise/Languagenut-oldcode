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
		}
	}

	protected function eal_upgrade() {
		if(isset($_SESSION['user']['uid']) && isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights']!='student') {
			$arrPackages			= user::get_user_packages($_SESSION['user']['uid']);
			if(isset($_GET['confirm']) && $_GET['confirm']=='true') {
				// check if eal subscription is exist with user package 
				if(!in_array('eal',$arrPackages)) {
					echo '<pre>';
					print_r($_SESSION);
					echo '</pre>';
				}
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
	protected function mfl_upgrade() {
		if(isset($_SESSION['user']['uid']) && isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights']!='student') {
			$arrPackages			= user::get_user_packages($_SESSION['user']['uid']);
			if(isset($_GET['confirm']) && $_GET['confirm']=='true') {
				// check if standard subscription is exist with user package 
				if(!in_array('standard',$arrPackages)) {
					echo '<pre>';
					print_r($_SESSION);
					echo '</pre>';
				}
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

	protected function index() {
		if(isset($_SESSION['user']['uid']) && isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights']=='school') {
			$arrSubscription =subscriptions::getUserSubscriptionDetails($_SESSION['user']['uid']);
			if(is_array($arrSubscription) && count($arrSubscription)) {
				if($_SESSION['user']['uid']==$arrSubscription['user_uid']) {
					$page = new page('upgrade');
					$title_url	= '';
					$title_alt	= '';
					$intro_text = '';
					$standard_text = '';
					$eal_text = '';
					/**
					 * Fetch the page details
					 */
					$arrPackages = subscriptions::getAllSubscribedPackages();
					if(is_array($arrPackages) && count($arrPackages)) {
						
						
						if($arrSubscription['package_token'] == 'gaelic' && $arrPackages[0]=='gaelic') {
							if(isset($_GET['upgrade']) && $_GET['upgrade']=='gtos') {
								$objSubsription = new subscriptions($arrSubscription['uid']);
								if($objSubsription->get_valid()) {
									$objSubsription->load();
									$objSubsription->set_package_token('standard');
									if(isset($_SESSION['user']['package_token'])) {
										$_SESSION['user']['package_token'] = 'standard';
									}
									$objSubsription->save();
									output::redirect(config::url('upgrade/success'));
								}
							}
							$body = make::tpl('body.upgrade.gaelic');
						} else {
							if(isset($_GET['upgrade']) && in_array($_GET['upgrade'],array('standard','eal')) && count($arrPackages)==1) {
								$objSubsription = new subscriptions();
								if($objSubsription->upgradeuserPackage($_GET['upgrade'])) {
									output::redirect(config::url('upgrade/success'));
								}
							}
							if(in_array('standard',$arrPackages)) {
								$standard_text = 'you already subscribe to this resource';
							} else {
								$standard_text = '<a href="'.config::url('upgrade/?upgrade=standard').'">Free trial this resource</a>';
							}
							if(in_array('eal',$arrPackages)) {
								$eal_text = 'you already subscribe to this resource';
							} else {
								$eal_text = '<a href="'.config::url('upgrade/?upgrade=eal').'">Free trial this resource</a>';
							}
							$body = make::tpl('body.upgrade');
						}
						$page = new page('upgrade');
						$body->assign(
							array(
								'type'						=> 'school',
								'translate.back_to_homepage'=> '',
								'title_url'					=> $title_url,
								'title_alt'					=> $title_alt,
								'intro_text'				=> $intro_text,
								'locale'					=> $this->locale . '/',
								'standard_text'				=> $standard_text,
								'eal_text'					=> $eal_text
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
				} else {
					output::redirect(config::url());
				}
			} else {
				output::redirect(config::url());
			}
		} else {
			output::redirect(config::url());
		}
	}
	private function thankyou() {

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