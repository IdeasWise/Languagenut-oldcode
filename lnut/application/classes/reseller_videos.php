<?php

class reseller_videos extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList( $all = false ) {
		
		if( !$all ) {
			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`reseller_videos` ";
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`reseller_videos` ";
		$query.="ORDER BY ";
		$query.="`title` ";
		if($all	== false) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);	
	}

	public function isValidCreate () {
		
		if($this->isValidateFormData() === true) {
			$this->insert();
			return true;
		} else {
			return false;
		}
		
	}

	public function isValidUpdate () {
		
		if($this->isValidateFormData() === true) {
			$this->save();
			return true;
		} else {
			return false;
		}
		
	}



	private function isValidateFormData() {

		if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'],__CLASS__);
			$this->load();
		}
		$title			= (isset($_POST['title']) ) ? $_POST['title']:'';
		$locale			= (isset($_POST['locale']) ) ? $_POST['locale']:'';
		$description	= (isset($_POST['description']) ) ? $_POST['description']:'';
		$arrMessages	= array();
		if( trim(strlen($title)) < 5 || trim(strlen($title)) > 255 ) {
			$arrMessages['error_title'] = "Title must be 5 to 255 characters in length.";
		} else if(!validation::isValid('text',$title) ) {
			$arrMessages['error_title'] = "Please enter valid name.";
		}
		if( trim($locale) == '') {
			$arrMessages['error_locale'] = "Please select locale.";
		} else if(!validation::isValid('text',$locale) ) {
			$arrMessages['error_locale'] = "Please select valid locale.";
		}

		if(isset($_FILES['file']['name']) && empty($_FILES['file']['name']) && $_POST['uid'] == '') {
			$arrMessages['error_file'] ="Please upload video file.";
		} else if(isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {
			$fileExtension = explode('.',$_FILES['file']['name']);
			if(!in_array(strtolower($fileExtension[count($fileExtension)-1]),array('flv','mp4'))) {
				$arrMessages['error_file'] ="Please upload valid video file, we're only support flv and mp4 files!";
			}
		}

		if(isset($_FILES['file']['name']) && !empty($_FILES['file']['name']) && count($arrMessages) == 0) {

			$dirUpload = config::get('root').'/uploads/';
			if(isset($_POST['hidden_file']) && !empty($_POST['hidden_file'])) {
				if(is_file($dirUpload.$_POST['hidden_file'])) {
					unlink($dirUpload.$_POST['hidden_file']);
				}
			}

			move_uploaded_file(
				$_FILES["file"]["tmp_name"],
				$dirUpload.$_FILES['file']['name']
			);
			$this->set_file($_FILES['file']['name']);
		}
		if(count($arrMessages) == 0) {
			$this->set_title($title);
			$this->set_locale($locale);
			$this->set_description($description);
		} else {

			$strMessage = '';
			foreach( $arrMessages as $index => $value ){
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>'.$value.'</li>';
			}
			$this->arrForm['message'] = '<p>Please correct the errors below:</p><ul>'.$strMessage.'</ul>';

		}

		foreach( $_POST as $index => $value ) {
			$this->arrForm[$index] = $value;
		}

		if(count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}
	}

}
?>