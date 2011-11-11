<?php

class promocode extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList( $all = false ) {
		
		if( !$all ) {
			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`promocode` ";
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`promocode` ";
		$query.="ORDER BY ";
		$query.="`name` ";
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
		$name				= (isset($_POST['name']) ) ? $_POST['name']:'';
		$code				= (isset($_POST['code']) ) ? strtoupper($_POST['code']):'';
		$active_from		= (isset($_POST['active_from']) ) ? $_POST['active_from']:'';
		$avail_until		= (isset($_POST['avail_until']) ) ? $_POST['avail_until']:'';
		$sub_start_date		= (isset($_POST['sub_start_date']) ) ? $_POST['sub_start_date']:'';
		$sub_end_date		= (isset($_POST['sub_end_date']) ) ? $_POST['sub_end_date']:'';
		$locale				= (isset($_POST['locale']) ) ? $_POST['locale']:'';
		$override_date		= (isset($_POST['override_date'])) ? $_POST['override_date']:'0';
		$arrMessages		= array();
		if( trim(strlen($name)) < 5 || trim(strlen($name)) > 255 ) {
			$arrMessages['error_name'] = "Name must be 5 to 255 characters in length.";
		} else if(!validation::isValid('text',$name) ) {
			$arrMessages['error_name'] = "Please enter valid name.";
		}
		if( trim(strlen($code)) < 5 || trim(strlen($code)) > 255 ) {
			$arrMessages['error_code'] = "Code must be 5 to 255 characters in length.";
		} else if(!validation::isValid('text',$code) ) {
			$arrMessages['error_code'] = "Please enter valid code.";
		}
		if( trim($active_from) == '') {
			$arrMessages['error_active_from'] = "Please enter promocode start date.";
		}
		if( trim($avail_until) == '') {
			$arrMessages['error_avail_until'] = "Please enter promocode end date.";
		}
		if(isset($_POST['active_from']) && isset($_POST['avail_until']) && !empty($_POST['active_from']) && !empty($_POST['avail_until'])) { 
			if( strtotime($_POST['active_year'].'-'.$_POST['active_month'].'-'.$_POST['active_day']) > strtotime($_POST['avail_year'].'-'.$_POST['avail_month'].'-'.$_POST['avail_day']) ) {
				$arrMessages['error_avail_until'] = "Promocode end date should be bigger then promocode start date.";
			}
		}
		if( trim($sub_start_date) == '') {
			$arrMessages['error_sub_start_date'] = "Please enter subscription start date.";
		}
		if( trim($sub_end_date) == '') {
			$arrMessages['error_sub_end_date'] = "Please enter subscription end date.";
		}
		if(isset($_POST['sub_start_date']) && isset($_POST['sub_end_date']) && !empty($_POST['sub_start_date']) && !empty($_POST['sub_end_date'])) {
			if( strtotime($_POST['sub_start_year'].'-'.$_POST['sub_start_month'].'-'.$_POST['sub_start_day']) > strtotime($_POST['sub_end_year'].'-'.$_POST['sub_end_month'].'-'.$_POST['sub_end_day']) ) {
				$arrMessages['error_sub_end_date']		= "Subscription end date should be bigger then subscription start date.";
			}
		}
		if( trim($locale) == '') {
			$arrMessages['error_locale'] = "Please select locale.";
		} else if(!validation::isValid('text',$locale) ) {
			$arrMessages['error_locale'] = "Please select valid locale.";
		}
		if(!validation::isValid('int',$override_date) ) {
			$arrMessages['error_override_date'] = "Please select valid override date.";
		}
		if(count($arrMessages) == 0) {
			$this->set_name($name);
			$this->set_code($code);
			$this->set_active_from($_POST['active_year'].'-'.$_POST['active_month'].'-'.$_POST['active_day']);
			$this->set_active_until($_POST['avail_year'].'-'.$_POST['avail_month'].'-'.$_POST['avail_day']);
			$this->set_sub_start_date($_POST['sub_start_year'].'-'.$_POST['sub_start_month'].'-'.$_POST['sub_start_day']);
			$this->set_sub_end_date($_POST['sub_end_year'].'-'.$_POST['sub_end_month'].'-'.$_POST['sub_end_day']);
			$this->set_override_date($override_date);
			$this->set_locale($locale);
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
	public function getPromoCodeDetails( $promocode = '' ) {
		$arrPromocode = array();
		
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`promocode` ";
		$query.="WHERE ";
		$query.="'".date('Y-m-d')."' ";
		$query.="BETWEEN ";
		$query.="`active_from` ";
		$query.="AND ";
		$query.="`active_until` ";
		$query.="AND ";
		$query.="`code` = '".mysql_real_escape_string(strtoupper($promocode))."' ";
		$query.="AND ";
		$query.="`locale` = '".config::get('locale')."'";
		$result = database::query($query);
		if(mysql_error() == '' && $result && mysql_num_rows($result)) {
			$arrPromocode = mysql_fetch_array($result);
		}
		return $arrPromocode;
	}
}
?>