<?php

class language extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function exists($language_uid=null) {
		$sql = "SELECT * FROM `language` WHERE `uid`='".$language_uid."' LIMIT 1";
		$res = database::query($sql);

		if($res && mysql_error()=='' && mysql_num_rows($res) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function LanguageSelectBox($inputName, $selctedValue = NULL) {
		$sql = "SELECT `uid`, `name` FROM `language` ORDER BY `name` ASC";
		$res = database::query($sql);

		$data = array();
		$data[0] = 'Language';

		if($res && mysql_error()=='' && mysql_num_rows($res) > 0) {
			while($row=mysql_fetch_assoc($res)) {
				$data[$row['uid']] = $row['name'];
			}
		}
		return format::to_select(array("name" => $inputName,"id" => $inputName,"style" => "width:180px;","options_only" => false), $data , $selctedValue);
	}

	public function LocaleSelectBox($inputName, $selctedValue = NULL) {
		$sql = "SELECT prefix  FROM language ORDER BY prefix";
		$res = database::query($sql);

		$data = array();
		$data[''] = 'Locale';

		if($res && mysql_error()=='' && mysql_num_rows($res) > 0) {
			while($row=mysql_fetch_assoc($res)) {
				$data[$row['prefix']] = $row['prefix'];
			}
		}
		return format::to_select(array("name" => $inputName,"id" => $inputName,"options_only" => false), $data , $selctedValue);
	}

	public function getList( $data = array(), $OrderBy = "name ", $all = false ) {
		$parts = config::get('paths');
		$where = ' where 1 = 1';

		foreach($data as $idx => $val ){
			$where .= " AND " .  $idx . "='" . $val . "'";
		}
		if($all == false) {
			$result = database::query('SELECT COUNT(uid) FROM language '.$where);
			$max = 0;

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				$row = mysql_fetch_array($result);
				$max = $row[0];
			}
			$pageId = '';
			if($pageId=='') {
				$n = count($parts) - 1;
			
				if(isset($parts[$n]) && is_numeric($parts[$n]) && $parts[$n] > 0) {
					$pageId = $parts[$n];
				} else {
					$pageId = 1;
				}
			}

			$this->pager(
					$max,						//see above
					config::get("pagesize"),	//how many records to display at one time
					$pageId,
					array("php_self" => "")
			);
			
			$this->set_range(10);
			$result = database::query("SELECT * FROM language ".$where." ORDER BY " . $OrderBy . "  LIMIT ".$this->get_limit());

		} else {
			$result = database::query("SELECT * FROM language ".$where." ORDER BY " . $OrderBy );
		}
		$this->data		= array();
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$this->data[] = $row;
			}
		}
		return $this->data;
	}

	public function doSave () {
		$response = true;
		$response = $this->isValidate();

		if( count( $response ) == 0 ) {
			if( $_POST['uid'] > 0) {
				$this->save ();
			} else {
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;                
			}
		} else {
			$msg  = NULL;
			foreach( $response as $idx => $val ) {
				$this->arrForm[$idx] = 'label_error';
				$msg .= '<li>'.$val.'</li>';
			}
			if($msg != NULL) {
				$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>'.$msg.'</ul>';
			}
		}
		if( count( $response ) > 0 ) {
			return false;
		} else {
			return true;
		}
	}

	public function isValidate() {
		if(is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'],__CLASS__);
			$this->load();
		}
		$message = array();
		if( trim($_POST['name']) == '' ) {
			$message['error_name']      =   "Please provide language name.";
		}
		if( trim($_POST['prefix']) == '' ) {
			$message['error_prefix']      =   "Please provide language prefix.";
		}
		
		foreach( $_POST as $idx => $val )   {
			$this->arrForm[$idx] = $val;
			if( in_array($idx,array('uid', 'form_submit_button')) ) continue;
			$this->arrFields[$idx]['Value'] = $val;
		}
		return $message;
	}

	public function getLanguageArray() {
		$languages = array ();
		$query = "SELECT * FROM `language` ORDER BY `name` ASC";
		$result = database::query($query);

		if($result) {
			if(mysql_num_rows($result) > 0) {
				while($row = mysql_fetch_assoc($result)) {
					$languages[$row['uid']] = $row['name'];
				}
			}
		}
		return $languages;
	}

	public function getLanguagesList() {
		$languages	= array ();
		$query		= "SELECT * FROM `language` ORDER BY `name` ASC";
		$result		= database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			while($row = mysql_fetch_assoc($result)) {
				$languages[$row['uid']] = array (
					'name'		=> stripslashes($row['name']),
					'directory'	=> stripslashes($row['directory']),
					'available'	=> $row['available']
				);
			}
		}

		return $languages;
	}

	public function getLanguages() {
		$languages	= array ();
		$query		= "SELECT * FROM `language` ORDER BY `name` ASC";
		$result		= database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			while($row = mysql_fetch_assoc($result)) {
				$languages[$row['uid']] = $row;
			}
		}

		return $languages;
	}

	public function getPrefix( $language_id ) {
		$locale = 'en';

		$query = "SELECT ";
		$query.= "`prefix` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "WHERE ";
		$query.= "`uid`=$language_id ";
		$query.= "LIMIT 1";

		$result = database::query($query);        

		if($result) {
			if(mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$locale = $row['prefix'];
			}
		}
		return $locale;
	}

	public function CheckLocale( $locale ){
		$query = "SELECT ";
		$query.= "`uid` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "WHERE ";
		$query.= "`prefix`='".mysql_real_escape_string( $locale )."' AND ";
		$query.= "`available`='1'";
		$query.= "LIMIT 1";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_array($result);
			return $row['uid'];
		}

		return false;
	}

	public function doSavePricing(){
		$response = true;
		$response = $this->isValidatePricing();

		if( count( $response ) == 0 ){
			$this->save();
		} else {
			$msg  = NULL;
			foreach( $response as $idx => $val ){
				$this->arrForm[$idx] = 'label_error';
				$msg .= '<li>'.$val.'</li>';
			}
			if($msg != NULL) {
				$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>'.$msg.'</ul>';
			}
		}
		if( count( $response ) > 0 ) {
			return false;
		} else {
			return true;
		}
	}

	public function isValidatePricing() {

		if(is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'],__CLASS__);
			$this->load();
		}
		$message            =   array();
		if( trim($_POST['currency_uid']) == '0' ) {
			$message['error_currency_uid']      =   "Please choose currency.";
		}
		if( !is_numeric($_POST['home_user_price']) || $_POST['home_user_price'] == 0 ) {
			$message['error_home_user_price']      =   "Please enter price for home user.";
		}

		if( !is_numeric($_POST['school_price']) || $_POST['school_price'] == 0 ) {
			$message['error_school_price']      =   "Please enter price for school.";
		}

		$IgnoreArray = array('uid', 'locale','table_name','form_submit_button');
		foreach( $_POST as $idx => $val )   {
			$this->arrForm[$idx] = $val;
			if( in_array($idx, $IgnoreArray ) ) continue;
			$this->arrFields[$idx]['Value'] = $val;
		}
		return $message;
	}

	public static function getPrefixes () {

		$response = array();

		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`name`, ";
		$query.= "`prefix` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$response[$row['uid']] = array (
					'name'	=> stripslashes($row['name']),
					'prefix'=> stripslashes($row['prefix'])
				);
			}
		}

		return $response;
	}
}
?>