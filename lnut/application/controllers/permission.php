<?php

/**
 * permission.php
 */

class Permission extends Controller {

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		if(isset($arrPaths[1]) && $arrPaths[1]=='detail') {
			$this->doJsonDetail();
		} else {
			$this->doJson();
		}
	}

	protected function doJson() {
		$arrSupportLanguages = user::getUserPackage();
		echo json_encode(
			array(
				'support_languages' => $arrSupportLanguages
			)
		);
	}
	protected function doJsonDetail() {
		$json_dir = config::get('cache').'json/reseller/';
		$json_file = '3434_1.json';
		echo file_get_contents($json_dir.$json_file);
	}

}

?>