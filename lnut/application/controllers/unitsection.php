<?php

/**
 * terms.php
 */
class Unitsection extends Controller {

	public function __construct() {
		parent::__construct();

		$this->content();
	}

	protected function content() {
/*
		if (session_is_registered('trynow') == false && session_is_registered('user') == false) {
			if (!isset($_POST['swfBuild'])) {
				die('Access denied..');
			}
		}
*/

		if(!isset($_SESSION['trynow']) && !isset($_SESSION['user']) && !isset($_POST['swfBuild'])) {
			die('Access denied..');
		}


		$objCache = new cache();
		$objCache->cache_api_folder = "unitsection/";


		$year_id = '';
		$language_id = '';
		$language_support_id = '';
		if(isset($_REQUEST['year_id']) && isset($_REQUEST['support_language_id']) && isset($_REQUEST['language_id'])) {
			$_POST['year_id'] 				= $_REQUEST['year_id'];
			$_POST['support_language_id'] 	= $_REQUEST['support_language_id'];
			$_POST['language_id'] 			= $_REQUEST['language_id'];
		}
		if (!isset($_POST['swfBuild'])) {
			$year_id = (isset($_POST['year_id']) && strlen($_POST['year_id']) > 0) ? preg_replace('/[^\d]/', '', (int) $_POST['year_id']) : '';
			$language_id = (isset($_POST['language_id']) && strlen($_POST['language_id']) > 0) ? preg_replace('/[^\d]/', '', (int) $_POST['language_id']) : '';
			$language_support_id = (isset($_POST['support_language_id']) && strlen($_POST['support_language_id']) > 0) ? preg_replace('/[^\d]/', '', (int) $_POST['support_language_id']) : '';
		} else {
			$year_id = (isset($_POST['year_id']) && strlen($_POST['year_id']) > 0) ? preg_replace('/[^\d]/', '', (int) $_POST['year_id']) : '';
			$language_id = (isset($_POST['language_id']) && strlen($_POST['language_id']) > 0) ? preg_replace('/[^\d]/', '', (int) $_POST['language_id']) : '';
			$language_support_id = (isset($_POST['support_language_id']) && strlen($_POST['support_language_id']) > 0) ? preg_replace('/[^\d]/', '', (int) $_POST['support_language_id']) : '';
		}

		$units = array();
		$sections = array();

		$cacheFileName = "content_" . $year_id . $language_id . $language_support_id;
		$cacheData = "";
		if ($objCache->cacheExist($cacheFileName) && 2==5) {
			echo $objCache->getCacheContent($cacheFileName);
		} else {


			if ($year_id != '' && $language_id != '') {

				/**
				 * Get the locale from the language
				 * Check for the file: /stories/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_story[section_id]/[locale]_u[unit_id]_s[section_id]_story.xml
				 * Check for the file: /karaoke/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[section_id]_karaoke.xml
				 */
				$langObject = new language(); // initializing language Object
				$locale = $langObject->getPrefix($language_id); // returns language prefix

				//$path = '/home/language/public_html';
				$path = config::get('root');
				//	$story	= '/swf/story/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_story0[section_id]/[locale]_u[unit_id]_s[section_id]_story.xml';
				$story = '/swf/story/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_story.xml';
				$karaoke = '/swf/karaoke/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[story_id]_karaoke.xml';

				$units = array();
				$objUnit = new units(); // initializing units Object
				$units = $objUnit->getUnitTransArray($language_id, $year_id, $locale); // return unit translation array

				if (count($units) > 0) {
					$sections = array();
					$objSection = new sections(); // initializing sections Object

					$unit_ids = array_keys($units);
					$sections = $objSection->getSectionTranslations($language_id, $unit_ids); // return section translation array

					$objGameScore = new gamescore();
					foreach ($sections as $section_id => $section_data) {
						$games[$section_id] = $objGameScore->getScoresBySectionAndUser($section_id, (isset($_SESSION['user']['uid']) ? $_SESSION['user']['uid'] : 24), $language_id,$language_support_id);
					}
				}

				$query = "SELECT ";
				$query.= "(SELECT `text` FROM `certificate_messages_translations` WHERE `message_uid`=1 AND `locale`='$locale' LIMIT 1) AS `goldmedal`, ";
				$query.= "(SELECT `text` FROM `certificate_messages_translations` WHERE `message_uid`=2 AND `locale`='$locale' LIMIT 1) AS `silvermedal`, ";
				$query.= "(SELECT `text` FROM `certificate_messages_translations` WHERE `message_uid`=3 AND `locale`='$locale' LIMIT 1) AS `bronzemedal` ";

				$result = database::query($query);

				$goldMedal = '';
				$silverMedal = '';
				$bronzeMedal = '';

				if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_assoc($result)) {
						$goldMedal = stripslashes($row['goldmedal']);
						$silverMedal = stripslashes($row['silvermedal']);
						$bronzeMedal = stripslashes($row['bronzemedal']);
					}
				}
			}

			$cacheData.= '<?xml version="1.0" encoding="utf-8" ?>';
			$cacheData.='<units>';
			if (count($units) > 0) {
				$utObject = new units_translations(); // initializing units_translations Object
				$stObject = new sections_translations(); // initializing sections_translations Object
				foreach ($units as $unit_id => $arrayOuter) {
					/**
					 * Look up unit id in language_support_id, default to language_id if not present
					 */
					if ($language_support_id > 0) {
						$name = $utObject->getUnitTranslationName($language_support_id, $unit_id);
						if (!empty($name))
							$arrayOuter['name'] = $name;
					}

					if($language_id > 0 && $language_id==75) {
						$cacheData.='<unit title="' . str_replace('\\','',$arrayOuter['name']) . '" uid="' . $unit_id . '" story="1" song="1">';
					} else {
						$cacheData.='<unit title="' . str_replace('\\','',$arrayOuter['name']) . '" uid="' . $unit_id . '" story="' . $arrayOuter['story'] . '" song="' . $arrayOuter['karaoke'] . '">';
					}
					if (count($sections) > 0) {
						foreach ($sections as $section_id => $arrayInner) {
							if ($arrayInner['unit_id'] == $unit_id) {
								/**
								 * Look up section id in language_support_id, default to language_id if not present
								 */
								if ($language_support_id > 0) {

									$name = $stObject->getSectionTranslationName($language_support_id, $section_id);
									if (!empty($name)) {
										$arrayInner['name'] = $name;
									}
								}
								$cacheData.='<section id="' . $section_id . '" title="' . str_replace('\\','',$arrayInner['name']) . '">';

								if (count($games[$section_id]) > 0) {
									foreach ($games[$section_id] as $game_id => $data) {
										$phrase = '';
										$medalNum = '';
										if ($data >= 85) {
											// get gold phrase
											$phrase = $goldMedal;
											$medalNum = 3;
										} else if ($data >= 70) {
											// get silver phrase
											$phrase = $silverMedal;
											$medalNum = 2;
										} else if ($data >= 50) {
											// get bronze phrase
											$phrase = $bronzeMedal;
											$medalNum = 1;
										} else {
											// no phrase
											$phrase = '';
											$medalNum = 0;
										}
										$cacheData.='<gs id="' . $game_id . '" score="' . $data . '" medal="' . $medalNum . '" medalText="' . stripslashes(stripslashes($phrase)) . '" />';
									}
								}

								$cacheData.='</section>';
							}
						}
					}
					$cacheData.='</unit>';
				}
			}
			$cacheData.='</units>';

			#$objCache->createOrReplace($cacheFileName,$cacheData);
			echo $cacheData;
		}
	}

}

?>