<?php

/**
 * packagescript.php
 */

class packageScript extends Controller {

	private $json_languages		= array(0);
	private $json_years			= array();
	private $json_units			= array();
	private $json_sections		= array();
	private $json_section_uids	= array();
	private $json_games			= array();
	private $games				= array();

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		$this->index();
	}

	private function index() {
		$query ="INSERT ";
		$query.="INTO ";
		$query.="`reseller_sub_package` ( ";
			$query.="`reseller_uid`,";
			$query.="`name`,";
			$query.="`support_language_uid`,";
			$query.="`created_date`,";
			$query.="`learnable_language`,";
			$query.="`price`,";
			$query.="`vat`,";
			$query.="`sections`,";
			$query.="`games`,";
			$query.="`is_active`,";
			$query.="`package_type`,";
			$query.="`is_default_school_package`";
		$query.=") ";
		$query.="SELECT ";
		$query.="`reseller_uid`,";
		$query.="`name`,";
		$query.="`support_language_uid`,";
		$query.="'".date('Y-m-d H:i:s')."',";
		$query.="`learnable_language`,";
		$query.="`price`,";
		$query.="`vat`,";
		$query.="`sections`,";
		$query.="`games`,";
		$query.="`is_active`,";
		$query.="'school',";
		$query.="`is_default_school_package` ";
		$query.="FROM ";
		$query.="`reseller_sub_package` ";
		$query.="WHERE ";
		echo $query.="`package_type`='homeuser'";
	}

	private function index_old2() {
		$arrLoclase= array(
			'dn','me','ae'
		);

		$query ="SELECT ";
		$query.="`iuser_uid`, ";
		$query.="`locale_rights` ";
		$query.="FROM ";
		$query.="`profile_reseller` ";
		$query.="WHERE ";
		$query.="`locale_rights`!=''  ";
		$query.="AND  ";
		$query.="`locale_rights` IN ('".implode("','",$arrLoclase)."') ";
		$query.="GROUP BY ";
		$query.="`locale_rights` ";
		$resultReseller = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($resultReseller)) {
			while($arrReseller = mysql_fetch_array($resultReseller)) {
				//$query = "DELETE FROM `reseller_sub_package` WHERE `reseller_uid`='".$arrReseller['iuser_uid']."'";
				//$resultResellerPackage = database::query($query);

				$query ="INSERT ";
					$query.="INTO ";
					$query.="`reseller_sub_package` ( ";
						$query.="`reseller_uid`,";
						$query.="`name`,";
						$query.="`support_language_uid`,";
						$query.="`created_date`,";
						$query.="`learnable_language`,";
						$query.="`price`,";
						$query.="`vat`,";
						$query.="`sections`,";
						$query.="`games`,";
						$query.="`is_active`,";
						$query.="`package_type`,";
						$query.="`is_default_school_package`";
					$query.=") ";
					$query.="SELECT ";
					$query.="'".$arrReseller['iuser_uid']."',";
					$query.="`name`,";
					$query.="'".$this->getLanguageUid($arrReseller['locale_rights'])."',";
					$query.="'".date('Y-m-d H:i:s')."',";
					$query.="`learnable_language`,";
					$query.="`price`,";
					$query.="`vat`,";
					$query.="`sections`,";
					$query.="`games`,";
					$query.="`is_active`,";
					$query.="`package_type`,";
					$query.="`is_default_school_package` ";
					$query.="FROM ";
					$query.="`reseller_sub_package` ";
					$query.="WHERE ";
					$query.="`reseller_uid`='8637'";

					// insert package for reseller
					$resultResellerPackage = database::query($query);
					// store reseller package uid in a variable
			}
		}
	}

	private function index_old() {
		set_time_limit(0);
/*
		$arrLoclase= array(
			'en',
			'fr',
			'de',
			'it',
			'sp',
			'dn',
			'nl',
			'me',
			'ae',
			'us',
			'ca',
			'mx',
			'cl',
			'cn',
			'au',
			'nz',
			'bz'
		);
		*/

		$arrLoclase= array(
			'dn','me','ae'
		);
		$arrPackage= array(
			'en'=>'UK School Package',
			'fr'=>'France School Package',
			'de'=>'Germany School Package',
			'it'=>'Italy School Package',
			'sp'=>'Spain School Package',
			'dn'=>'Denmark School Package',
			'nl'=>'Netherlands School Package',
			'me'=>'Gulf School Package',
			'ae'=>'UAE School Package',
			'us'=>'USA School Package',
			'ca'=>'Canada School Package',
			'mx'=>'Mexico School Package',
			'cl'=>'Chile School Package',
			'cn'=>'China School Package',
			'au'=>'Australia School Package',
			'nz'=>'New Zealand School Package',
			'bz'=>'Brazil School Package'
		);

		$arrPair1 = array(
			14,3,7,6,4
		);
		$arrPair2 = array(
			14,3,7,6,23
		);
		
		// get all resellers
		$query ="SELECT ";
		$query.="`iuser_uid`, ";
		$query.="`locale_rights` ";
		$query.="FROM ";
		$query.="`profile_reseller` ";
		$query.="WHERE ";
		$query.="`locale_rights`!=''  ";
		$query.="AND  ";
		$query.="`locale_rights` IN ('".implode("','",$arrLoclase)."') ";
		$query.="GROUP BY ";
		$query.="`locale_rights` ";
		$resultReseller = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($resultReseller)) {
			while($arrReseller = mysql_fetch_array($resultReseller)) {

			$learnable_language = '';
			if(in_array($arrReseller['locale_rights'],array('us','ca','mx'))) {
				$arrLearnableLanguages =array(
					'language_uids'=>$arrPair2
				);
				$learnable_language = json_encode($arrLearnableLanguages);
			} else {
				$arrLearnableLanguages =array(
					'language_uids'=>$arrPair1
				);
				$learnable_language = json_encode($arrLearnableLanguages);
			}

				$query ="INSERT ";
				$query.="INTO ";
				$query.="`reseller_sub_package` ( ";
					$query.="`reseller_uid`,";
					$query.="`name`,";
					$query.="`support_language_uid`,";
					$query.="`created_date`,";
					$query.="`learnable_language`,";
					$query.="`price`,";
					$query.="`vat`,";
					$query.="`sections`,";
					$query.="`games`,";
					$query.="`is_active`,";
					$query.="`package_type`,";
					$query.="`is_default_school_package`";
				$query.=") ";
				$query.="SELECT ";
				$query.="'".$arrReseller['iuser_uid']."',";
				$query.="'".$arrPackage[$arrReseller['locale_rights']]."',";
				$query.="'".$this->getLanguageUid($arrReseller['locale_rights'])."',";
				$query.="'".date('Y-m-d H:i:s')."',";
				$query.="'".$learnable_language."',";
				$query.="`price`,";
				$query.="`vat`,";
				$query.="`sections`,";
				$query.="`games`,";
				$query.="'1',";
				$query.="'school',";
				$query.="'0' ";
				$query.="FROM ";
				$query.="`reseller_sub_package` ";
				$query.="WHERE ";
				$query.="`uid`='1'";
				echo $query; echo '<br><br>';
				// insert package for reseller
				$resultResellerPackage = database::query($query);
				// store reseller package uid in a variable
			}
		}
	}

	private function default_packages_with_all_perimissions_for_school() {
		set_time_limit(0);
		// get all resellers
		$query ="SELECT ";
		$query.="`iuser_uid`, ";
		$query.="`locale_rights` ";
		$query.="FROM ";
		$query.="`profile_reseller` ";
		$query.="WHERE ";
		$query.="`locale_rights`!=''";
		$query.="GROUP BY ";
		$query.="`locale_rights` ";
		$resultReseller = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($resultReseller)) {
			while($arrReseller = mysql_fetch_array($resultReseller)) {

				$query ="INSERT ";
				$query.="INTO ";
				$query.="`reseller_sub_package` ( ";
					$query.="`reseller_uid`,";
					$query.="`name`,";
					$query.="`support_language_uid`,";
					$query.="`created_date`,";
					$query.="`learnable_language`,";
					$query.="`price`,";
					$query.="`vat`,";
					$query.="`sections`,";
					$query.="`games`,";
					$query.="`is_active`,";
					$query.="`package_type`,";
					$query.="`is_default_school_package`";
				$query.=") ";
				$query.="SELECT ";
				$query.="'".$arrReseller['iuser_uid']."',";
				$query.="'default school package',";
				$query.="'".$this->getLanguageUid($arrReseller['locale_rights'])."',";
				$query.="'".date('Y-m-d H:i:s')."',";
				$query.="`learnable_language`,";
				$query.="`price`,";
				$query.="`vat`,";
				$query.="`sections`,";
				$query.="`games`,";
				$query.="'1',";
				$query.="'school',";
				$query.="'1' ";
				$query.="FROM ";
				$query.="`reseller_sub_package` ";
				$query.="WHERE ";
				$query.="`uid`='1'";

				// insert package for reseller
				$resultResellerPackage = database::query($query);
				// store reseller package uid in a variable


				$query ="INSERT ";
				$query.="INTO ";
				$query.="`reseller_sub_package` ( ";
					$query.="`reseller_uid`,";
					$query.="`name`,";
					$query.="`support_language_uid`,";
					$query.="`created_date`,";
					$query.="`learnable_language`,";
					$query.="`price`,";
					$query.="`vat`,";
					$query.="`sections`,";
					$query.="`games`,";
					$query.="`is_active`,";
					$query.="`package_type`,";
					$query.="`is_default_school_package`";
				$query.=") ";
				$query.="SELECT ";
				$query.="'".$arrReseller['iuser_uid']."',";
				$query.="'default school package [en]',";
				$query.="'14',";
				$query.="'".date('Y-m-d H:i:s')."',";
				$query.="`learnable_language`,";
				$query.="`price`,";
				$query.="`vat`,";
				$query.="`sections`,";
				$query.="`games`,";
				$query.="'1',";
				$query.="'school',";
				$query.="'0' ";
				$query.="FROM ";
				$query.="`reseller_sub_package` ";
				$query.="WHERE ";
				$query.="`uid`='1'";

				// insert package for reseller
				$resultResellerPackage = database::query($query);
			}
		}
	}

	private function getLanguageUid($locale='en') {
		$query ="SELECT ";
		$query.="`uid` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="WHERE ";
		$query.="`prefix`='".$locale."' ";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			$row = mysql_fetch_array($result);
			return $row['uid'];
		}
		return 14;
	}
}

?>