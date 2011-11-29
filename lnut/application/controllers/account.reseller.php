<?php

class account_reseller extends Controller {

	private $objUser = null;
	private $arrProfiles = array(
		'school',
		'schooladmin',
		'schoolteacher',
		'student',
		'homeuser'
	);

	public function __construct() {
		parent::__construct();
		$this->index();
	}

	protected function index() {

		if(user::isLoggedIn()) {
			$this->objUser = new user($_SESSION['user']['uid']);
			$this->objUser->load();

			if($this->objUser->isLoggedIn()) {
				$this->pageAccount();
			} else {
				$this->objUser->logout();
				output::redirectTo('login/');
			}
		} else {
			output::redirectTo('login/');
		}

	}

	protected function pageAccount() {
		$arrPaths	= config::get('paths');
		if(count($arrPaths) > 1) {
			if(isset($arrPaths[1])) {
				$token = $arrPaths[1];
				switch ($token) {
					case "users":
						$this->load_controller('account.reseller.users');
					break;
					case "classes":
						$this->load_controller('account.classes');
					break;
					case "invoice":
						$this->load_controller('account.invoice');
					break;
					case 'login-history':
						$this->load_controller('login_history');
					break;
					case "currency":
						$this->load_controller('account.currency');
					break;
					case 'school-registration':
						$this->load_controller('account_school_registration');
					break;
					case "packages":
						$this->load_controller('account.reseller.package');
					break;
					case "package-management":
						$this->load_controller('account.reseller.manage.school.package');
					break;
					case "schoolClass":
						$this->load_controller('admin.school.classes');
					break;
					default:
						output::redirectTo('account/users/school/');
					break;
				}

			} else {
				output::redirectTo('account/users/school/');
			}
		} else {
			output::redirectTo('account/users/school/');
		}
	}

}

?>