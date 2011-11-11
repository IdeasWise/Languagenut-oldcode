<?php

class MyCron extends Controller {

	public $isEnabled = false;

	public function __construct () {
		parent::__construct();
		$this->run();
	}

	private function run() {
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
		}
		//$objUserSectionRights = new user_section_rights();
		//$objUserSectionRights->cronCommand();
	}
}

?>