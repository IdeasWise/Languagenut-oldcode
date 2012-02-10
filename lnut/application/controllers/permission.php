<?php

/**
 * permission.php
 */

class Permission extends Controller {

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		if(isset($arrPaths[1]) && $arrPaths[1]=='detail') {
			$this->doJsonDetail();
		} else {
			$this->create();
			$this->doJson();
		}
	}

	protected function doJson() {
		//$arrSupportLanguages = user::getUserPackage();
		$arrLanguage = array();
		if(isset($_REQUEST['package_token'])) {

			if(in_array($_REQUEST['package_token'],array('standard','home','lgfl_standard'))) {
				$locale					= config::get('locale');
				$support_language_id	= 14;
				$query ="SELECT ";
				$query.="`uid` ";
				$query.="from ";
				$query.="`language` ";
				$query.="WHERE ";
				$query.="`prefix` = '".$locale."' ";
				$query.="LIMIT 1";
				$result = database::query($query);
				if($result && mysql_num_rows($result) ){
					$row = mysql_fetch_array($result);
					$support_language_id = $row['uid'];
				}
				$arrLanguage[] = $support_language_id;
			} else if($_REQUEST['package_token']=='gaelic') {
				$query = "SELECT `uid` FROM `language` WHERE `prefix` = 'en'";
				$result = database::query($query);
				while($arrRow = mysql_fetch_array($result)) {
					$arrLanguage[] = $arrRow['uid'];
				}
			} else if(in_array($_REQUEST['package_token'],array('eal','lgfl_eal'))) {
				$arrELLlanguage = array(
					"'so'",
					"'ar'",
					"'fr'",
					"'pt'",
					"'mx'",
					"'cc'",
					"'fr'",
					"'ge'",
					"'ht'",
					"'it'"
				);
				$query = "SELECT `uid` FROM `language` WHERE `prefix` IN (".implode(',',$arrELLlanguage).") ";
				$result = database::query($query);
				while($arrRow = mysql_fetch_array($result)) {
					$arrLanguage[] = $arrRow['uid'];
				}
			}
			echo json_encode(
				array(
					'support_languages' => $arrLanguage
				)
			);
		} else {
			echo '{"success":"false"}';
		}
		
	}
	protected function doJsonDetail() {
		if(isset($_REQUEST['support_languauge_uid']) && is_numeric($_REQUEST['support_languauge_uid']) && isset($_REQUEST['package_token']) && !empty($_REQUEST['package_token'])) {
			if($_REQUEST['package_token']=='standard' || $_REQUEST['package_token']=='home') {
				if(in_array($_REQUEST['support_languauge_uid'],array(28,27))) {
					$json_file = config::get('cache').'json/eal_package.json';
					echo file_get_contents($json_file);
				} else if(in_array($_REQUEST['support_languauge_uid'],array(107,109,114))) {
					$json_file = config::get('cache').'json/ae_standard_home_package.json';
					echo str_replace('sl_uid',$_REQUEST['support_languauge_uid'],file_get_contents($json_file));
				} else if($_REQUEST['support_languauge_uid']==20) {
					$json_file = config::get('cache').'json/dk_standard_home_package.json';
					echo str_replace('sl_uid',$_REQUEST['support_languauge_uid'],file_get_contents($json_file));
				} else if($_REQUEST['support_languauge_uid']==23) {
					$json_file = config::get('cache').'json/mx_standard_home_package.json';
					echo str_replace('sl_uid',$_REQUEST['support_languauge_uid'],file_get_contents($json_file));
				} else if($_REQUEST['support_languauge_uid']==21) {
					$json_file = config::get('cache').'json/nl_standard_home_package.json';
					echo str_replace('sl_uid',$_REQUEST['support_languauge_uid'],file_get_contents($json_file));
				} else {
					//$json_file = config::get('cache').'json/standard_home_package.json';
					$json_file = config::get('cache').'json/ae_standard_home_package.json';
					echo str_replace('sl_uid',$_REQUEST['support_languauge_uid'],file_get_contents($json_file));
				}
			} else if($_REQUEST['package_token']=='gaelic') {
				$json_file = config::get('cache').'json/gaelic_package.json';
				echo file_get_contents($json_file);
			} else if($_REQUEST['package_token']=='eal') {
				$json_file = config::get('cache').'json/eal_package.json';
				echo str_replace('sl_uid',$_REQUEST['support_languauge_uid'],file_get_contents($json_file));
			} else if($_REQUEST['package_token']=='lgfl_standard') {
				$json_file = config::get('cache').'json/lgfl_standard.json';
				echo str_replace('sl_uid',$_REQUEST['support_languauge_uid'],file_get_contents($json_file));
			} else if($_REQUEST['package_token']=='lgfl_eal') {
				$json_file = config::get('cache').'json/lgfl_eal.json';
				echo str_replace('sl_uid',$_REQUEST['support_languauge_uid'],file_get_contents($json_file));
			} else {
				echo '{"success":"false"}';
			}
		} else {
			echo '{"success":"false"}';
		}
	}
	/*
	* please do not remove folloing function we're using it to create new json files
	*/
	protected function create() {
		/*
		$json_file = config::get('cache').'json/package_permission.json';
		$content = str_replace('sl_uid',14,file_get_contents($json_file));
		echo '<pre>';
		print_r(json_decode($content));
		echo '</pre>';
		*/
		/*
		$arrJson = array();
		$query_unit = "SELECT `uid` FROM `units` WHERE `active` = '1' ORDER BY `unit_number`";
		$res_unit = database::query($query_unit);
		while($arrUnit=mysql_fetch_array($res_unit)) {
			$query_section = "SELECT `uid` FROM `sections` WHERE `active` = '1' AND `unit_uid`='".$arrUnit['uid']."' ORDER BY `section_number`";
			$res_section = database::query($query_section);
			while($arrSection=mysql_fetch_array($res_section)) {
				$arrJson['u'][$arrUnit['uid']]['s'][$arrSection['uid']]['g'] = true;
			}
		}
		echo '<pre>';
		print_r($arrJson);
		echo '</pre>';
		*/

$arrUnitSectionGames = array(
	'u' => array(
	1 => array(
		's' => array(
			1 => array(
				'g' => true
						),

					2 => array(
							'g' => true
						),

					3 => array(
							'g' => true
						),

					4 => array(
							'g' => true
						),

					5 => array(
							'g' => true
						),

					6 => array(
							'g' => true
						),

				),

		),

	2 => array(
			's' => array(
					7 => array(
							'g' => true
						),

					8 => array(
							'g' => true
						),

					9 => array(
							'g' => true
						),

					10 => array(
							'g' => true
						),

					11 => array(
							'g' => true
						),

					12 => array(
							'g' => true
						),

				),

		),

	3 => array(
			's' => array(
					13 => array(
							'g' => true
						),

					14 => array(
							'g' => true
						),

					15 => array(
							'g' => true
						),

					16 => array(
							'g' => true
						),

					17 => array(
							'g' => true
						),

					18 => array(
							'g' => true
						),

				),

		),

	4 => array(
			's' => array(
					19 => array(
							'g' => true
						),

					20 => array(
							'g' => true
						),

					21 => array(
							'g' => true
						),

					22 => array(
							'g' => true
						),

					23 => array(
							'g' => true
						),

					24 => array(
							'g' => true
						),

				),

		),

	5 => array(
			's' => array(
					25 => array(
							'g' => true
						),

					26 => array(
							'g' => true
						),

					27 => array(
							'g' => true
						),

					28 => array(
							'g' => true
						),

					29 => array(
							'g' => true
						),

					30 => array(
							'g' => true
						),

				),

		),

	6 => array(
			's' => array(
					31 => array(
							'g' => true
						),

					32 => array(
							'g' => true
						),

					33 => array(
							'g' => true
						),

					34 => array(
							'g' => true
						),

					35 => array(
							'g' => true
						),

					36 => array(
							'g' => true
						),

				),

		),

	7 => array(
			's' => array(
					37 => array(
							'g' => true
						),

					38 => array(
							'g' => true
						),

					39 => array(
							'g' => true
						),

					40 => array(
							'g' => true
						),

					41 => array(
							'g' => true
						),

					42 => array(
							'g' => true
						),

				),

		),

	8 => array(
			's' => array(
					43 => array(
							'g' => true
						),

					44 => array(
							'g' => true
						),

					45 => array(
							'g' => true
						),

					46 => array(
							'g' => true
						),

					47 => array(
							'g' => true
						),

					48 => array(
							'g' => true
						),

				),

		),

	9 => array(
			's' => array(
					49 => array(
							'g' => true
						),

					50 => array(
							'g' => true
						),

					51 => array(
							'g' => true
						),

					52 => array(
							'g' => true
						),

					53 => array(
							'g' => true
						),

					54 => array(
							'g' => true
						),

				),

		),

	10 => array(
			's' => array(
					55 => array(
							'g' => true
						),

					56 => array(
							'g' => true
						),

					57 => array(
							'g' => true
						),

					58 => array(
							'g' => true
						),

					59 => array(
							'g' => true
						),

					60 => array(
							'g' => true
						),

				),

		),

	11 => array(
			's' => array(
					61 => array(
							'g' => true
						),

					62 => array(
							'g' => true
						),

					63 => array(
							'g' => true
						),

					64 => array(
							'g' => true
						),

					65 => array(
							'g' => true
						),

					66 => array(
							'g' => true
						),

				),

		),

	12 => array(
			's' => array(
					67 => array(
							'g' => true
						),

					68 => array(
							'g' => true
						),

					69 => array(
							'g' => true
						),

					70 => array(
							'g' => true
						),

					71 => array(
							'g' => true
						),

					72 => array(
							'g' => true
						),

				),

		),

	13 => array(
			's' => array(
					73 => array(
							'g' => true
						),

					74 => array(
							'g' => true
						),

					75 => array(
							'g' => true
						),

					76 => array(
							'g' => true
						),

					77 => array(
							'g' => true
						),

					78 => array(
							'g' => true
						),

				),

		),

	14 => array(
			's' => array(
					79 => array(
							'g' => true
						),

					80 => array(
							'g' => true
						),

					81 => array(
							'g' => true
						),

					82 => array(
							'g' => true
						),

					83 => array(
							'g' => true
						),

					84 => array(
							'g' => true
						),

				),

		),

	15 => array(
			's' => array(
					85 => array(
							'g' => true
						),

					86 => array(
							'g' => true
						),

					87 => array(
							'g' => true
						),

					88 => array(
							'g' => true
						),

					89 => array(
							'g' => true
						),

					90 => array(
							'g' => true
						),

				),

		),

	16 => array(
			's' => array(
					91 => array(
							'g' => true
						),

					92 => array(
							'g' => true
						),

					93 => array(
							'g' => true
						),

					94 => array(
							'g' => true
						),

					95 => array(
							'g' => true
						),

					96 => array(
							'g' => true
						),

				),

		),

	17 => array(
			's' => array(
					97 => array(
							'g' => true
						),

					98 => array(
							'g' => true
						),

					99 => array(
							'g' => true
						),

					100 => array(
							'g' => true
						),

					101 => array(
							'g' => true
						),

					102 => array(
							'g' => true
						),

				),

		),

	18 => array(
			's' => array(
					103 => array(
							'g' => true
						),

					104 => array(
							'g' => true
						),

					105 => array(
							'g' => true
						),

					106 => array(
							'g' => true
						),

					107 => array(
							'g' => true
						),

					108 => array(
							'g' => true
						),

				),

		),

	19 => array(
			's' => array(
					109 => array(
							'g' => true
						),

					110 => array(
							'g' => true
						),

					111 => array(
							'g' => true
						),

					112 => array(
							'g' => true
						),

					113 => array(
							'g' => true
						),

					114 => array(
							'g' => true
						),

				),

		),

	20 => array(
			's' => array(
					115 => array(
							'g' => true
						),

					116 => array(
							'g' => true
						),

					117 => array(
							'g' => true
						),

					118 => array(
							'g' => true
						),

					119 => array(
							'g' => true
						),

					120 => array(
							'g' => true
						),

				),

		),

	21 => array(
			's' => array(
					121 => array(
							'g' => true
						),

					122 => array(
							'g' => true
						),

					123 => array(
							'g' => true
						),

					124 => array(
							'g' => true
						),

					125 => array(
							'g' => true
						),

					126 => array(
							'g' => true
						),

				),

		),

	22 => array(
			's' => array(
					127 => array(
							'g' => true
						),

					128 => array(
							'g' => true
						),

					129 => array(
							'g' => true
						),

					130 => array(
							'g' => true
						),

					131 => array(
							'g' => true
						),

					132 => array(
							'g' => true
						),

				),

		),

	23 => array(
			's' => array(
					133 => array(
							'g' => true
						),

					134 => array(
							'g' => true
						),

					135 => array(
							'g' => true
						),

					136 => array(
							'g' => true
						),

					137 => array(
							'g' => true
						),

					138 => array(
							'g' => true
						),

				),

		),

	24 => array(
			's' => array(
					139 => array(
							'g' => true
						),

					140 => array(
							'g' => true
						),

					141 => array(
							'g' => true
						),

					142 => array(
							'g' => true
						),

					143 => array(
							'g' => true
						),

					144 => array(
							'g' => true
						),

				),

		),

	),

);


$arrUnitSectionGames = array(
	'u' => array(
		1 => array(
			's' => array(
				1 => array(
					'g' => true
					),
				2 => array(
					'g' => true
				),

				3 => array(
					'g' => true
				),

				4 => array(
					'g' => true
				),

				5 => array(
					'g' => true
				),

				6 => array(
					'g' => true
				)
			)
		),
		2 => array(
			's' => array(
				7 => array(
					'g' => true
				),
				8 => array(
					'g' => true
				),
				9 => array(
					'g' => true
				),
				10 => array(
					'g' => true
				),
				11 => array(
					'g' => true
				),
				12 => array(
					'g' => true
				)
			)
		),
		3 => array(
			's' => array(
				13 => array(
					'g' => true
				),
				14 => array(
					'g' => true
				),
				15 => array(
					'g' => true
				),
				16 => array(
					'g' => true
				),
				17 => array(
					'g' => true
				),
				18 => array(
					'g' => true
				)
			)
		)
	)

);

		@ini_set('memory_limit', '256M');
		$arrJson['data'] = array();
		$arrJson['data']['sl'] = 'sl_uid';
		//$arrDK = array(3,4,5,6,7,10,11,12,14);
		

		//$query = "SELECT `uid` FROM `language` WHERE `uid` IN (3,4,5,6,7,10,11,12,14,16,17,23,24,75)";
		//$query = "SELECT `uid` FROM `language` WHERE `uid` IN (3,4,5,6,7,10,14)";
		$query = "SELECT `uid` FROM `language` WHERE `uid` IN (14)";
		$result = database::query($query);
		while($arrRow = mysql_fetch_array($result)) {
			$arrJson['data']['l'][$arrRow['uid']]=$arrUnitSectionGames;
		}

		$json_file = config::get('cache').'json/cl_lgfl_standard.json';
		//echo json_encode($arrJson);
		$fh = fopen($json_file, 'w');
		if($fh) {
			fwrite($fh, json_encode($arrJson));
			fclose($fh);
		}
	}

}





/*
rules

package_token=standard -  support_languages : one - learning_languages : many
package_token=home -  support_languages : one - learning_languages : many
package_token=gaelic -  support_languages : en - learning_languages : gaelic
package_token=eal -  support_languages : many - learning_languages : all json will be for en

*/
?>