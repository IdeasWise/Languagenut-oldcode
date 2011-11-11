<?php

class temp_csv_import extends generic_object {

	private $csvissue = '';

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function Save() {
		if( isset($_FILES['csv_file']['name']) ) {
			if(isset($_FILES['csv_file']['tmp_name'])) {
				$time 					= time();
				$count 					= 0;
				$_SESSION['csv_data']	= array();
				$_SESSION['session_time']= $time;

				$handle = fopen($_FILES['csv_file']['tmp_name'], "r");
				if( $handle ) {
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						if( $count > 0 ) {
							$this->save_in_tamp_table($data, $time);
							if($count <= 20 ) {
								$_SESSION['csv_data'][$count++] = $data;
							}
						} else {
							$count++;
						}
						
					}
					fclose($handle);
				}
				
			}
		}
	}

	private function save_in_tamp_table( $data = array(), $time = 0 ) {
		/*
		CSV FILE WILL RETURN ARRAY LIKE FOLLOWING SO WE WE USER INDEX OF ARRAY FORM CSV LIKE BELLOW...
		$fields[0] 	= "phrase_id";
		$fields[1] 	= "year_id";
		$fields[2] 	= "year";
		$fields[3] 	= "unit_id";
		$fields[4] 	= "unit_number";
		$fields[5] 	= "unit_name";
		$fields[6] 	= "section_name";
		$fields[7] 	= "section_number";
		$fields[8] 	= "section_uid";
		$fields[9]	= "section_name_translation";
		$fields[10] 	= "section_name_default";
		*/
		if(isset($data[0]) && is_numeric($data[0])) {
			$this->set_phrase_id($data[0]);
		}
		if(isset($data[1]) && is_numeric($data[1])) {
			$this->set_year_id($data[1]);
		}
		if(isset($data[3]) && is_numeric($data[3])) {
			$this->set_unit_id($data[2]);
		}
		if(isset($data[8]) && is_numeric($data[3])) {
			$this->set_section_uid($data[8]);
		}
		if(isset($data[9])) {
			$this->set_section_name_translation($data[9]);
		}
		if(isset($data[5]) && is_numeric($data[5])) {
			$this->set_section_uid($data[5]);
		}
		$this->set_time($time);
		$this->set_session_id(session_id());
		if(isset($_SESSION['user']['uid'])) {
			$this->set_user_uid($_SESSION['user']['uid']);
		}
		if(isset($_POST['language_uid'])) {
			$this->set_language_uid($_POST['language_uid']);
		}
		$this->insert();
	}

	public function isValidTranslationSubmission() {
		if(isset($_POST['submit'])) {
			$arrMessage = array();

			if(isset($_POST['language_uid']) && trim($_POST['language_uid']) == '0' ) {
				$arrMessage['error.language_uid'] = "Please select a language.";
			}
			if(!isset($_FILES['csv_file'])) {
				$arrMessage['error.csv_file'] = "Please upload translation csv file.";
			}
			if(isset($_FILES['csv_file']) && !$this->isValidCsvfile()) {
				$arrMessage['error.csv_file'] = "Please upload valid translation csv file.".$this->csvissue;
			}
		}
		$response	= array();
		$msg		= NULL;
		
		foreach( $arrMessage as $idx => $val ){
			$response[$idx] = 'label_error';
			$msg .= '<li>'.$val.'</li>';
		}
		if($msg != NULL) {
			$response['message'] = '<p>Please correct the errors below:</p><ul>'.$msg.'</ul>';
			return $response;
		}
		return true;
	}

	private function isValidCsvfile() {
		if( isset($_FILES['csv_file']['name']) ) {
			$csv_file = explode('.',$_FILES['csv_file']['name']);
			if(strtolower(trim($csv_file[count($csv_file)-1])) != 'csv' ){
				$this->csvissue = 'notcsv';
				return false;
			}
			if(isset($_FILES['csv_file']['tmp_name'])) {
				$fields		= array();
				$fields[0]	= "phrase_id";
				$fields[1]	= "year_id";
				$fields[2]	= "year";
				$fields[3]	= "unit_id";
				$fields[4]	= "unit_number";
				$fields[5]	= "unit_name";
				$fields[6]	= "section_name";
				$fields[7]	= "section_number";
				$fields[8]	= "section_uid";
				$fields[9]	= "section_name_translation";
				$fields[10] = "section_name_default";
				$count = 0;
				$handle = fopen($_FILES['csv_file']['tmp_name'], "r");
				if( $handle ) {
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						if( $count > 0 ) {
							/*echo '<pre>';
							print_r($data);
							echo '</pre>';*/
							//$Save->Save($data);
						} else {
							$count++;
							$array_diff = array();
							$array_diff = array_diff( $fields, $data );
							if( count( $array_diff ) > 0 ) {
								$this->csvissue = 'mismatch';
								return false;
							}
							break;
						}
						
					}
					fclose($handle);
				}
				
			}
		}
		return true;
	}

	public function ImportTranslations() {
		if(isset($_SESSION['session_time']) && isset($_SESSION['user']['uid'])) {
			$query = "INSERT ";
			$query .= "INTO ";
			$query .= "`sections_vocabulary_translations` ";
			$query .= "( ";
					$query .= "`term_uid`, ";
					$query .= "`language_id`, ";
					$query .= "`name`, ";
					$query .= "`active` ";
			$query .= " ) ";
			$query .= "SELECT ";
			$query .= "`phrase_id`, ";
			$query .= "`language_uid`, ";
			$query .= "`section_name_translation`, ";
			$query .= "'1' ";
			$query .= "FROM ";
			$query .= "`temp_csv_import` ";
			$query .= "WHERE ";
			$query .= "`time` = '".$_SESSION['session_time']."' ";
			$query .= "AND `session_id` = '".session_id()."' ";
			$query .= "AND `user_uid` = '".$_SESSION['user']['uid']."' ";
			database::query($query);

			$query = "DELETE ";
			$query .= "FROM ";
			$query .= "`temp_csv_import` ";
			$query .= "WHERE ";
			$query .= "`time` = '".$_SESSION['session_time']."' ";
			$query .= "AND `session_id` = '".session_id()."' ";
			$query .= "AND `user_uid` = '".$_SESSION['user']['uid']."' ";
			database::query($query);
			unset($_SESSION['session_time']);
			unset($_SESSION['csv_data']);
		}
	}

	public function CancelImportTranslation() {
		if(isset($_SESSION['session_time']) && isset($_SESSION['user']['uid'])) {
			$query = "DELETE ";
			$query .= "FROM ";
			$query .= "`temp_csv_import` ";
			$query .= "WHERE ";
			$query .= "`time` = '".$_SESSION['session_time']."' ";
			$query .= "AND `session_id` = '".session_id()."' ";
			$query .= "AND `user_uid` = '".$_SESSION['user']['uid']."' ";
			database::query($query);
			unset($_SESSION['session_time']);
			unset($_SESSION['csv_data']);
		}
	}
}

?>