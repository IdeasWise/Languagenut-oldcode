<?php

class gamedata extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($game_uid=null) {
		$query ="SELECT ";
		$query.="COUNT(`uid`) ";
		$query.="FROM ";
		$query.="`gamedata` ";
		$query.="WHERE ";
		$query.="`game_uid` = '".$game_uid."' ";
		$this->setPagination($query);
		
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`gamedata` ";
		$query.="WHERE ";
		$query.="`game_uid` = '".$game_uid."' ";
		$query.= "LIMIT ".$this->get_limit();
		return database::arrQuery($query);
	}

	public function doSave() {
		if($this->isValidateFormData()===true) {

			if( isset($_POST['uid']) && is_numeric($_POST['uid']) ) {
				parent::__construct($_POST['uid'], __CLASS__);
				$this->load();
				$this->set_key($_POST['default_key']);
				$this->set_value($_POST['default_value']);
				$this->set_game_uid($_POST['game_uid']);
				$this->save();
			} else {
				$this->set_key($_POST['default_key']);
				$this->set_value($_POST['default_value']);
				$this->set_game_uid($_POST['game_uid']);
				$game_data_uid = $this->insert();
				$this->insertDefaultPairToTraslation($game_data_uid);
			}
			return true;
		} else {
			$this->arrForm['default_message_error'] = $this->arrForm['message_error'];
			return false;
		}
	}

	public function insertDefaultPairToTraslation($game_data_uid) {
		$query = "SELECT ";
		$query.= "`uid` ";
		$query.= "FROM ";
		$query.= "`language` ";
		if (isset($_SESSION['user']['localeRights'])) {
			$sql.= "WHERE `prefix` IN( " . $_SESSION['user']['localeRights'] . ") ";
		}
		$query.= "ORDER BY ";
		$query.= "`name` ASC";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			$objGamedataTranslations = new gamedata_translations();
			while($arrRow = mysql_fetch_array($result)) {

				$objGamedataTranslations->set_key($_POST['default_key']);
				$objGamedataTranslations->set_value($_POST['default_value']);
				$objGamedataTranslations->set_game_uid($_POST['game_uid']);
				$objGamedataTranslations->set_gamedata_uid($game_data_uid);
				$objGamedataTranslations->set_language_uid($arrRow['uid']);
				$objGamedataTranslations->insert();

			}
		}
	}

	private function isValidateFormData() {
		$arrFields = array(
			'key' => array(
				'value' => (isset($_POST['default_key'])) ? trim($_POST['default_key']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter key.',
				'minChar' => 3,
				'maxChar' => 32,
				'errMinMax' => 'Key must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid key.',
				'errIndex' => 'default_key_error'
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
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			return true;
		} else {
			return false;
		}
	}

	public function getGameData($game_uid=null) {
		$arrResponse = array(
			'default_key'	=>'',
			'default_value'	=>''
		);
		if($game_uid!=null) {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`gamedata` ";
			$query.="WHERE ";
			$query.="`game_uid` = '".$game_uid."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$arrRow = mysql_fetch_array($result);
				$arrResponse = array(
					'default_key'	=>$arrRow['key'],
					'default_value'	=>$arrRow['value']
				);
			}
		}
		return $arrResponse;
	}
}

?>