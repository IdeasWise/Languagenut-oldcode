<?php

class email_templates extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList( $all = false ) {
		if( $all == false ){
			$query = "SELECT ";
			$query .= "count(`uid`) AS `ToT` ";
			$query .= "FROM ";
			$query .= "`email_templates` ";
			$this->setPagination($query);
		}
			$query = "SELECT ";
			$query .= "* ";
			$query .= "FROM ";
			$query .= "`email_templates` ";
			$query .= "ORDER BY ";
			$query .= "`purpose` ";
			if( $all == false ){
				$query .= "LIMIT " . $this->get_limit();
			}
			return database::arrQuery($query);
	}

	public function doSave() {
		if( $this->isValidateFormData() == true ) {
			if(isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$insert = $this->insert();
			}
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

		$purpose	= (isset($_POST['purpose'])) ? $_POST['purpose']:'';
		$arrMessages	= array();

		if( trim(strlen($purpose)) < 5 || trim(strlen($purpose)) > 260 ) {
			$arrMessages['error.purpose'] = "Templaten name must be 5 to 260 characters in length.";
		} else if(!validation::isValid('text',$purpose) ) {
			$arrMessages['error.purpose'] = "Please enter valid template name.";
		}

		if(count($arrMessages) == 0) {
			$this->set_purpose($purpose);
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

	public function getEmailTemplate($slug=null,$locale=null) {
		if( $slug!=null && $locale!=null ) {
			$query = "SELECT ";
			$query.= "`ett`.`subject`, ";
			$query.= "`ett`.`body`, ";
			$query.= "`ett`.`from` ";
			$query.= "FROM ";
			$query.= "`email_templates` AS `et`, ";
			$query.= "`email_templates_translations` AS `ett` ";
			$query.= "WHERE ";
			$query.= "`et`.`tag`='".$slug."' ";
			$query.= "AND `ett`.`locale`='".$locale."' ";
			$query.= "AND `ett`.`email_uid`=`et`.`uid` ";
			$query.= "LIMIT 1";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$arrRow = mysql_fetch_array($result);
				return array(
					'subject'	=> $arrRow['subject'],
					'body'		=> str_replace(
									array('&#123;&#123;','&#125;&#125;'),
									array('{{','}}'),
									$arrRow['body']
									),
					'from'		=> $arrRow['from']
				);
			}
			return false;
		}
		return false;
	}

}
?>