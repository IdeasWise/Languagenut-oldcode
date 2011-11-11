<?php

class file_processing extends generic_object {

	var $upload_path = '';
	var $base_upload_path = '';

	function __construct($uid = 0, $table = '', $takeautoid = false) {
		parent::__construct($uid, $table, $takeautoid);
	}

	static function fileUpload($uploadPath, $ext='') {
		$this->base_upload_path = self::$data["site"];
	}

}

?>
