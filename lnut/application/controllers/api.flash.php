<?php

/**
 * api.writing.php
 */

class API_Writing extends Controller {

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		$method = 'getInvalidLink';
		if(isset($arrPaths[2]) && isset($arrPaths[3]) && !empty($arrPaths[2]) && !empty($arrPaths[3])) {
			$newMethod = $arrPaths[2].ucfirst($arrPaths[3]);
			if(method_exists($this,$newMethod)) {
				$method = $newMethod;
			}
		}
		$this->$method();
	}

	private function getInvalidLink() {
		die('Invalid Link!!!');
	}
	
	private function getTips() {
		if(isset($_REQUEST['language_uid']) && is_numeric($_REQUEST['language_uid'])) {
			$objFlashTips = new flash_tips_translation();
			echo str_replace('\r\n','\n',json_encode(
				$objFlashTips->getAPIFlashTipsTranslations($_REQUEST['language_uid'])
			));
		} else {
			echo '{"success":"false"}';
		}
	}
}

?>