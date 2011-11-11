<?php

class school_packages extends generic_object {

	public $json_languages		= array();
	public $json_years			= array();
	public $json_units			= array();
	public $json_sections		= array();
	public $json_section_uids	= array();
	public $json_games			= array();
	public $games				= array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function MakeABuyRequest($package_uid=null,$reseller_uid=null) {
		if($package_uid!=null && $reseller_uid!=null) {
			$objResellerSubPackage = new reseller_sub_package($package_uid);
			$objResellerSubPackage->load();
			if($reseller_uid==$objResellerSubPackage->get_reseller_uid()) {
				$this->set_package_uid($objResellerSubPackage->get_uid());
				$this->set_reseller_uid($objResellerSubPackage->get_reseller_uid());
				$this->set_name($objResellerSubPackage->get_name());
				$this->set_support_language_uid($objResellerSubPackage->get_support_language_uid());
				$this->set_learnable_language($objResellerSubPackage->get_learnable_language());
				$this->set_sections($objResellerSubPackage->get_sections());
				$this->set_games($objResellerSubPackage->get_games());
				$this->set_requested_date(date('Y-m-d H:i:s'));
				$this->set_requested_by_uid($_SESSION['user']['uid']);
				$this->set_school_uid($_SESSION['user']['school_uid']);
				$this->insert();
			}
		}
	}

	public function CancelPurchaseRequest($package_uid=null,$reseller_uid=null,$school_uid=null) {
		if($package_uid!=null && $reseller_uid!=null) {
			parent::__construct($package_uid, __CLASS__);
			$this->load();
			if($reseller_uid==$this->get_reseller_uid() && ( isset($_SESSION['user']['school_uid']) && $_SESSION['user']['school_uid']==$this->get_school_uid()) || ($school_uid!=null && $school_uid==$this->get_school_uid())) {
				$this->set_is_cancelled(1);
				$this->set_canclled_date(date('Y-m-d H:i:s'));
				$this->set_canclled_by_uid($_SESSION['user']['uid']);
				$this->save();
			}
		}
	}

	public function ActivatePackage($package_uid=null,$reseller_uid=null,$school_uid=null) {
		if($package_uid!=null && $reseller_uid!=null && $school_uid!=null) {
			parent::__construct($package_uid, __CLASS__);
			$this->load();
			if($reseller_uid==$this->get_reseller_uid() && $school_uid==$this->get_school_uid()) {
				$this->set_is_approved(1);
				$this->set_approved_date(date('Y-m-d H:i:s'));
				$this->set_approved_by_uid($_SESSION['user']['uid']);
				$this->save();
			}
		}
	}

	public function getPendingRequests($school_uid=null) {
		if($school_uid!=null) {
			$query ="SELECT ";
			$query.="count(`uid`) AS `TOT`";
			$query.="FROM ";
			$query.="`school_packages` ";
			$query.="WHERE ";
			$query.="`school_uid` = '".$school_uid."' ";
			$query.="AND ";
			$query.="`is_cancelled` = '0' ";
			$query.="AND ";
			$query.="`is_approved` = '0' ";
			$result = database::query($query);
			if($result && mysql_error()=='' && mysql_num_rows($result)) {
				$row = mysql_fetch_array($result);
				return $row['TOT'];
			}
			return 0;
		}
		return 0;
	}

	public function getListPendingPackageOrders($reseller_uid=null,$school_uid=null,$all=false) {
		if($reseller_uid!=null) {
			if(!$all) {
			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`school_packages` ";
			$query.="WHERE ";
			if($school_uid!=null) {
				$query.="`school_uid` = '".$school_uid."' ";
			} else {
				$query.="`school_uid` = '".$_SESSION['user']['school_uid']."' ";
			}
			$query.="AND ";
			$query.="`is_cancelled` = '0' ";
			$query.="AND ";
			$query.="`is_approved` = '0' ";

			$this->setPagination( $query );
			}
			$query ="SELECT * ";
			$query.="FROM ";
			$query.="`school_packages` ";
			$query.="WHERE ";
			if($school_uid!=null) {
				$query.="`school_uid` = '".$school_uid."' ";
			} else {
				$query.="`school_uid` = '".$_SESSION['user']['school_uid']."' ";
			}
			$query.="AND ";
			$query.="`is_cancelled` = '0' ";
			$query.="AND ";
			$query.="`is_approved` = '0' ";
			if(!$all) {
				$query.= "LIMIT ".$this->get_limit();
			}
			return database::arrQuery($query);
		}
	}

	public function getSchoolActivePackages($reseller_uid=null,$school_uid=null,$all=false) {
		if($reseller_uid!=null) {
			if(!$all) {
			$query ="SELECT ";
			$query.="count(`uid`) ";
			$query.="FROM ";
			$query.="`school_packages` ";
			$query.="WHERE ";
			if($school_uid!=null) {
				$query.="`school_uid` = '".$school_uid."' ";
			} else {
				$query.="`school_uid` = '".$_SESSION['user']['school_uid']."' ";
			}
			$query.="AND ";
			$query.="`is_approved` = '1' ";

			$this->setPagination( $query );
			}
			$query ="SELECT * ";
			$query.="FROM ";
			$query.="`school_packages` ";
			$query.="WHERE ";
			if($school_uid!=null) {
				$query.="`school_uid` = '".$school_uid."' ";
			} else {
				$query.="`school_uid` = '".$_SESSION['user']['school_uid']."' ";
			}
			$query.="AND ";
			$query.="`is_approved` = '1' ";
			if(!$all) {
				$query.= "LIMIT ".$this->get_limit();
			}
			return database::arrQuery($query);
		}
	}

	public function getSchoolActivePackagesForClass($class_uid=null) {
		if($class_uid!=null) {
			$subQuery ="SELECT ";
			$subQuery.="`package_uid` ";
			$subQuery.="FROM ";
			$subQuery.="`class_packages` ";
			$subQuery.="WHERE ";
			$subQuery.="`class_uid`='".$class_uid."' ";
			$subQuery.="AND ";
			$subQuery.="`removed_by_uid`='0' ";


			$query ="SELECT * ";
			$query.="FROM ";
			$query.="`school_packages` ";
			$query.="WHERE ";
			$query.="`school_uid` = '".$_SESSION['user']['school_uid']."' ";
			$query.="AND ";
			$query.="`is_approved` = '1' ";
			$query.="AND ";
			$query.="`uid` NOT IN (".$subQuery.") ";
			return database::arrQuery($query);
		}
	}

	public function getSchoolActivePackagesForStudent($student_user_uid=null) {
		if($student_user_uid!=null) {
			$subQuery ="SELECT ";
			$subQuery.="`package_uid` ";
			$subQuery.="FROM ";
			$subQuery.="`student_packages` ";
			$subQuery.="WHERE ";
			$subQuery.="`student_user_uid`='".$student_user_uid."' ";
			$subQuery.="AND ";
			$subQuery.="`removed_by_uid`='0' ";

			$query ="SELECT * ";
			$query.="FROM ";
			$query.="`school_packages` ";
			$query.="WHERE ";
			$query.="`school_uid` = '".$_SESSION['user']['school_uid']."' ";
			$query.="AND ";
			$query.="`is_approved` = '1' ";
			$query.="AND ";
			$query.="`uid` NOT IN (".$subQuery.") ";
			return database::arrQuery($query);
		}
	}

	public function isValidPackage($package_uid=null) {
		if($package_uid!=null) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`school_packages` ";
			$query.="WHERE ";
			$query.="`reseller_uid`='".$_SESSION['user']['reseller_uid']."' ";
			$query.="AND ";
			$query.="`school_uid`='".$_SESSION['user']['school_uid']."' ";
			$query.="AND ";
			$query.="`uid`='".$package_uid."' ";
			$query.="AND ";
			$query.="`is_approved`='1' ";
			$query.="AND ";
			$query.="`is_cancelled`='0' ";
			$query.="LIMIT 0,1 ";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)==1) {
				parent::__construct($package_uid, __CLASS__);
				$this->load();
				$this->ParsePackage();
				return true;
			}
		}
		return false;
	}
	private function ParsePackage() {
		@ini_set('memory_limit', '256M');
		if($this->get_sections() != '') {
			$this->objJson = json_decode($this->get_sections());

			if(isset($this->objJson->sections)) {
				foreach($this->objJson->sections as $data) {
					$this->json_sections[] = $data->section_pair;
					$this->json_years[$data->learnable_language_uid][] = $data->year_uid;
					$this->json_units[$data->learnable_language_uid][$data->year_uid][] = $data->unit_uid;
					$this->json_section_uids[$data->learnable_language_uid][$data->unit_uid][] = $data->section_uid;
				}
			}
		}

		if($this->get_learnable_language() != '') {
			$this->objJson = json_decode($this->get_learnable_language());
			if(isset($this->objJson->language_uids) && is_array($this->objJson->language_uids)) {
				$this->json_languages = $this->objJson->language_uids;
			}
		}

		if($this->get_games() != '') {
			$this->objJson = json_decode($this->get_games());
			if(isset($this->objJson->games)) {
				foreach($this->objJson->games as $data) {
					$this->json_games[] = $data->game_pair;
					$this->games[$data->learnable_language_uid][$data->unit_uid][$data->section_uid][] = $data->game_uid;
					//$this->json_section_uids[$data->learnable_language_uid][$data->unit_uid][] = $data->section_uid;
				}
			}
		}
		//echo '<pre>';
		//print_r($this->games);
		//echo '</pre>';
		//exit;
	}

}

?>