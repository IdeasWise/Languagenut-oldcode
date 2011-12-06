<?php

/**
 * printables.php
 */
class Printables extends Controller {

	public function __construct() {
		parent::__construct();
		if(!isset($_SESSION['trynow']) && !isset($_SESSION['user'])) {
			die('Access denied..');
		}
		$this->content();
	}

	protected function content() {

		header('Content-Type: text/html; charset=utf-8');
		$language_uid = isset($_GET['language_id']) ? preg_replace('/[^\d]/', '', $_GET['language_id']) : '';
		$section_uid = isset($_GET['section_id']) ? preg_replace('/[^\d]/', '', $_GET['section_id']) : '';
		$support_language_uid = isset($_GET['support_language_id']) ? preg_replace('/[^\d]/', '', $_GET['support_language_id']) : '';

		$objCache = new cache();
		$objCache->cache_api_folder = "printables/";
		$cacheFileName = "content_" .$_GET['type']. $support_language_uid . $section_uid . $language_uid;
		$cacheData = "";
		if ($objCache->cacheExist($cacheFileName)) {
			header('Content-Type: application/pdf');
			echo $objCache->getCacheContent($cacheFileName);
		} else {

			$objSectionVocabulary = new sections_vocabulary();
			$objSectionVocabularyTranslations = new sections_vocabulary_translations();
			$objSection = new sections();
			$objSectionTranslations = new sections_translations();
			$objUnitTranslations = new units_translations();
			$objLanguage = new language($language_uid);
			$objLanguage->load();

			$language_name = $objLanguage->get_name();
			$objSupportLanguage = new language($support_language_uid);
			$objSupportLanguage->load();
			$support_language = $objSupportLanguage->get_name();

			if (!empty($language_name)) {
				$result = $objSectionVocabulary->getVocabTranslation($section_uid);
				if ($result) {
					$vocab = array();
					if (mysql_num_rows($result) > 0) {
						/**
						 * Fetch the section vocabulary
						 */
						while ($row = mysql_fetch_assoc($result)) {
							$newTranslation = $row['name'];
							$TmpName = $objSectionVocabularyTranslations->getSecvocabTranslationName($row['uid'], $support_language_uid);
							if (!empty($TmpName)) {
								$newTranslation = $TmpName;
							}
							$vocab[$row['uid']]['support'] = stripslashes(stripslashes(stripslashes($newTranslation)));
						}
						/**
						 * Find Translations
						 */
						$vocab = $objSectionVocabularyTranslations->getSecVocabTransArray($vocab, $language_uid, $language_name);
						$dataRow = $objSection->getSectionUnitandId($section_uid);

						if (count($dataRow) > 0) {
							$section_name = $dataRow['section_name'];
							$unit_name = $dataRow['unit_name'];
							$unit_uid = $dataRow['uid'];

							$TmpName = '';
							$TmpName = $objSectionTranslations->getSectionTranslationName($support_language_uid, $section_uid);
							if (!empty($TmpName)) {
								$section_name = $TmpName;
							}

							$TmpName = '';
							$TmpName = $objUnitTranslations->getUnitTranslationName($support_language_uid, $unit_uid);

							if (!empty($TmpName)) {
								$unit_name = $TmpName;
							}
						} else {
							$cacheData = mysql_error() . $query;
						}
					} else {
						$cacheData = 'no rows:' . $query;
					}
				} else {
					$cacheData = mysql_error() . $query;
				}
			}

			$type = isset($_GET['type']) ? $_GET['type'] : '';
			/*
			 * ksort is used to set ascending order on $vocab array 
			 */
			if (is_array($vocab))
				ksort($vocab);

			switch ($type) {
				case 'vocab':
					if (count($vocab) > 0) {
						/**
						 * Build the PDF
						 */
						$vocabPDF = new vocabPdf();

						//Data loading
						$vocabPDF->printVocab(
								array(
									'language' => $language_name,
									'section' => $section_name,
									'unit' => $unit_name,
									'support' => $support_language,
									'vocab' => $vocab
								)
						);
						$cacheData = $vocabPDF->Output('', 'S');
						$objCache->createOrReplace($cacheFileName, $cacheData);
						header('Content-Type: application/pdf');
						echo $cacheData;
					}
					break;
				case 'flashcards':
					if (count($vocab) > 0) {
						/**
						 * Build the PDF
						 */
						$vocabPDF = new vocabPdf('L', 'mm', 'A4');

						//Data loading
						$vocabPDF->printFlashCards(
								array(
									'language' => $language_name,
									'section' => $section_name,
									'unit' => $unit_name,
									'support' => $support_language,
									'vocab' => $vocab
								)
						);
						
						$cacheData = $vocabPDF->Output('', 'S');
						$objCache->createOrReplace($cacheFileName, $cacheData);
						header('Content-Type: application/pdf');
						echo $cacheData;
					}
					break;
			}
		}
	}

}

?>