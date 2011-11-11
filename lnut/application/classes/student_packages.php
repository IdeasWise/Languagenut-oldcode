<?php

class student_packages extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function AssignPackagesToStudent($student_user_uid=null) {
		if(isset($_POST['assign_now']) && isset($_POST['package']) && count($_POST['package']) && $student_user_uid!=null) {
			$query ="INSERT INTO ";
			$query.="`student_packages` (";
			$query.="`student_user_uid`,";
			$query.="`main_package_uid`,";
			$query.="`package_uid`,";
			$query.="`reseller_uid`,";
			$query.="`school_uid`,";
			$query.="`name`,";
			$query.="`support_language_uid`,";
			$query.="`learnable_language`,";
			$query.="`sections`,";
			$query.="`games`,";
			$query.="`assigned_by_uid`";
			$query.=") ";
			$query.="SELECT ";
			$query.="'".$student_user_uid."',";
			$query.="`package_uid`,";
			$query.="`uid`,";
			$query.="`reseller_uid`,";
			$query.="`school_uid`,";
			$query.="`name`,";
			$query.="`support_language_uid`,";
			$query.="`learnable_language`,";
			$query.="`sections`,";
			$query.="`games`,";
			$query.="'".$_SESSION['user']['uid']."' ";
			$query.="FROM ";
			$query.="`school_packages` ";
			$query.="WHERE ";
			$query.="`school_uid` ='".$_SESSION['user']['school_uid']."' ";
			$query.="AND ";
			$query.="`is_approved`='1' ";
			$query.="AND ";
			$query.="`reseller_uid` = '".$_SESSION['user']['reseller_uid']."' ";
			$query.="AND ";
			$query.="`uid` IN (".implode(',',$_POST['package']).") ";
			database::query($query);
		}
	}

	public function getStudentActivePackages($student_user_uid=null,$school_uid=null) {
		if($student_user_uid!=null) {
			$query ="SELECT * ";
			$query.=" FROM ";
			$query.="`student_packages` ";
			$query.="WHERE ";
			$query.="`student_user_uid`='".$student_user_uid."' ";
			$query.="AND ";
			$query.="`school_uid` ='".$_SESSION['user']['school_uid']."' ";
			$query.="AND ";
			$query.="`assigned_by_uid`='".$_SESSION['user']['uid']."'";
			$query.="AND ";
			$query.="`removed_by_uid`='0'";
			return database::arrQuery($query);
		}
	}

	public function isValidCreate () {
		
		if($this->isValidateFormData() === true) {
			$package_uid = $this->insert();
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
		} else {
			$this->set_created_date(date('Y-m-d H:i:s'));
		}
		$name			= (isset($_POST['name']) ) ? $_POST['name']:'';
		$arrMessages	= array();

		if( trim(strlen($name)) < 5 || trim(strlen($name)) > 255 ) {
			$arrMessages['error_name'] = "Name must be 5 to 255 characters in length.";
		} else if(!validation::isValid('text',$name) ) {
			$arrMessages['error_name'] = "Please enter valid name.";
		}

		if(!isset($_POST['learnable_language_uid'])) {
			$arrMessages['error_learnable_language'] = "Please select at-least one available language.";
		}



		if(count($arrMessages) == 0) {
			$arrLearnableLanguages =array(
				'language_uids'=>$_POST['learnable_language_uid']
			);
			$this->set_learnable_language(json_encode($arrLearnableLanguages));
		} else {

			$strMessage = '';
			foreach( $arrMessages as $index => $value ){
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>'.$value.'</li>';
			}
			$this->arrForm['message'] = '<p>Please correct the errors below:</p><ul>'.$strMessage.'</ul>';

		}

		foreach( $_POST as $index => $value ) {
			if(!is_array($value)) {
				$this->arrForm[$index] = $value;
			}
		}

		if(count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}
	}

	public function SavePackageSections($package_uid=null,$sections=array()) {
		if($package_uid!=null && is_array($sections) && count($sections)>0) {
			parent::__construct($package_uid);
			if($this->get_valid()) {
				$this->load();
				$arrSections = array();
				$arrSectionUid = array();
				foreach($sections as $index => $value) {
					$language_uid	= 0;
					$year_uid		= 0;
					$unit_uid		= 0;
					$section_uid	= 0;
					list(
						$language_uid,
						$year_uid,
						$unit_uid,
						$section_uid
					) = explode('_',$index);
					$arrSectionUid[$language_uid][$unit_uid][]=$section_uid;
					$arrSections[] = array(
						'learnable_language_uid'	=>$language_uid,
						'year_uid'					=>$year_uid,
						'unit_uid'					=>$unit_uid,
						'section_uid'				=>$section_uid,
						'section_pair'				=>$index
					);
					//$objPackage->set_sections($_POST['package_uid']);
				}

				$this->set_sections(
					json_encode(
						array(
							'sections'=>$arrSections
						)
					)
				);

				/*
				 * UPDATE GAME JSON BASD ON SESSION CHANGES
				*/
				$jsonGame = json_decode($this->get_games());
				$arrGames = array();
				if(isset($jsonGame->games)) {
					foreach($jsonGame->games as $data) {
						if( isset($arrSectionUid[$data->learnable_language_uid][$data->unit_uid]) && is_array($arrSectionUid[$data->learnable_language_uid][$data->unit_uid]) && in_array($data->section_uid,$arrSectionUid[$data->learnable_language_uid][$data->unit_uid])) {
							$arrGames[] = array(
								'learnable_language_uid'	=>$data->learnable_language_uid,
								'unit_uid'					=>$data->unit_uid,
								'section_uid'				=>$data->section_uid,
								'game_uid'					=>$data->game_uid,
								'game_pair'					=>$data->game_pair
							);
						}
					}
					$this->set_games(
						json_encode(
							array(
								'games'=>$arrGames
							)
						)
					);
				}

				$this->save();
			}
		}
	}


	public function SavePackageGames($package_uid=null,$games=array()) {
		if($package_uid!=null && is_array($games) && count($games)>0) {
			parent::__construct($package_uid);
			if($this->get_valid()) {
				$this->load();
				$arrGames = array();
				foreach($games as $index => $value) {
					$language_uid = 0;
					$unit_uid = 0;
					$section_uid = 0;
					$game_uid = 0;
					list(
						$language_uid,
						$unit_uid,
						$section_uid,
						$game_uid
					) = explode('_', $index);
					$arrGames[] = array(
						'learnable_language_uid'	=>$language_uid,
						'unit_uid'					=>$unit_uid,
						'section_uid'				=>$section_uid,
						'game_uid'					=>$game_uid,
						'game_pair'					=>$index
					);
					//$objPackage->set_sections($_POST['package_uid']);
				}
				$this->set_games(
					json_encode(
						array(
							'games'=>$arrGames
						)
					)
				);
				$this->save();
			}
		}
	}

}

?>