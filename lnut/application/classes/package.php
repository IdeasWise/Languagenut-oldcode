<?php

class package extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList( $all = false ) {
		
		if( !$all ) {
			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`package` ";
			$this->setPagination( $query );
		}
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`package` ";
		$query.="ORDER BY ";
		$query.="`name` ";
		if($all	== false) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function isValidCreate () {
		
		if($this->isValidateFormData() === true) {
			$package_uid = $this->insert();
			//$objPackageLanguage = new package_language();
			//$objPackageLanguage->AddLearnableLanguages($package_uid);
			//$objPackagePrice = new package_price();
			//$objPackagePrice->SavePackagePriceandVat($package_uid);
			return true;
		} else {
			return false;
		}
		
	}

	public function isValidUpdate () {
		
		if($this->isValidateFormData() === true) {
			$this->save();
			//$objPackageLanguage = new package_language();
			//$objPackageLanguage->EditLearnableLanguages($this->get_uid());
			//$objPackagePrice = new package_price();
			//$objPackagePrice->SavePackagePriceandVat($this->get_uid());
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
		$support_language_uid			= (isset($_POST['support_language_uid']) ) ? $_POST['support_language_uid']:0;
		$arrMessages	= array();

		if( trim(strlen($name)) < 5 || trim(strlen($name)) > 255 ) {
			$arrMessages['error_name'] = "Name must be 5 to 255 characters in length.";
		} else if(!validation::isValid('text',$name) ) {
			$arrMessages['error_name'] = "Please enter valid name.";
		}

		if( $support_language_uid == 0 ) {
			$arrMessages['error_support_language'] = "Please select support language.";
		} else if(!validation::isValid('int',$support_language_uid) ) {
			$arrMessages['error_support_language'] = "Please select valid support language.";
		}

		if(!isset($_POST['learnable_language_uid'])) {
			$arrMessages['error_learnable_language'] = "Please select at-least one available language.";
		}

		$result = $this->isValidPriceandVat();
		if(is_array($result) && count($result)) {
			if(is_array($result['arrPrice']) && count($result['arrPrice'])) {
				$arrMessages['error_price'] = "Please enter valid price in ".implode(',',$result['arrPrice'])." locale.";
			}
			if(is_array($result['arrVat']) && count($result['arrVat'])) {
				$arrMessages['error_vat'] = "Please enter valid VAT% in ".implode(',',$result['arrVat'])." locale.";
			}
		}

		if(count($arrMessages) == 0) {
			$this->set_name($name);
			$this->set_support_language_uid($support_language_uid);
			$this->set_updated_date(date('Y-m-d H:i:s'));
			$this->set_iupdated_date(time());
			$arrLearnableLanguages =array(
				'language_uids'=>$_POST['learnable_language_uid']
			);
			$this->set_learnable_language(json_encode($arrLearnableLanguages));
			$this->set_pricing($this->preparePriceJson());
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

	public function updatePackagedates($package_uid) {
		if(isset($package_uid) && is_numeric($package_uid) && $package_uid > 0) {
			parent::__construct($package_uid,__CLASS__);
			$this->load();
			$this->set_updated_date(date('Y-m-d H:i:s'));
			$this->set_iupdated_date(time());
			$this->save();
		}
	}

	private function preparePriceJson() {
		$arrPricing = array();
		if(isset($_POST['price']) && is_array($_POST['price']) && isset($_POST['vat']) && is_array($_POST['vat'])) {
			foreach($_POST['price'] as $locale => $value) {
				$arrPricing[] = array(
					'locale'	=>$locale,
					'price'		=>($value>0)?$value:'0.00',
					'vat'		=>(isset($_POST['vat'][$locale]) && is_numeric($_POST['vat'][$locale]))?$_POST['vat'][$locale]:'0.00'
				);
			}
		}
		
		return json_encode(
			array(
				'pricing'	=>$arrPricing
			)
		);
	}

	public function SavePackageSections($package_uid=null,$sections=array()) {
		if($package_uid!=null && is_array($sections) && count($sections)>0) {
			parent::__construct($package_uid, __CLASS__);
			if($this->get_valid()) {
				$this->load();
				$arrSections = array();
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
				$this->save();
			}
		}
	}


	public function SavePackageGames($package_uid=null,$games=array()) {
		if($package_uid!=null && is_array($games) && count($games)>0) {
			parent::__construct($package_uid, __CLASS__);
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


	public function isValidPriceandVat() {
		$arrPrice	= array();
		$arrVat		= array();
		if(isset($_POST['price']) && is_array($_POST['price'])) {
			foreach($_POST['price'] as $index => $value) {
				if(trim($value)!='' && (!is_numeric($value) || strlen(trim($value))>11)) {
					$arrPrice[] = '<i><b>'.$index.'</b></i>';
				}
			}
		}
		if(isset($_POST['vat']) && is_array($_POST['vat'])) {
			foreach($_POST['vat'] as $index => $value) {
				if(trim($value)!='' && (!is_numeric($value) || strlen(trim($value))>5)) {
					$arrVat[] = '<i><b>'.$index.'</b></i>';
				}
			}
		}
		return array(
			'arrPrice'	=>$arrPrice,
			'arrVat'	=>$arrVat
		);
	}

}
?>