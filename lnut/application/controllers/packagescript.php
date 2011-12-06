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
		$query ="SELECT ";
		$query.="`iuser_uid`  ";
		$query.="FROM  ";
		$query.="`profile_reseller`  ";
		$query.="WHERE  ";
		$query.="`locale_rights`='en'";
		$resultReseller = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($resultReseller)) {
			while($arrReseller = mysql_fetch_array($resultReseller)) {

				$query="create new package query"
				$reseller_package_uid=1;

				$query="get Reseller's Active Schools";
				$resultSchool = database::query($query);
				if(mysql_error()=='' && mysql_num_rows($resultSchool)) {
					while($arrSchool = mysql_fetch_array($resultSchool)) {
						$query="Get 1 Active school admin for each school LIMIT 1";
						$resultSchoolAdmin = database::query($query);
						if(mysql_error()=='' && mysql_num_rows($resultSchoolAdmin)) {
							$arrSchoolAdmin = mysql_fetch_array($resultSchoolAdmin);

						}
					}
				}
			}
		}
	}

}

?>