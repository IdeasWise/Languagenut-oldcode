<?php

class gamedata_translations extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function doSave() {
		if($this->isValidateFormData()===true) {
			$this->set_key($_POST['new_key']);
			$this->set_value($_POST['new_value']);
			$this->set_game_uid($_POST['game_uid']);
			$this->set_language_uid($_POST['gamedata_language_uid']);
			$this->insert();
			return true;
		} else {
			$this->arrForm['new_message_error'] = $this->arrForm['message_error'];
			return false;
		}
	}

	private function isValidateFormData() {
		$arrFields = array(
			'key' => array(
				'value' => (isset($_POST['new_key'])) ? trim($_POST['new_key']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter key.',
				'minChar' => 3,
				'maxChar' => 32,
				'errMinMax' => 'Key must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid key.',
				'errIndex' => 'new_key_error'
			),
			'value' => array(
				'value' => (isset($_POST['new_value'])) ? trim($_POST['new_value']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter text value.',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid text value.',
				'errIndex' => 'new_value_error'
			),			
			'game_uid' => array(
				'value' => (isset($_POST['game_uid'])) ? trim($_POST['game_uid']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please provide game_uid.',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'int',
				'errdataType' => 'Please provide valid game_uid.',
				'errIndex' => 'error.game_uid'
			),
			'gamedata_language_uid' => array(
				'value' => (isset($_POST['gamedata_language_uid'])) ? trim($_POST['gamedata_language_uid']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please provide gamedata_language_uid.',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'int',
				'errdataType' => 'Please provide valid gamedata_language_uid.',
				'errIndex' => 'error.gamedata_language_uid'
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			return true;
		} else {
			return false;
		}
	}

	public function getGameData($game_uid=null,$language_uid=null) {
		$arrResponse = array();
		if($game_uid!=null && $language_uid!=null) {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`gamedata_translations` ";
			$query.="WHERE ";
			$query.="`game_uid` = '".$game_uid."' ";
			$query.="AND ";
			$query.="`language_uid`='".$language_uid."' ";

			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrResponse[] = array(
						'uid'		=>$arrRow['uid'],
						'game_uid'	=>$arrRow['game_uid'],
						'key'		=>$arrRow['key'],
						'value'		=>$arrRow['value']
					);
				}
			}
		}
		return $arrResponse;
	}

	public function doSaveTranslations() {
		if($this->validatTranslationFrom()===true) {
			foreach($_POST['key'] as $uid => $value) {
				$query ="UPDATE ";
				$query.="`gamedata_translations` ";
				$query.="SET ";
				$query.="`key`='".addslashes(mysql_real_escape_string($value))."', ";
				$query.="`value`='".addslashes(mysql_real_escape_string($_POST['value'][$uid]))."' ";
				$query.="WHERE ";
				$query.="`uid`='".$uid."' ";

				database::query($query);
			}
			return true;
		} else {
			return false;
		}
	}

	private function validatTranslationFrom() {
		if(isset($_POST['key']) && is_array($_POST['key']) && isset($_POST['value']) && is_array($_POST['value']) ) {
			$arrMessages = array();
			foreach($_POST['key'] as $uid => $value) {
				$key = trim($value);
				if(strlen($key)==0 || strlen($key)>32) {
					$arrMessages[] = 'Please check key vlaues.';
					$arrMessages[] = 'Key values must be 5 to 255 characters in length.';
					break;
				}
			}
			foreach($_POST['value'] as $uid => $value) {
				if(empty($value)) {
					$arrMessages[] = 'Please enter text in value textbox.';
					break;
				}
			}
			if(count($arrMessages) == 0) {
				return true;
			} else {
				if (count($arrMessages) > 0) {
					$strMessage = '';
					foreach ($arrMessages as $index => $value) {
						$this->arrForm[$index] = 'label_error';
						$strMessage .= '<li>' . $value . '</li>';
					}
					$this->arrForm['current_message_error'] = '<p>Please correct the errors below:</p><ul>' . $strMessage . '</ul>';
				}
			}
		}
		return false;
	}

	public function getGameDataTranslationForAPI($game_uid=null,$language_uid=null) {
		$arrGamedata = array();
		if($game_uid!=null && $language_uid!=null) {
			$query ="SELECT ";
			$query.="`key`, `value` ";
			$query.="FROM ";
			$query.="`gamedata_translations` ";
			$query.="WHERE ";
			$query.="`game_uid` = '".$game_uid."' ";
			$query.="AND ";
			$query.="`language_uid`='".mysql_real_escape_string($language_uid)."' ";

			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrGamedata[] = array(
						'key'		=>$arrRow['key'],
						'value'		=>$arrRow['value']
					);
				}
			}

			if(count($arrGamedata)==0) {
				$query ="SELECT ";
				$query.="`key`, `value` ";
				$query.="FROM ";
				$query.="`gamedata` ";
				$query.="WHERE ";
				$query.="`game_uid` = '".$game_uid."' ";
				$result = database::query($query);
				if(mysql_error()=='' && mysql_num_rows($result)) {
					while($arrRow = mysql_fetch_array($result)) {
						$arrGamedata[] = array(
							'key'		=>$arrRow['key'],
							'value'		=>$arrRow['value']
						);
					}
				}
			}
		}
		return $arrGamedata;
	}
}

?>