<?php

class reseller_sub_package extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function softDelete($uid) {
		$sql = "UPDATE ";
		$sql.="`reseller_sub_package` ";
		$sql.=" SET ";
		$sql.=" deleted='1' ";
		$sql.=" WHERE ";
		$sql.=" uid='{$uid}'";
		database::query($sql);
	}

	public function getList($reseller_uid=null,$all=false) {
		if($reseller_uid!=null) {
			if(!$all) {
			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`reseller_sub_package` ";
			$query.="WHERE ";
			$query.="`reseller_uid`='".$reseller_uid."'";
			$this->setPagination( $query );
			}
			$query ="SELECT * ";
			$query.="FROM ";
			$query.="`reseller_sub_package` ";
			$query.="WHERE ";
			$query.="`reseller_uid`='".$reseller_uid."' ";
			if(!$all) {
				$query.= "LIMIT ".$this->get_limit();
			}
			return database::arrQuery($query);
		}
	}

	public function getListActivatedPackages($reseller_uid=null,$all=false) {
		if($reseller_uid!=null) {
			if(!$all) {
			if(isset($_SESSION['user']['school_uid'])) {
				$subQuery ="SELECT ";
				$subQuery.="`package_uid` ";
				$subQuery.="FROM ";
				$subQuery.="`school_packages` ";
				$subQuery.="WHERE ";
				$subQuery.="`school_uid` = '".$_SESSION['user']['school_uid']."' ";
				$subQuery.="AND ";
				$subQuery.="`is_cancelled` = '0' ";
			} else {
				$subQuery=0;
			}

			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`reseller_sub_package` ";
			$query.="WHERE ";
			$query.="`reseller_uid`='".$reseller_uid."'";
			$query.="AND ";
			$query.="`is_active`='1' ";
			$query.="AND ";
			$query.="`package_type`='school' ";
			$query.="AND ";
			$query.="`uid` NOT IN (".$subQuery.")";

			$this->setPagination( $query );
			}
			$query ="SELECT * ";
			$query.="FROM ";
			$query.="`reseller_sub_package` ";
			$query.="WHERE ";
			$query.="`reseller_uid`='".$reseller_uid."' ";
			$query.="AND ";
			$query.="`is_active`='1' ";
			$query.="AND ";
			$query.="`package_type`='school' ";
			$query.="AND ";
			$query.="`uid` NOT IN (".$subQuery.")";
			if(!$all) {
				$query.= "LIMIT ".$this->get_limit();
			}
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
		$support_language_uid			= (isset($_POST['support_language_uid']) ) ? $_POST['support_language_uid']:0;
		$reseller_uid	= (isset($_POST['reseller_uid']) ) ? $_POST['reseller_uid']:0;
		$price	= (isset($_POST['price'])) ? $_POST['price']:0;
		$vat	= (isset($_POST['vat'])) ? $_POST['vat']:0;
		$package_type = (isset($_POST['package_type']))?$_POST['package_type']:'school';
		$arrMessages	= array();

		if( trim(strlen($name)) < 5 || trim(strlen($name)) > 255 ) {
			$arrMessages['error_name'] = "Name must be 5 to 255 characters in length.";
		} else if(!validation::isValid('text',$name) ) {
			$arrMessages['error_name'] = "Please enter valid name.";
		}

		if(!validation::isValid('int',$price) ) {
			$arrMessages['error_price'] = "Please enter valid price.";
		} else if( trim(strlen($price)) > 8) {
			$arrMessages['error_price'] = "Price shound not up to 8 digits.";
		}

		if(!validation::isValid('int',$vat) ) {
			$arrMessages['error_vat'] = "Please enter valid TAX.";
		} else if( trim(strlen($vat)) > 6) {
			$arrMessages['error_vat'] = "TAX shound not up to 6 digits.";
		}

		if( $support_language_uid == 0 ) {
			$arrMessages['error_support_language'] = "Please select support language.";
		} else if(!validation::isValid('int',$support_language_uid) ) {
			$arrMessages['error_support_language'] = "Please select valid support language.";
		}

		if(!isset($_POST['learnable_language_uid'])) {
			$arrMessages['error_learnable_language'] = "Please select at-least one available language.";
		}

		/*
		$result = $this->isValidPriceandVat();
		if(is_array($result) && count($result)) {
			if(is_array($result['arrPrice']) && count($result['arrPrice'])) {
				$arrMessages['error_price'] = "Please enter valid price in ".implode(',',$result['arrPrice'])." locale.";
			}
			if(is_array($result['arrVat']) && count($result['arrVat'])) {
				$arrMessages['error_vat'] = "Please enter valid VAT% in ".implode(',',$result['arrVat'])." locale.";
			}
		}
		*/

		if(count($arrMessages) == 0) {
			$this->set_reseller_uid($reseller_uid);
			$this->set_name($name);
			$this->set_price($price);
			$this->set_vat($vat);
			$this->set_support_language_uid($support_language_uid);
			$this->set_updated_date(date('Y-m-d H:i:s'));
			$this->set_iupdated_date(time());
			$this->set_package_type($package_type);
			$arrLearnableLanguages =array(
				'language_uids'=>$_POST['learnable_language_uid']
			);
			$this->set_learnable_language(json_encode($arrLearnableLanguages));
			//$this->set_pricing($this->preparePriceJson());
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

	public function getFields() {
		$response = array();
		foreach ($this->arrFields as $key => $val) {
			$response[$key] = $this->arrFields[$key]['Value'];
		}
		return $response;
	}

	public function getLearnableLanguages($resellerUid, $packageUid) {
		$sql = "SELECT learnable_language_uid FROM ";
		$sql.=" `reseller_sub_package_language`";
		$sql.=" WHERE ";
		$sql.=" package_uid IN (";
		$sql.="SELECT uid FROM `reseller_sub_package`";
		$sql.=" WHERE ";
		$sql.=" reseller_uid='{$resellerUid}'";
		$sql.=" AND package_uid='{$packageUid}'";
		$sql.=" AND iupdated_date='0'";
		$sql.=" )";
		$result = database::query($sql);
		$arrResult = array();
		while ($row = mysql_fetch_assoc($result)) {
			$arrResult[] = $row["learnable_language_uid"];
		}
		return $arrResult;
	}

	public function getPackagesByType($type="school",$reseller_uid=null) {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`is_default_school_package` ";
		$query.=",`is_default_homeuser_package` ";
		$query.="FROM ";
		$query.="`reseller_sub_package` ";
		$query.="WHERE ";
		$query.="`package_type` = '".$type."' ";
		$query.="AND ";
		$query.="`reseller_uid`='".$reseller_uid."' ";
		$query.="ORDER BY `name` ";
		return database::arrQuery($query);
	}
	
	public function setDefaultPackages() {
		if(isset($_POST['is_default_school_package']) && is_numeric($_POST['is_default_school_package'])) {
			$query ="UPDATE ";
			$query.="`reseller_sub_package` ";
			$query.="SET ";
			$query.="`is_default_school_package`='0' ";
			$query.="WHERE ";
			$query.="`reseller_uid` = '".$_SESSION['user']['uid']."' ";
			database::query($query);

			$query ="UPDATE ";
			$query.="`reseller_sub_package` ";
			$query.="SET ";
			$query.="`is_default_school_package`='1' ";
			$query.="WHERE ";
			$query.="`uid` = '".$_POST['is_default_school_package']."' ";
			$query.="AND ";
			$query.="`reseller_uid` = '".$_SESSION['user']['uid']."' ";
			$query.="LIMIT 1";
			database::query($query);
		}

		if(isset($_POST['is_default_homeuser_package']) && is_numeric($_POST['is_default_homeuser_package'])) {
			$query ="UPDATE ";
			$query.="`reseller_sub_package` ";
			$query.="SET ";
			$query.="`is_default_homeuser_package`='0' ";
			$query.="WHERE ";
			$query.="`reseller_uid` = '".$_SESSION['user']['uid']."' ";
			database::query($query);

			$query ="UPDATE ";
			$query.="`reseller_sub_package` ";
			$query.="SET ";
			$query.="`is_default_homeuser_package`='1' ";
			$query.="WHERE ";
			$query.="`uid` = '".$_POST['is_default_homeuser_package']."' ";
			$query.="AND ";
			$query.="`reseller_uid` = '".$_SESSION['user']['uid']."' ";
			$query.="LIMIT 1";
			database::query($query);
		}
	}
}

?>