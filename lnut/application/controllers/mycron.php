<?php

class MyCron extends Controller {

	public $isEnabled = false;

	public function __construct () {
		parent::__construct();
		$this->run();
	}
/*
	private function run() {
		var_dump(config::get('PRD'));
		die('coming soon!!!');
	}
*/
	private function run() {
		if($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']) {
			if( $this->isEnabled === true ) {
				$arrLocale = array(
						'en',
						'us',
						'au',
						'ca',
						'nz',
						'sco'
					);
				if(date('H:i') == '00:15') {
					$objLnutCron = new lnutCron();
					$objLnutCron->runDailyCron($arrLocale);
				}
				
				if(date('H:i') == '00:30') {
					$objLnutCron = new lnutCron();
					$objLnutCron->runDailyReminderCron($arrLocale);
				}
			} else {
				echo 'cron is not enable!!';
			}
		} else {
			echo 'Invalid link!';
		}
		//$objUserSectionRights = new user_section_rights();
		//$objUserSectionRights->cronCommand();
	}
}

?>