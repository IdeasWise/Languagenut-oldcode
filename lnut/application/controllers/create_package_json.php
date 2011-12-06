<?php

/**
 * create_package_json.php
 */

class create_package_json extends Controller {

	private $games = array();

	public function __construct () {
		parent::__construct();
		set_time_limit(0);
		@ini_set('memory_limit', '256M');
		$this->index();
	}
	protected function create_dir () {
		//$dir = config::get('cache');
		//echo $dir.'json/reseller/';
		//chmod($dir.'json/reseller/', 0777);
		//mkdir($dir.'json/reseller/',0777);
	}
	protected function index() {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`reseller_uid`, ";
		$query.="`support_language_uid`, ";
//		$query.="`learnable_language`, ";
//		$query.="`sections`, ";
		$query.="`games` ";
		$query.="FROM ";
		$query.="`reseller_sub_package` ";
		$query.="ORDER BY ";
		$query.="`uid` LIMIT 13500,1000";
		echo $query;
		$result = database::query($query);
		$count = 0;
		if(mysql_error()=='' && mysql_num_rows($result)) {
			$josn_dir = config::get('cache').'json/reseller/';
			$arrGameKeys = array();
			$arrGameKeys = game::getGameKeys();
			while($arrRow = mysql_fetch_array($result)) {
				$json_file = $arrRow['reseller_uid'].'_'.$arrRow['uid'].'.json';

				if(!file_exists($josn_dir.$json_file)) {
					echo '<br>';
					echo 'creating: '.$josn_dir.$json_file;
					$count++;
					$arrJson['data'] = array();
					$this->ParsePackage($arrRow);
					$arrJson['data']['sl'] = $arrRow['support_language_uid'];
					foreach($this->games as $language_uid => $arrUnit) {
						/*
						if(!isset($arrJson['data']['language'])) {
							$arrJson['data']['language'] = array();
						}
						$lang_index = count($arrJson['data']['language']);
						$arrJson['data']['language'][$lang_index]['language_uid'] = $language_uid;
						*/
						foreach($arrUnit as $unit_uid => $arrSection ) {
						//	$arrJson['data']['language'][$lang_index]['units'][$unit_uid]['unit_uid'] = $unit_uid;
							foreach($arrSection as $section_uid => $arrGames) {
									if(count($arrGames) == count($arrGameKeys)) {
										$arrJson['data']['l'][$language_uid]['u'][$unit_uid]['s'][$section_uid]['g'] = true;
										continue;
									}
								foreach($arrGames as $game_uid) {
									if(isset($arrGameKeys[$game_uid])) {
										$arrJson['data']['l'][$language_uid]['u'][$unit_uid]['s'][$section_uid]['g'][] = $arrGameKeys[$game_uid];
										
									}
								}
							}
						}
					}

					$fh = fopen($josn_dir.$json_file, 'w');
					if($fh) {
						fwrite($fh, json_encode($arrJson));
						fclose($fh);
					}
				} else {
					echo '<br>';
					echo 'Skipped: '.$josn_dir.$json_file;
				}
			}
			//echo json_encode($arrJson);
		}
		die('<br>'.$count.' Files are generate successfully');
	}


	private function ParsePackage($arrPackage=array()) {
		$this->games = array();
		if(is_array($arrPackage) && count($arrPackage)) {
			if($arrPackage['games'] != '') {
				$this->objJson = json_decode($arrPackage['games']);
				if(isset($this->objJson->games->l)) {
					foreach($this->objJson->games->l as $arrLang) {
						foreach($arrLang->u as $arrUnit) {
							foreach($arrUnit->s as $arrSection) {
								foreach($arrSection->g as $game_uid) {
									$this->json_games[] =$arrLang->uid.'_'.$arrUnit->uid.'_'.$arrSection->uid.'_'.$game_uid;
									$this->games[$arrLang->uid][$arrUnit->uid][$arrSection->uid][] = $game_uid;
								}
							}
						}
					}
				}
				unset($this->objJson);
				unset($arrPackage);
			}
		}
	}
}

?>