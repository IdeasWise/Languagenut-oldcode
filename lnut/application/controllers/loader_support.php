<?php

/**
 * loader_support.php
 */

class LoaderSupport extends Controller {

	public function __construct () {
		parent::__construct();

		$this->content();
	}

	protected function content () {

		$support_language_uid = 14;
		$arrPaths = config::get('paths');
		$yaerObject = new years(); // initializing year Object
		$years = $yaerObject->getYearArray(); // returns year id and name in array

		$langObject = new language(); // initializing language Object

		$arrAvailableLanguages = array();
		if(isset($_GET['support_language_uid']) && (int)$_GET['support_language_uid'] > 0) {
			if($langObject->exists($_GET['support_language_uid'])===true) {
				$support_language_uid = $_GET['support_language_uid'];
				$objLanguage = new language($support_language_uid);
				$objLanguage->load();
				if(in_array('trynow',$arrPaths)) {
					if($objLanguage->get_trynow_available_language_uids() !='') {
						$arrAvailableLanguages = explode(',',$objLanguage->get_trynow_available_language_uids());
					}
				} else {
					if($objLanguage->get_live_available_language_uids() !='') {
						$arrAvailableLanguages = explode(',',$objLanguage->get_live_available_language_uids());
					}
				}
				if(count($arrAvailableLanguages) && in_array($support_language_uid,array(114,109))){
					$arrAvailableLanguages[]=16;
				}
			}
		}

		$languages = $langObject->getLanguagesList($support_language_uid); // returns language id and array

		$objFlashTranslations = new flash_translations_locales();
		$copy = $objFlashTranslations->getListByLanguageUid($support_language_uid);

		$objGame = new game();
		$arrGames = $objGame->getListBySupportName($support_language_uid);

		echo '<?xml version="1.0" encoding="utf-8" ?>';
		echo '<data>';
		if($support_language_uid == '21') { // $support_language_uid==21 means $locale=='nl'
			echo '<show_subscribe>no</show_subscribe>';
		}
		echo '<years>';
		if(count($years) > 0) {
			foreach($years as $id=>$name) {
				echo '<year id="'.$id.'" title="'.stripslashes(stripslashes($name)).'" />';
			}
		}

		$arrNLlanguage = array(14,3,4,6,5,10,7);
		$arrCLlanguage = array(14,4);

		echo '</years>';
		echo '<support_language_uid>'.$support_language_uid.'</support_language_uid>';
		echo '<languages>';
		if(count($languages) > 0) {
			foreach($languages as $uid=>$array) {
				if(is_array($arrAvailableLanguages) && count($arrAvailableLanguages) ) {
					if(in_array($uid,$arrAvailableLanguages)) {
						$use = 'yes';
					} else {
						$use = 'no';
					}
					$name		= stripslashes(stripslashes($array['name']));
					$directory	= $array['directory'];
					
					echo '<language ';
					echo 'id="'.$uid.'" ';
					echo 'title="'.$name.'" ';
					echo 'directory="'.$directory.'" ';
					echo 'use="'.$use.'" ';
					echo (strlen($array['runtime'])>0?'runtime="'.$array['runtime'].'" ':'').' ';
					echo (strlen($array['audiodirectory'])>0?'audiodirectory="'.$array['audiodirectory'].'"':'');
					echo ' />';

				} else {
					$name		= $array['name'];
					$directory	= $array['directory'];
					$use		= ($array['available']==1 ? 'yes' : 'no');
					if(in_array($support_language_uid,array(114,109)) && $uid==16){
						$use='yes';
					}
					echo '<language ';
					echo 'id="'.$uid.'" ';
					echo 'title="'.$name.'" ';
					echo 'directory="'.$directory.'" ';
					echo 'use="'.$use.'" ';
					echo (strlen($array['runtime'])>0?'runtime="'.$array['runtime'].'" ':'').' ';
					echo (strlen($array['audiodirectory'])>0?'audiodirectory="'.$array['audiodirectory'].'"':'');
					echo ' />';
				}
				
			}
		}
		echo '</languages>';
		echo '<copy>';
		foreach($copy as $uid=>$array) {
			echo '<'.$array['tag_name'].'>'.stripslashes(stripslashes($array['translation_text'])).'</'.$array['tag_name'].'>';
		}
		echo '</copy>';
		echo '<games>';

		if($arrGames && is_array($arrGames) && count($arrGames) > 0) {
			foreach($arrGames as $gameUid => $gameArray) {
				echo '<game uid="'.$gameUid.'" name="'.stripslashes(stripslashes($gameArray['name'])).'" key="'.$gameArray['tagname'].'" />';
			}
		}

		echo '</games>';
		echo '</data>';
	}

}

?>