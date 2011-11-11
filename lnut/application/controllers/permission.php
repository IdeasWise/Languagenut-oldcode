<?php

/**
 * permission.php
 */

class Permission extends Controller {

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
		if(isset($arrPaths[1]) && $arrPaths[1]=='json') {
			$this->doJson();
		} else {
			$this->doXml();
		}

		
	}

	protected function doJson () {
		$arrJson = array();
		$arrJson['data'] = array();
		
		//header ("Content-Type:text/xml");

		$objPackage = new reseller_sub_package(1);
		if ($objPackage->get_valid()) {
			$objPackage->load();
			$arrGameKeys = array();
			$arrGameKeys = game::getGameKeys();
			$this->ParsePackage($objPackage);
			
			$arrJson['data']['get_support_language_uid'] = $objPackage->get_support_language_uid();

			foreach($this->json_languages as $lang_index => $language_uid) {
	
				$arrJson['data']['language'][$lang_index]['language_uid'] = $language_uid;
				//echo '<language id="'.$language_uid.'">';
				if(isset($this->games[$language_uid]) && is_array($this->games[$language_uid])) {
					foreach($this->games[$language_uid] as $unit_uid => $arrUnit) {

						$arrJson['data']['language'][$lang_index]['units'][$unit_uid]['unit_uid'] = $unit_uid;
						//echo '<unit id="'.$unit_uid.'">';
						if(isset($arrUnit) && is_array($arrUnit)) {
							foreach($arrUnit as $section_uid => $arrSection) {
								$arrJson['data']['language'][$lang_index]['units'][$unit_uid]['sections'][$section_uid]['section_uid'] = $section_uid;
								//echo '<section id="'.$section_uid.'">';
									if(isset($arrSection) && is_array($arrSection) && count($arrSection)) {
										//echo '<games>';
											foreach($arrSection as $game_uid) {
												if(isset($arrGameKeys[$game_uid])) {
													$arrJson['data']['language'][$lang_index]['units'][$unit_uid]['sections'][$section_uid]['games'][] = $arrGameKeys[$game_uid];
													//echo '<game id="'.$arrGameKeys[$game_uid].'">';
												} else {
													//echo '<game id="'.$game_uid.'">';
												}
												//echo '</game>';
											}
										//echo '</games>';
									}
								//echo '</section>';
							}
						}
						//echo '</unit>';
					}
				}
				//echo '</language>';
			}

		}

		//echo '</data>';
		/*
		echo '<pre>';
		print_r($arrTest);
		echo '</pre>';
		*/
		echo json_encode($arrJson);
	}

	protected function doXml () {
		header ("Content-Type:text/xml");
		echo '<?xml version="1.0" encoding="utf-8" ?>';
		echo '<data>';
		$objPackage = new reseller_sub_package(1);
		if ($objPackage->get_valid()) {
			$objPackage->load();
			$arrGameKeys = array();
			$arrGameKeys = game::getGameKeys();
			$this->ParsePackage($objPackage);
			echo '<support_language_uid>'.$objPackage->get_support_language_uid().'</support_language_uid>';
			foreach($this->json_languages as $lang_index => $language_uid) {
				echo '<language id="'.$language_uid.'">';
				if(isset($this->games[$language_uid]) && is_array($this->games[$language_uid])) {
					foreach($this->games[$language_uid] as $unit_uid => $arrUnit) {
						echo '<unit id="'.$unit_uid.'">';
						if(isset($arrUnit) && is_array($arrUnit)) {
							foreach($arrUnit as $section_uid => $arrSection) {
								echo '<section id="'.$section_uid.'">';
									if(isset($arrSection) && is_array($arrSection) && count($arrSection)) {
										echo '<games>';
											foreach($arrSection as $game_uid) {
												if(isset($arrGameKeys[$game_uid])) {
													echo '<game id="'.$arrGameKeys[$game_uid].'">';
												} else {
													echo '<game id="'.$game_uid.'">';
												}
												echo '</game>';
											}
										echo '</games>';
									}
								echo '</section>';
							}
						}
						echo '</unit>';
					}
				}
				echo '</language>';
			}

		}
		echo '</data>';
	}

	private function ParsePackage($objPackage=null) {
		if($objPackage!=null) {
			/*
			if($objPackage->get_sections() != '') {
				$this->objJson = json_decode($objPackage->get_sections());

				if(isset($this->objJson->sections)) {
					foreach($this->objJson->sections as $data) {
						$this->json_sections[] = $data->section_pair;
						$this->json_years[$data->learnable_language_uid][] = $data->year_uid;
						$this->json_units[$data->learnable_language_uid][] = $data->unit_uid;
						$this->json_section_uids[$data->learnable_language_uid][$data->unit_uid][] = $data->section_uid;
					}
				}
			}
			*/

			if($objPackage->get_learnable_language() != '') {
				$this->objJson = json_decode($objPackage->get_learnable_language());
				if(isset($this->objJson->language_uids) && is_array($this->objJson->language_uids)) {
					$this->json_languages = $this->objJson->language_uids;
				}
			}

			if($objPackage->get_games() != '') {
				$this->objJson = json_decode($objPackage->get_games());
				//echo '<pre>';
				//print_r($this->objJson);
				if(isset($this->objJson->games)) {
					foreach($this->objJson->games as $data) {						
						$this->games[$data->learnable_language_uid][$data->unit_uid][$data->section_uid][] = $data->game_uid;
					}
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