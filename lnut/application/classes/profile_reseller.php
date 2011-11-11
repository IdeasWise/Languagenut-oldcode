<?php
class profile_reseller extends generic_object {
	public $arrForm = array();
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__, true);
	}
	public function doSave () {
		if( $this->isValidateFormData() == true ) {
			if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$this->set_itime(time());
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;
				$query ="UPDATE ";
				$query.="`user` ";
				$query.="SET ";
				$query.="`user_type` = CONCAT(`user_type` , ',reseller') ";
				$query.="WHERE ";
				$query.="`uid` = '".mysql_real_escape_string($_POST['iuser_uid'])."' ";
				$query.="LIMIT 1 ";
				database::query( $query );
			}
			return true;
		}
		return false;
	}
	private function isValidateFormData() {
		if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'],__CLASS__);
			$this->load();
		}
		$iuser_uid			= (isset($_POST['iuser_uid']) && is_numeric($_POST['iuser_uid'])) ? $_POST['iuser_uid']:'0';
		$vfirstname			= (isset($_POST['vfirstname']) && strlen(trim($_POST['vfirstname'])) > 0) ? $_POST['vfirstname'] : '';
		$vlastname			= (isset($_POST['vlastname']) && strlen(trim($_POST['vlastname'])) > 0) ? $_POST['vlastname'] : '';
		$vemail				= (isset($_POST['vemail']) && strlen(trim($_POST['vemail'])) > 0) ? $_POST['vemail'] : '';
		$vfax				= (isset($_POST['vfax']) && strlen(trim($_POST['vfax'])) > 0) ? $_POST['vfax'] : '';
		$vphone				= (isset($_POST['vphone']) && strlen(trim($_POST['vphone'])) > 0) ? $_POST['vphone'] : '';
		$locale_rights		= (isset($_POST['locale_rights']) && count($_POST['locale_rights'])) ? trim($_POST['locale_rights']) : ''; 
		$user_limit_reached	= (isset($_POST['user_limit_reached']))? $_POST['user_limit_reached'] : '';
		$vat				= (isset($_POST['vat']) && strlen(trim($_POST['vat'])) > 0) ? $_POST['vat'] : 0;
		$arrMessages = array();
		if( trim(strlen($vfirstname)) < 5 || trim(strlen($vfirstname)) > 250 ) {
			$arrMessages['error_vfirstname'] = "First name must be 5 to 250 characters in length.";
		} else if(!validation::isValid('text',$vfirstname) ) {
			$arrMessages['error_vfirstname'] = "Please enter valid first name.";
		}
		if( trim(strlen($vlastname)) < 3 || trim(strlen($vlastname)) > 250 ) {
			$arrMessages['error_vlastname'] = "Last name must be 3 to 250 characters in length.";
		} else if(!validation::isValid('text',$vlastname) ) {
			$arrMessages['error_vlastname'] = "Please enter valid last name.";
		}
		if( trim(strlen($vemail)) < 5 || trim(strlen($vemail)) > 250 ) {
			$arrMessages['error_email'] = "Email must be 5 to 250 characters in length.";
		} else if(!validation::isValid('email',$vemail) ) {
			$arrMessages['error_email'] = "Please enter valid email.";
		}
		if( trim(strlen($vfax)) < 8 || trim(strlen($vfax)) > 21 ) {
			$arrMessages['error_vfax'] = "Fax number must be 8 to 21 characters in length.";
		} else if(!validation::isValid('fax',$vfax) ) {
			$arrMessages['error_vfax'] = "Please enter valid fax number.";
		}
		if( trim(strlen($vphone)) < 8 || trim(strlen($vphone)) > 21 ) {
			$arrMessages['error_vphone'] = "Phone number must be 8 to 21 characters in length.";
		} else if(!validation::isValid('phone',$vphone) ) {
			$arrMessages['error_vphone'] = "Please enter valid phone number.";
		}
		if(!validation::isValid('int',$vat) ) {
			$arrMessages['error_vat'] = "Please enter valid VAT%.";
		} else if (strlen($vat) > 5) {
			$arrMessages['error_vat'] = "VAT% must be up to 5 digits in length.";
		}
		if(trim($locale_rights) == '') {
			$arrMessages['error_locale_rights'] = "Please select locale rights.";
		} else if(!validation::isValid('text',$locale_rights) ) {
			$arrMessages['error_locale_rights'] = "Please select valid locale rights.";
		}
		$_POST['locale_rights'] = $locale_rights;
		 if(!validation::isValid('text',$user_limit_reached) ) {
			$arrMessages['error_user_limit_reached'] = "Please enter valid user limit reached content.";
		}
		if(count($arrMessages) == 0) {
			$this->set_iuser_uid($iuser_uid);
			$this->set_vfirstname($vfirstname);
			$this->set_vlastname($vlastname);
			$this->set_vemail($vemail);
			$this->set_vfax($vfax);
			$this->set_vphone($vphone);
			$this->set_locale_rights($locale_rights);
			$this->set_vat($vat);
			$this->set_user_limit_reached($user_limit_reached);
		} else {
			$strMessage = '';
			foreach( $arrMessages as $index => $value ){
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>'.$value.'</li>';
			}
			$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>'.$strMessage.'</ul>';
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
	public function GetLocaleRights( $user_uid=null ) {
		$locales = '';
		if(is_numeric($user_uid ) && $user_uid  > 0) {
			$query  = "SELECT ";
			$query .= "`locale_rights` ";
			$query .= "FROM ";
			$query .= "`profile_reseller` ";
			$query .= "WHERE ";
			$query .= "`iuser_uid` = '".$user_uid."' ";
			
			$result = database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				$row = mysql_fetch_array($result);
				if(isset($row['locale_rights']) && $row['locale_rights'] != '') {
					$locales = "'".str_replace(',',"','",$row['locale_rights'])."'";
				}
			}
		}
		return $locales;
	}
	public static function getPrefixes () {
			$response = array();
			$query = "SELECT ";
			$query.= "`uid`, ";
			$query.= "`name`, ";
			$query.= "`prefix` ";
			$query.= "FROM ";
			$query.= "`language` ";
			$query.= "WHERE ";
			$query.= "`prefix` IN (".$_SESSION['user']['localeRights'].") ";
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

	public function getResellerNameByUid($user_uid=null) {
		if($user_uid!=null) {
			$query ="SELECT ";
			$query.="`vfirstname`, ";
			$query.="`vlastname` ";
			$query.="FROM ";
			$query.="`profile_reseller` ";
			$query.="WHERE ";
			$query.="`iuser_uid`='".$user_uid."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)==1 && $result) {
				$row=mysql_fetch_array($result);
				return $row['vfirstname'].' '.$row['vlastname'];
			} else {
				return 'Reseller';
			}
			return 'Reseller';
		}
	}
}
?>