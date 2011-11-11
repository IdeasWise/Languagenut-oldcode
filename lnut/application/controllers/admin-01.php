<?php

class Admin extends Controller {

	public function __construct() {
		parent::__construct();
		$this->page();
	}

	protected function page() {
		/**
		 * Process Request if user is logged in as an administrator
		 */
		if (user::isLoggedIn()) {
			$objUser = new user($_SESSION['user']['uid']);
			$objUser->load();

			if ($objUser->isAdmin()) {
				$this->pageAdmin();
			} else {
				$objUser->logout();
				output::redirect(config::url('login/'));
			}
		} else {
			output::redirect(config::url('login/'));
		}
	}

	protected function pageAdmin() {
		$arrPaths = config::get('paths');
		if (count($arrPaths) > 1) {
			if (isset($arrPaths[1])) {
				$token = $arrPaths[1];
				switch ($token) {
					case 'page-update':
						$this->page_update();
						break;
					case 'pages':
						$this->load_controller('pages');
						break;
					case 'appearance':
						$this->load_controller('appearance');
						break;
					case 'settings':
						$this->load_controller('settings');
						break;
					case "plugins":
						$this->load_controller('plugins');
						break;
					case "users":
						$this->load_controller('users');
						break;
					case "invoice":
						$this->load_controller('invoice');
						break;
					case "library":
						$this->load_controller('library');
						break;
					case "language":
						$this->load_controller('language');
						break;
					case "currency":
						$this->load_controller('admin.currency');
						break;
					case "year":
						$this->load_controller('admin.year');
						break;
					case "unit":
						$this->load_controller('admin.unit');
						break;
					case "section":
						$this->load_controller('admin.sections');
						break;
					case "vocabulary":
						$this->load_controller('admin.vocabulary');
						break;
					case "classes":
						$this->load_controller('admin.classes');
						break;
					case "translations":
						$this->load_controller('admin.translations');
						break;
					case "flash_translations":
						$this->load_controller('admin.flash_translations');
						break;
					case "flash_translations_tags":
						$this->load_controller('admin.flash_translations_tags');
						break;
					case "message_translations":
						$this->load_controller('admin.message_translations');
						break;
					case 'games':
						$this->load_controller('admin.games');
						break;
					case 'qaetopics':
						$this->load_controller('admin.qaetopics');
						break;
					case 'skills':
						$this->load_controller('admin.skills');
						break;
					case 'referencematerialtype':
						$this->load_controller('admin.referencematerialtype');
						break;
					case 'exercisetype':
						$this->load_controller('admin.exercisetype');
						break;
					case 'difficultylevel':
						$this->load_controller('admin.difficultylevel');
						break;
					case 'articletemplate':
						$this->load_controller('admin.articletemplate');
						break;
					case 'articleitemtype':
						$this->load_controller('admin.articleitemtype');
						break;
					case 'notification':
						$this->load_controller('admin.notification');
						break;
					case 'notificationevent':
						$this->load_controller('admin.notificationevent');
						break;
					case 'game_translations':
						$this->load_controller('admin.game_translations');
						break;
					case 'reseller_sub_package':
						$this->load_controller('admin.reseller_sub_package');
						break;
					case 'wordbank':
						$this->load_controller('admin.wordbank');
						break;
					case 'certificate':
						$this->load_controller('certificate_settings');
						break;
					case 'view_logs':
						$this->load_controller('view_logs');
						break;
					case 'login-history':
						$this->load_controller('login_history');
						break;
					case 'multilingual':
						$this->load_controller('admin_multilingual');
						break;
					case 'registration-email':
						$this->load_controller('admin_registration_email');
						break;
					case 'school-registration':
						$this->load_controller('admin_school_registration');
						break;
					case 'promocode':
						$this->load_controller('admin.promocode');
						break;
					case 'packages':
						$this->load_controller('admin.package');
						break;
					case 'article':
						$this->load_controller('admin.article');
						break;
					case 'article-category':
						$this->load_controller('admin.article_category');
						break;
					case 'article-template':
						$this->load_controller('admin.article.template');
						break;
					default:
						output::redirect(config::url('admin/users/school/'));
						break;
				}
			} else {
				output::redirect(config::url('admin/users/school/'));
			}
		} else {
			output::redirect(config::url('admin/users/school/'));
		}
	}

}

?>