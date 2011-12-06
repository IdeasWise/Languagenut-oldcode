<?php

/**
 * loader_support.php
 */
class LoaderSupport extends Controller {

	
	public function __construct() {
		parent::__construct();
		$this->content();
	}

	protected function content() {
		$objCache = new cache();
		$objCache->cache_api_folder = "loader_support/";
		
		$support_language_uid = 14;

		$objYears = new years(); // initializing year Object
		$arrYears = $objYears->getYearArray(); // returns year id and name in array
		$objLanguage = new language(); // initializing language Object


		if (isset($_GET['support_language_uid']) && (int) $_GET['support_language_uid'] > 0) {
			if ($objLanguage->exists($_GET['support_language_uid']) === true) {
				$support_language_uid = $_GET['support_language_uid'];
			}
		}
		$cacheFileName = "content_" . $support_language_uid;
		if(isset($_REQUEST["language_uid"])) {
			$cacheFileName = $cacheFileName . $_REQUEST["language_uid"];
		}
		if ($objCache->cacheExist($cacheFileName)) {
			echo $objCache->getCacheContent($cacheFileName);
		} else {
			$arrLanguages = $objLanguage->getLanguagesList($support_language_uid); // returns language id and array

			$objFlashTranslations = new flash_translations_locales();
			$copy = $objFlashTranslations->getListByLanguageUid($support_language_uid);

			$objGame = new game();
			$arrGames = $objGame->getListBySupportName($support_language_uid);



			$cacheData = "";
			$cacheData.= '<?xml version="1.0" encoding="utf-8" ?>';
			$cacheData.='<data>';
			$cacheData.='<years>';
			if (count($arrYears) > 0) {
				foreach ($arrYears as $id => $name) {
					$cacheData.= '<year id="' . $id . '" title="' . $name . '" />';
				}
			}

			$arrNLlanguage = array(14, 3, 4, 6, 5, 10, 7);

			$cacheData.='</years>';
			$cacheData.='<support_language_uid>' . $support_language_uid . '</support_language_uid>';
			$cacheData.='<languages>';
			if (count($arrLanguages) > 0) {
				foreach ($arrLanguages as $uid => $array) {
					if ($support_language_uid == '21') {
						if (in_array($uid, $arrNLlanguage)) {
							$use = 'yes';
						} else {
							$use = 'no';
						}
						$name = $array['name'];
						$directory = $array['directory'];

						$cacheData.='<language ';
						$cacheData.='id="' . $uid . '" ';
						$cacheData.='title="' . $name . '" ';
						$cacheData.='directory="' . $directory . '" ';
						$cacheData.='use="' . $use . '" ';
						$cacheData.= ( strlen($array['runtime']) > 0 ? 'runtime="' . $array['runtime'] . '" ' : '') . ' ';
						$cacheData.= ( strlen($array['audiodirectory']) > 0 ? 'audiodirectory="' . $array['audiodirectory'] . '"' : '');
						$cacheData.=' />';
					} else {
						$name = $array['name'];
						$directory = $array['directory'];
						$use = ($array['available'] == 1 ? 'yes' : 'no');
						$cacheData.='<language ';
						$cacheData.='id="' . $uid . '" ';
						$cacheData.='title="' . $name . '" ';
						$cacheData.='directory="' . $directory . '" ';
						$cacheData.='use="' . $use . '" ';
						$cacheData.= ( strlen($array['runtime']) > 0 ? 'runtime="' . $array['runtime'] . '" ' : '') . ' ';
						$cacheData.= ( strlen($array['audiodirectory']) > 0 ? 'audiodirectory="' . $array['audiodirectory'] . '"' : '');
						$cacheData.=' />';
					}
				}
			}
			$cacheData.='</languages>';
			$cacheData.='<copy>';
			foreach ($copy as $uid => $array) {
				$cacheData.='<' . $array['tag_name'] . '>' . $array['translation_text'] . '</' . $array['tag_name'] . '>';
			}
			$cacheData.='</copy>';
			$cacheData.='<games>';

			if ($arrGames && is_array($arrGames) && count($arrGames) > 0) {
				foreach ($arrGames as $gameUid => $gameArray) {
					$cacheData.='<game uid="' . $gameUid . '" name="' . $gameArray['name'] . '" key="' . $gameArray['tagname'] . '" />';
				}
			}

			$cacheData.='</games>';
			$cacheData.='</data>';
			$objCache->createOrReplace($cacheFileName,$cacheData);
			echo $cacheData;
		}
	}

}

?>