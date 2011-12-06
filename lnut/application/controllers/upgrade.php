<?php

/**
 * subscribe.php
 */
class Upgrade extends Controller {

	private $locale		= 'en';
	private $type		= 'homeuser';
	private $typegiven	= false;

	public function __construct() {
		parent::__construct();
		$paths = config::get('paths');
		$this->locale = config::get('locale');

		if(isset($this->locale) && strlen($this->locale) > 0) {
			if (isset($paths[1]) && in_array($paths[1], array('homeuser', 'school'))) {
				$this->type = $paths[1];
				$this->typegiven = true;
			}
			$this->set_locale();
		} else {
			output::redirect(config::url('upgrade/'));
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
		$this->show_upgrade_select();
	}

	protected function show_upgrade_select() {
		/**
		* Determine which stage of the upgrade process we're trying to complete
		*/
		
		if(isset($_POST['products']) && count($_POST['products']) > 0) {
			$stage = 2;												 
		} else {
			// set errors if we havn't received any packeges on submit
			if(isset($_POST['submit'])) {
				$_SESSION['errors'] = "You need to select a product.<p>&nbsp;</p>";
			}
			$stage = 1;
		}
		$method = 'show_upgrade_' . $stage;
		$this->$method();
	}

	protected function show_upgrade_1() {
		/**
		 * Check SESSION variables
		 */
		 
		// error display
		$errors = '';
		if(isset($_SESSION['errors'])) { $errors = $_SESSION['errors']; unset($_SESSION['errors']); }
		 
		// re-key user type array
		foreach ($_SESSION['user']['user_type'] as $key => $value) {
			$user_types[] = $value; 
		}
		
		// check for school user type
		if(in_array('school',$user_types)) {
		 
			if(isset($_SESSION['user']) && $_SESSION['user']['uid'] > 0) {
				$user_uid = $_SESSION['user']['uid'];
				$school_uid = $_SESSION['user']['school_uid'];
				
				// get the existing subscriptions this user has
				$subscriptions = array();
				$subscriptions = subscriptions::getAllUserSubscriptionsDetails($user_uid);
				
				$form = '';
				if(false !== ($products = product_locale::getByLocale())) {
					foreach ($products as $product) {
						// if they have the subscription already, skip it
						if(in_array($product['uid'],$subscriptions)) {
							$form .= '<input name="products[]" type="checkbox" value="'.$product['uid'].'" checked="checked" disabled> '.stripslashes($product['name'])."<br>";	
						} else {
							$form .= '<input name="products[]" type="checkbox" value="'.$product['uid'].'"> '.stripslashes($product['name'])."<br>";
						}
					}
				}
				
				$title_url	= '';
				$title_alt	= '';
				$intro_text = '';
				
				/**
				 * Fetch the page details
				 */
				$page = new page('upgrade');
	
				$body = make::tpl('body.upgrade');
				$body->assign(
					array(
						'type'						=> 'school',
						'translate.back_to_homepage'=> '',
						'title_url'					=> $title_url,
						'title_alt'					=> $title_alt,
						'intro_text'				=> $intro_text,
						'form'						=> $form,
						'errors'					=> $errors,
						'locale'					=> $this->locale . '/',
					)
				);
	
				/**
				 * Fetch the standard public xhtml page template
				 */
				$skeleton = make::tpl('skeleton.upgrade');
				$skeleton->assign(
					array(
						'title'				=> $page->title(),
						'keywords'			=> $page->keywords(),
						'description'		=> $page->description(),
						'body'				=> $body,
						'background_url'	=> 'registration_bg.en.jpg',
						'locale'			=> $this->locale
					)
				);
	
				output::as_html($skeleton, true);			
			} else {
				output::redirect(config::url());
			}
		} else { // eof check for school user type 
			output::redirect(config::url());
		}
		
	}
	
	protected function show_upgrade_2() {
		if(isset($_SESSION['user']) && $_SESSION['user']['uid'] > 0) {
			if(isset($_POST['products']) && count($_POST['products']) >0) {
	
				// get session variables for this user
				$user_uid = $_SESSION['user']['uid'];
				$reseller_uid = $_SESSION['user']['reseller_uid'];
				$school_uid = $_SESSION['user']['school_uid'];
				
				// get the product id's
				$products = $_POST['products'];
	
				foreach ($products as $product) {
					if(false !== ($product_details = product_locale::getByLocaleUid($product))) {

						$price = $product_details['years_1'];
						$product_uid = $product_details['product_uid'];
						$product_locale_uid = $product_details['uid'];

						/**
						* Add a subscription from 'now'+ 1 year + 2 weeks
						*/
						$objSubscription = new subscriptions();
						$subscribe_uid = 0;
						$subscribe_uid = $objSubscription->CreateSchoolSubscription($user_uid, $price, '1');
						if ($subscribe_uid > 0) {
							// then we have a subscription id
							if(false !== ($uid = subscriptions_products::addEntry($subscribe_uid,$product_locale_uid,$product_uid))) {
								$this->show_upgrade_finish();
							}
						} else {
							echo "problem creating new subscription";
						}
					} else { // eof get the product details
						echo " problem with getProductByUid";
					}
				} // eof loop through each selected product
			} else {
				echo "no products clicked";
			}
		} else {
			output::redirect(config::url());
		}
	}
	
	protected function show_upgrade_finish() {
		if(isset($_SESSION['user']) && $_SESSION['user']['uid'] > 0) {
			
			$title_url	= '';
			$title_alt	= '';
			$intro_text = '';
			$form = "Thanks for your upgrade.";
			$errors = '';
			
			/**
			 * Fetch the page details
			 */
			$page = new page('upgrade');

			$body = make::tpl('body.upgrade');
			$body->assign(
				array(
					'type'						=> 'school',
					'translate.back_to_homepage'=> '',
					'title_url'					=> $title_url,
					'title_alt'					=> $title_alt,
					'intro_text'				=> $intro_text,
					'form'						=> $form,
					'errors'					=> $errors,
					'locale'					=> $this->locale . '/',
				)
			);

			/**
			 * Fetch the standard public xhtml page template
			 */
			$skeleton = make::tpl('skeleton.upgrade');
			$skeleton->assign(
				array(
					'title'				=> $page->title(),
					'keywords'			=> $page->keywords(),
					'description'		=> $page->description(),
					'body'				=> $body,
					'background_url'	=> 'registration_bg.en.jpg',
					'locale'			=> $this->locale
				)
			);

			output::as_html($skeleton, true);			
		} else {
			output::redirect(config::url());
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