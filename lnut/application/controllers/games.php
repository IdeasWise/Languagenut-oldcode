<?php

/**
 * terms.php
 */
class Games extends Controller {

	public function __construct() {
		parent::__construct();
		$this->content();
	}

	protected function content() {

		if(!isset($_SESSION['trynow']) && !isset($_SESSION['user']) && !isset($_POST['swfBuild'])) {
			die('Access denied..');
		}

		$objCache = new cache();
		$objCache->cache_api_folder = "games/";
		$section_id="";
		$language_id="";
		$support_language_id="";
		if (!isset($_POST['swfBuild'])) {
			$section_id = isset($_POST['section_id']) ? preg_replace('/[^\d]/', '', $_POST['section_id']) : '';
			$language_id = isset($_POST['language_id']) ? preg_replace('/[^\d]/', '', $_POST['language_id']) : '';
			$support_language_id = isset($_POST['support_language_id']) ? preg_replace('/[^\d]/', '', $_POST['support_language_id']) : '14';
		} else {
			$section_id = isset($_POST['section_id']) ? preg_replace('/[^\d]/', '', $_POST['section_id']) : '';
			$language_id = isset($_POST['language_id']) ? preg_replace('/[^\d]/', '', $_POST['language_id']) : '';
			$support_language_id = isset($_POST['support_language_id']) ? preg_replace('/[^\d]/', '', $_POST['support_language_id']) : '14';
		}

		$cacheFileName = "content_" . $support_language_id . $language_id . $section_id;
		$cacheData = "";
		if (1==0 && $objCache->cacheExist($cacheFileName)) {
			echo $objCache->getCacheContent($cacheFileName);
		} else {


			$arrSections = array();
			$arrTranslations = array();

			if ($section_id != '' && $language_id != '') {
				/**
				 * Get Vocabulary for the given section
				 */
				$arrTerms = array();
				$arrTermId = array();
				$objSectionVocabulary = new sections_vocabulary(); // initializing sections_vocabulary Object
				$result = $objSectionVocabulary->getIdNameArray($section_id);
				$arrTerms = $result[0];
				$arrTermId = $result[1];
				$termsOriginal = $arrTerms;

				if ($support_language_id != 14) {
					$objSectionsVocabularyTranslations = new sections_vocabulary_translations(); // initializing sections_vocabulary_translations Object
					$result = $objSectionsVocabularyTranslations->getVocabTransArray($arrTermId, $support_language_id);
					if (count($result)) {
						$arrTerms = $result;
					} else {
						//echo 'no rows<br />';
					}
				}

				if (count($arrTerms) > 0) {
					/**
					 * Get all the translation for those terms in the given language
					 */
					$objSectionsVocabularyTranslations = new sections_vocabulary_translations(); // initializing sections_vocabulary_translations Object
					$result = $objSectionsVocabularyTranslations->getVocabTransResult($arrTermId, $language_id);

					if ($result) {
						if (mysql_num_rows($result) > 0) {
							while ($row = mysql_fetch_assoc($result)) {
								if (strlen($row['name']) > 0) {
									$arrTranslations[$row['uid']] = array('term_id' => $row['term_uid'], 'term' => stripslashes($row['name']));
								}
							}
						}

						if (count($arrTranslations) < 1) {
							foreach ($termsOriginal as $id => $term) {
								$arrTranslations[$id] = array('term_id' => $id, 'term' => $term['term']);
							}
						}


						$cacheData.= '<?xml version="1.0" encoding="utf-8" ?>';
						$cacheData.= '<gamedata>';
						/**
						 * Go through all the terms and generate a node for each
						 * Go through each of the translations for each node and get the right translation for the term
						 */
						if (count($arrTerms) > 0 && count($arrTranslations) > 0) {
							foreach ($arrTerms as $term_id => $term_array) {
								$cacheData.= '<data title="' . str_replace('\\', '', $term_array['term']) . '" term_id="' . $term_id . '" ';
								$used = false;
								foreach ($arrTranslations as $translation_id => $translation_array) {
									if ($translation_array['term_id'] == $term_id && !$used) {
										$used = true;
										$cacheData.= ' translation="' . str_replace('\\', '', $translation_array['term']) . '"';
									}
								}
								$cacheData.= ' />';
							}
						}
						$cacheData.= '</gamedata>';

					} else {
						$cacheData = mysql_error();
					}
				} else {
					$cacheData = '<?xml version="1.0" encoding="utf-8" ?><gamedata><item>No Data</item></gamedata>';
				}
			} else {
				$cacheData = '<?xml version="1.0" encoding="utf-8" ?><gamedata><item>Bad Request [2]'.print_r($_POST,true).'</item></gamedata>';
			}

			#$objCache->createOrReplace($cacheFileName, $cacheData);
			echo $cacheData;
		}
	}

}

?>