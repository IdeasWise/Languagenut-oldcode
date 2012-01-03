<?php

/**
 * webservice.php
 */

class WebService extends Controller {
	private $token = 'config';
	private $arrTokens = array(
		'config',
		'languageTranslations',
		'unitList',
		'unitSectionTitles',
		'sections',
		'languageLocalisation',
		'games',

		'article',
		'template',
		'articleTranslation',
		'templateTranslation',
		'articleTemplates',
		'articleCategories',
		'createArticle',
		'createTemplate',
		'loginStatus',
		'messages'
	);

	public function __construct () {
		parent::__construct();
		$paths = config::get('paths');

		if(!isset($_POST['swfBuild'])) {
			$_POST['swfBuild'] = 1;
		}
		if(isset($paths[1]) && in_array($paths[1], $this->arrTokens)) {
			$this->token = str_replace(array('user', '-'), array('', ''), $paths[1]);
		}

		if(in_array($this->token, $this->arrTokens)) {
			$method = 'get' . ucfirst($this->token);
			$this->$method();
		} else {
			$this->getConfig();
		}
	}

	public function getMessages() {
		$messages = array();
		if(count($_SESSION) > 0) {
			$lastping = strtotime(isset($_SESSION['lastping']) ? $_SESSION['lastping'] : time()-30);
			$_SESSION['lastping'] = time();
			$query = "SELECT `uid`,`message`,`flash_callback` FROM `notification` WHERE `to_uid`=".$_SESSION['user']['uid']." AND `notification_created` >='$lastping' AND `notification_sent`='0000-00-00 00:00:00' ORDER BY `notification_created` ASC";
			$result = database::query($query);
			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				while($row = mysql_fetch_assoc($result)) {
					$messages[$row['uid']] = array('message'=>stripslashes($row['message']),'callback'=>$row['flash_callback']);
				}
			}
		}
		if(count($messages) > 0) {
			$keys = array_keys($messages);
			$query = "UPDATE `notifications` SET `notification_sent`='".date('Y-m-d H:i:s')."' WHERE `uid` IN (".implode(',',$keys).")";
			$result = database::query($query);
		}
		$arrData = array();
		$arrData['messages'] = $messages;
		if(isset($_GET['self'])) {
			$arrData['me'] = $_SESSION;
		}
		echo json_encode($arrData);
	}

	protected function getLanguageLocalisation () {

		$language_id		= (isset($_REQUEST['language_id']) && strlen($_REQUEST['language_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['language_id']) : '';
		if($language_id != '') {
			if(language::exists($language_id)) {
				$data		= flash_translations_locales::getListByLanguageUid($language_id);
				$arrFlash	= array();
				foreach($data as $uid => $arrTranslation) {
					$arrFlash[] = array (
										'key'		=> $arrTranslation['tag_name'],
										'textual'	=> $arrTranslation['translation_text'],
										'audio'		=> false
										);
				}
				if(count($arrFlash)) {
					$arrJson = array(
									'language'		=> $language_id,
									'localisation'	=> $arrFlash
									);
					echo json_encode($arrJson);
				}
			}
		}
	}
	protected function getConfig() {

		$support_language_uid = 14;

		$langObject = new language(); // initializing language Object

		if(isset($_GET['support_language_uid']) && (int)$_GET['support_language_uid'] > 0) {
			if($langObject->exists($_GET['support_language_uid'])===true) {
				$support_language_uid = $_GET['support_language_uid'];
			}
		}
		// returns language id and array
		$arrLanguages	= $langObject->getLanguagesList($support_language_uid);
		$arrLang		= array();
		$i = 0;
		if(count($arrLanguages) > 0) {
			foreach($arrLanguages as $uid=>$array) {
				$arrLang[$i] = array (
					'id'				=> $uid,
					//'title'				=> $array['name'],
					'directory'			=> $array['directory'],
					'isLearnable'		=> $array['is_learnable'],
					'isSupport'			=> $array['is_support'],
					'runtime'			=> $array['runtime'],
					'audiodirectory'	=> $array['audiodirectory']
				);

				$i++;
			}
		}
		$arrJson = array(
			'languages'	=> $arrLang,
			'user'		=> array(
				'username'		=> (isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : 'mystream01'),
				'loginAccess'	=> (isset($_SESSION['user']) && isset($_SESSION['user']['user_type'][0])) ? $_SESSION['user']['user_type'][0] : 'guest',
				'show_by_packages_button'	=> $this->getPackagesAvailable(),
				'buy_packages_url'			=> $this->getPackagesBuyUrl()
			)
		);

		echo json_encode($arrJson);

	}

	protected function getLoginStatus() {
		$userName = 'mystream01';
		if(isset($_SESSION['user']['uid'])) {
			$objUser = new user($_SESSION['user']['uid']);
			if($objUser->get_valid()) {
				$objUser->load();

				$userName = $objUser->get_username_open();
				if($userName == 'mystream01' || $userName == '') {
					$userName = $objUser->get_email();
				}
			}
		}
		$arrJson = array(
			'username'					=> $userName,
			'loginAccess'				=> (isset($_SESSION['user']) && isset($_SESSION['user']['user_type'][0])) ? $_SESSION['user']['user_type'][0] : 'guest',
			'show_by_packages_button'	=> $this->getPackagesAvailable(),
			'buy_packages_url'			=> $this->getPackagesBuyUrl()
		);

		echo json_encode($arrJson);

	}

	protected function getPackagesAvailable() {
		/**
		 * Look up their uid
		 * If they are a school, get their school subscription, get their subscription products, get their ids
		 * Look up the available school products to purchase for their locale_products and fetch the ids of the ones they don't have
		 * If there are any they don't have, return '1', else '0'
		 *
		 * If they are a home user, get their home user subscription, get their subscription products, get their ids
		 * Look up the available home products to purchase for their locale_products and fetch the ids of the ones they don't have
		 * If there are any they don't have, return '1', else '0'
		 */
		$user_uid = (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) ? $_SESSION['user']['uid'] : 0;

		if($user_uid != 0) {
			if($_SESSION['user']['user_type']=='school') {
				return $this->getPackagesAvailableToSchoolForUser($user_uid);
			} else if($_SESSION['user']['user_type']=='homeuser') {
				return $this->getPackagesAvailableToHomeuserForUser($user_uid);
			}
		} else {
			return 0;
		}
	}

	protected function getPackagesAvailableToSchoolForUser($user_uid='') {
		$query = "SELECT `uid` FROM `subscriptions` WHERE `user_uid`='$user_uid' AND `date_paid` <= '$today' AND `expires_dts` >= '$today'";
		$result = database::query($query);

		$response = 0;

		if($result && mysql_error()=='' && mysql_fetch_assoc($result) > 0) {
			$arrSubs = array();

			while($row = mysql_fetch_assoc($result)) {
				$arrSubs[] = $row['uid'];
			}

			$query = "SELECT `product_uid` FROM `subscriptions_products` WHERE `subscription_uid` IN (".implode(',',$arrSubs).")";
			$result = database::query($query);

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				$arrPurchasedProductUids = array();
				while($row = mysql_fetch_assoc($result)) {
					$arrPurchasedProductUids[] = $row['product_uid'];
				}
				if(count($arrPurchasedProductUids) > 0) {
					$arrProductUids = $this->getProductsByLocale($_SESSION['user']['prefix']);
					$newProductUids = array_diff($arrProductUids,$arrPurchasedProductUids);

					if(count($newProductUids) > 0) {
						$response = 1;
					}
				}
			}
		}

		return $response;
	}

	protected function getPackagesAvailableToHomeuserForUser($user_uid='') {
		return 0;
	}

	protected function getProductsByLocale($locale='') {

		$arrLocaleProducts = array();

		if($locale != '') {
			$query= "SELECT `uid` FROM `product_locale` WHERE `language_uid` = (SELECT `uid` FROM `language` WHERE `prefix`='$locale' LIMIT 1)";
			$result = database::query($query);

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				while($row = mysql_fetch_assoc($result)) {
					$arrLocaleProducts[] = $row['uid'];
				}
			}
		}

		return $arrLocaleProducts;
	}

	protected function getPackagesBuyUrl() {
		return 'http://ell.languagenut.com/'.((isset($_SESSION['user']) && isset($_SESSION['user']['prefix'])) ? $_SESSION['user']['prefix'] : 'en').'/subscribe/purchase/';
	}

	protected function getLanguageTranslations() {

		$support_language_uid = 14;

		$langObject = new language(); // initializing language Object

		if(isset($_GET['support_language_uid']) && (int)$_GET['support_language_uid'] > 0) {
			if($langObject->exists($_GET['support_language_uid'])===true) {
				$support_language_uid = $_GET['support_language_uid'];
			}
		}
		// returns language id and array
		$arrLanguages	= $langObject->getLanguagesList($support_language_uid);
		$arrLang		= array();
		$i = 0;
		if(count($arrLanguages) > 0) {
			foreach($arrLanguages as $uid=>$array) {
				$arrLang[$i] = array (
					'id'				=> $uid,
					'language'			=> $support_language_uid,
					'title'				=> stripslashes(str_replace('\\','',$array['name']))
				);

				$i++;
			}
		}
		echo json_encode($arrLang);

	}
	protected function getUnitList() {
		if(isset($_SESSION['trynow']) == false && isset($_SESSION['user']) == false) {
			if(!isset($_POST['swfBuild'])){
				die('Access denied..');
			}
		}

		$year_id			= null;
		$language_id		= '';
		$language_support_id= '';
		$unit_uid			= '';

		if(!isset($_POST['swfBuild'])) {
			$year_id			= (isset($_REQUEST['year_id']) && strlen($_REQUEST['year_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['year_id']) : null;
			$unit_uid			= (isset($_REQUEST['unit_uid']) && strlen($_REQUEST['unit_uid']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['unit_uid']) : '';
			$language_id		= (isset($_REQUEST['language_id']) && strlen($_REQUEST['language_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['language_id']) : '';
			$language_support_id= (isset($_REQUEST['support_language_id']) && strlen($_REQUEST['support_language_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['support_language_id']) : '';
		} else {
			$year_id			= (isset($_REQUEST['year_id']) && strlen($_REQUEST['year_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['year_id']) : null;
			$unit_uid			= (isset($_REQUEST['unit_uid']) && strlen($_REQUEST['unit_uid']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['unit_uid']) : '';
			$language_id		= (isset($_REQUEST['language_id']) && strlen($_REQUEST['language_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['language_id']) : '';
			$language_support_id= (isset($_REQUEST['support_language_id']) && strlen($_REQUEST['support_language_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['support_language_id']) : '';
		}
		if(($year_id == null || $year_id != '') && $language_id != '') {
			$langObject = new language(); // initializing language Object
			$locale = $langObject->getPrefix($language_id); // returns language prefix

			$units	= array ();
			$unitObject = new units(); // initializing units Object
			$units = $unitObject->getUnitTransArray($language_id, $year_id, $locale); // return unit translation array
			$arrUnit= array ();
			if(count($units) > 0) {
				$utObject = new units_translations(); // initializing units_translations Object

				foreach($units as $unit_id=>$arrayOuter) {
					/**
					 * Look up unit id in language_support_id, default to language_id if not present
					 */
					if($language_support_id > 0) {
						$name = $utObject->getUnitTranslationName($language_support_id, $unit_id);
						if(!empty($name)) {
							$arrayOuter['name']= $name;
						}
					}

					if($unit_uid == '' || $unit_uid == 0) {
						$arrUnit[] = array (
										'id'						=> $unit_id,
										'title'						=> stripslashes(str_replace('\\','',$arrayOuter['name'])),
										'colour'					=> ((isset($arrayOuter['colour']) && !empty($arrayOuter['colour']))?$arrayOuter['colour']:'0xFF0000'),
										'canAccessVocab'			=> false,
										'canAccessReadingWriting'	=> false,
										'canAccessSpeakingListening'=> false
									);
					} else if($unit_id == $unit_uid) {
						$arrUnit[] = array (
										'id'						=> $unit_id,
										'title'						=> stripslashes(str_replace('\\','',$arrayOuter['name'])),
										'colour'					=> ((isset($arrayOuter['colour']) && !empty($arrayOuter['colour']))?$arrayOuter['colour']:'0xFF0000'),
										'canAccessVocab'			=> false,
										'canAccessReadingWriting'	=> false,
										'canAccessSpeakingListening'=> false
									);
					}

				}
				$arrJson = array(
						'language'	=> $language_support_id,
						'units'		=> $arrUnit
						);

				echo json_encode($arrJson);
			}
		}
	}

	protected function getUnitSectionTitles () {
		if(isset($_SESSION['trynow']) == false && isset($_SESSION['user']) == false) {
			if(!isset($_POST['swfBuild'])){
				die('Access denied..');
			}
		}

		$unit_uid			= '';
		$language_id		= '';
		$language_support_id= '';

		if(!isset($_POST['swfBuild'])) {
			$unit_uid			= (isset($_REQUEST['unit_uid']) && strlen($_REQUEST['unit_uid']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['unit_uid']) : '';
			$language_id		= (isset($_REQUEST['language_id']) && strlen($_REQUEST['language_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['language_id']) : '';
			$language_support_id= (isset($_REQUEST['support_language_id']) && strlen($_REQUEST['support_language_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['support_language_id']) : '';
		} else {
			$unit_uid			= (isset($_REQUEST['unit_uid']) && strlen($_REQUEST['unit_uid']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['unit_uid']) : '';
			$language_id		= (isset($_REQUEST['language_id']) && strlen($_REQUEST['language_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['language_id']) : '';
			$language_support_id= (isset($_REQUEST['support_language_id']) && strlen($_REQUEST['support_language_id']) > 0) ? preg_replace('/[^\d]/','',(int)$_REQUEST['support_language_id']) : '';
		}


		if($unit_uid != '' && $language_id != '') {

			/**
			 * Get the locale from the language
			 * Check for the file: /stories/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_story[section_id]/[locale]_u[unit_id]_s[section_id]_story.xml
			 * Check for the file: /karaoke/[locale]/[locale]_u[unit_id]/[locale]_u[unit_id]_s[section_id]_karaoke.xml
			 */
			$langObject = new language(); // initializing language Object
			$locale = $langObject->getPrefix($language_id); // returns language prefix

			$units = array ($unit_uid);
			$sections = array ();

			if(count($units) > 0) {
				$sections		= array();
				$arrSection		= array();
				$sectionObject	= new sections(); // initializing sections Object

				$sections		= $sectionObject->getSectionTranslations($language_id, $units); // return section translation array
				$stObject		= new sections_translations(); // initializing sections_translations Object
				if(count($sections) > 0) {
					foreach($sections as $section_id=>$arrayInner) {
						/**
						 * Look up section id in language_support_id, default to language_id if not present
						 */
						if($language_support_id > 0) {
							$name = $stObject->getSectionTranslationName($language_support_id, $section_id);
							if(!empty($name)) {
								$arrayInner['name'] = $name;
							}
						}
						$arrSection[] = array (
										'sectionId'					=> $section_id,
										'title'						=> stripslashes(str_replace('\\','',$arrayInner['name']))
									);
					}
					$arrJson = array(
						'language'	=> $language_support_id,
						'unit'		=> $unit_uid,
						'sections'	=> $arrSection
						);
						echo json_encode($arrJson);
				}
			}
		}
	}
	protected function getSections($arrSections=array(1,2), $arrLanguages=array(14,3)) {
		header('Content-Type: text/html; charset=utf-8'); 
		$arrResult = array();
		$language_id = 3;
		$counter = 0;

		if(isset($_GET['from']) && is_numeric($_GET['from']) && $_GET['from'] > 0 && isset($_GET['to']) && is_numeric($_GET['to']) && $_GET['to'] > 0 && $_GET['to'] >=$_GET['from']  && isset($_GET['language_uid']) && trim($_GET['language_uid']) != '') {
			$arrLanguages = explode(',',$_GET['language_uid']);
			foreach($arrLanguages as $language_id) {
				if(!is_numeric($language_id)) {
					continue; // skip
				}
				$arrResult[$counter]['language'] = $language_id;
				for($section_id = $_GET['from']; $section_id <= $_GET['to']; $section_id++) {
					$arrResult[$counter]['sections'][] = $this->getSectionDetails($section_id,$language_id);
				}
				$counter++;
			}
		}
		else {
			foreach($arrLanguages as $language_id) {
				$arrResult[$counter]['language'] = $language_id;
				foreach($arrSections as $section_id) {
					$arrResult[$counter]['sections'][] = $this->getSectionDetails($section_id,$language_id);
				}
				$counter++;
			}
		}

		//echo '<pre>';
		//print_r($arrResult);
		//echo '</pre>';
		echo json_encode($arrResult); exit;
		//$json = json_decode( json_encode($arrResult));
		//print_r($json);
		//exit;
	}

	protected function getSectionDetails($section_id=null, $language_id=null) {

		$arrResult = array();
		$arrSection = array();
		if($section_id != null && $language_id != null) {
			$objSectionTranslations = new sections_translations();
			$section = $objSectionTranslations->getSectionTranslationName($language_id, $section_id);

			/**
			 * Get Vocabulary for the given section
			 */
			$terms		= array();
			$term_ids	= array ();
			$svObject	= new sections_vocabulary(); // initializing sections_vocabulary Object
			$result		= $svObject->getIdNameArray($section_id);
			$terms		= $result[0];
			$term_ids	= $result[1];

			$termsOriginal = $terms;


			$svtObject = new sections_vocabulary_translations(); // initializing sections_vocabulary_translations Object
			$result = $svtObject->getVocabTransArray($term_ids, $language_id);

			if(count($result)){
				$terms = $result;
			} else {
				//echo 'no rows<br />';
			}

			if(count($terms) > 0) {
				//echo '<gamedata>';
				/**
				* Go through all the terms and generate a node for each
				* Go through each of the translations for each node and get the right translation for the term
				*/
				$arrVocabs = array();
				if(count($terms) > 0) {
					foreach($terms as $term_id=>$term_array) {
						$arrVocabs[] = array(
										'termId'		=> $term_id,
										'title'			=> stripslashes(str_replace('\\','',$term_array['term']))
												);
						}
						$arrSection = array(
								'sectionId'	=> $section_id,
								'title'		=> stripslashes(str_replace('\\','',$section)),
								'vocab'		=> $arrVocabs
							);
						//$arrResult = array($arrSection);
						//echo '<pre>';
						//print_r($arrVocabs);
					}
				} else {
					echo mysql_error();
				}

			} else {
				echo 'Bad Request';
		}
		return $arrSection;
	}




	protected function getSectionsComplete($arrSections=array(1), $arrLanguages=array(14,3)) {

		if(!isset($_POST['swfBuild'])) {
			$section_id			= isset($_REQUEST['section_id']) ? preg_replace('/[^\d]/','',$_REQUEST['section_id']) : '';
			$language_id		= isset($_REQUEST['language_id']) ? preg_replace('/[^\d]/','',$_REQUEST['language_id']) : '';
			$support_language_id= isset($_REQUEST['support_language_id']) ? preg_replace('/[^\d]/','',$_REQUEST['support_language_id']) : '14';
		} else {
			$section_id			= isset($_REQUEST['section_id']) ? preg_replace('/[^\d]/','',$_REQUEST['section_id']) : '';
			$language_id		= isset($_REQUEST['language_id']) ? preg_replace('/[^\d]/','',$_REQUEST['language_id']) : '';
			$support_language_id= isset($_REQUEST['support_language_id']) ? preg_replace('/[^\d]/','',$_REQUEST['support_language_id']) : '14';
		}

		$sections = array ();
		$translations = array ();

		if($section_id != '' && $language_id != '') {
			/**
			 * Get Vocabulary for the given section
			 */
			$terms		= array();
			$term_ids	= array ();
			$svObject	= new sections_vocabulary(); // initializing sections_vocabulary Object
			$result		= $svObject->getIdNameArray($section_id);
			$terms		= $result[0];
			$term_ids	= $result[1];

			$termsOriginal = $terms;

			if($support_language_id != 14) {
				$svtObject = new sections_vocabulary_translations(); // initializing sections_vocabulary_translations Object
				$result = $svtObject->getVocabTransArray($term_ids, $support_language_id);
				if(count($result)){
					$terms = $result;
				} else {
					//echo 'no rows<br />';
				}
			}

			if(count($terms) > 0) {
				/**
				 * Get all the translation for those terms in the given language
				 */
				$svtObject = new sections_vocabulary_translations(); // initializing sections_vocabulary_translations Object
				$result = $svtObject->getVocabTransResult($term_ids, $language_id);

				if($result) {
					if(mysql_num_rows($result) > 0) {
						while($row = mysql_fetch_assoc($result)) {
							if(strlen($row['name']) > 0) {
								$translations[$row['uid']] = array('term_id'=>$row['term_uid'],'term'=>stripslashes(str_replace('\\','',$row['name'])));
							}
						}
					}

					if(count($translations) < 1) {
						foreach($termsOriginal as $id=>$term) {
							$translations[$id] = array('term_id'=>$id,'term'=>stripslashes(str_replace('\\','',$term['term'])));
						}
					}


					//echo '<gamedata>';
					/**
					* Go through all the terms and generate a node for each
					* Go through each of the translations for each node and get the right translation for the term
					*/
					$arrVocabs = array();
					if(count($terms) > 0 && count($translations) > 0) {
						foreach($terms as $term_id=>$term_array) {
							$translation	= '';
							$used			= false;
							foreach($translations as $translation_id=>$translation_array) {
								if($translation_array['term_id']==$term_id && !$used) {
									$used = true;
									$translation =stripslashes(str_replace('\\','',$translation_array['term']));
								}
							}
							$arrVocabs[] = array(
											'termId'		=> $term_id,
											'title'			=> stripslashes(str_replace('\\','',$term_array['term'])),
											'translation'	=> $translation
												);

						}
						echo '<pre>';
						print_r($arrVocabs);
					}

				} else {
					echo mysql_error();
				}
			} else {
				 echo 'No Data';
			}
		} else {
			echo 'Bad Request';
		}
	}

	protected function getArticle($article_uid=null) {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`title`, ";
		$query.="`article_category_uid`, ";
		$query.="`unit_uid`, ";
		$query.="`width`, ";
		$query.="`height` ";
		$query.="FROM ";
		$query.="`article` ";
		$query.="WHERE ";
		$query.="1=1 ";
		if(isset($_GET['article_uid']) && is_numeric($_GET['article_uid'])) {
			$query.="AND ";
			$query.="`uid`='".mysql_real_escape_string($_GET['article_uid'])."' ";
		}
		if($article_uid!=null && is_numeric($article_uid)) {
			$query.="AND ";
			$query.="`uid`='".mysql_real_escape_string($article_uid)."' ";
		}
		if(isset($_GET['unit_uid']) && is_numeric($_GET['unit_uid'])) {
			$query.="AND ";
			$query.="`unit_uid`='".mysql_real_escape_string($_GET['unit_uid'])."' ";
		}
		$query.="ORDER BY `uid`";
		$result = database::query($query);
		$arrArticle = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				$arrArticle[] = array(
					'uid'					=> $row['uid'],
					'title'					=> str_replace('\\','',$row['title']),
					'unit_uid'				=> $row['unit_uid'],
					'article_category_uid'	=> $row['article_category_uid'],
					'width'					=> $row['width'],
					'height'				=> $row['height'],
					'translations'			=> $this->getArticleTranslationArray($row['uid'])
				);
			}
		}
		$arrJson = array(
			'article'	=> $arrArticle
		);
		echo json_encode($arrJson);
	}

	protected function getArticleTranslationArray($article_uid=null) {
		$arrTranslations = array();
		if($article_uid!=null) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`language_uid`, ";
			$query.="`title`, ";
			$query.="`width`, ";
			$query.="`height` ";
			$query.="FROM ";
			$query.="`article_translations`";
			$query.="WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$arrTranslations = database::arrQuery($query);
		}
		return $arrTranslations;
	}

	protected function getTemplate($template_uid=null) {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`width`, ";
		$query.="`height` ";
		$query.="FROM ";
		$query.="`template` ";
		if(isset($_GET['template_uid']) && is_numeric($_GET['template_uid'])) {
			$query.="WHERE ";
			$query.="`uid`='".mysql_real_escape_string($_GET['template_uid'])."' ";
		}
		if($template_uid!=null && is_numeric($template_uid)) {
			$query.="WHERE ";
			$query.="`uid`='".mysql_real_escape_string($template_uid)."' ";
		}
		$query.="ORDER BY `uid`";
		$result = database::query($query);
		$arrTemplates = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				$arrTemplates[] = array(
					'uid'					=> $row['uid'],
					'name'					=> str_replace('\\','',$row['name']),
					'width'					=> $row['width'],
					'height'				=> $row['height'],
					'translations'			=> $this->getTemplateTranslationArray($row['uid'])
				);
			}
		}
		$arrJson = array(
			'template'	=> $arrTemplates
		);
		echo json_encode($arrJson);
	}

	protected function getTemplateTranslationArray($template_uid=null) {
		$arrTranslations = array();
		if($template_uid!=null) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`language_uid`, ";
			$query.="`name`, ";
			$query.="`width`, ";
			$query.="`height` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_uid`='".$template_uid."' ";
			$arrTranslations = database::arrQuery($query);
		}
		return $arrTranslations;
	}

	protected function getArticleTranslation() {
		$arrTranslations = array();
		if(isset($_GET['article_uid']) && is_numeric($_GET['article_uid']) && isset($_GET['language_uid']) && is_numeric($_GET['language_uid'])) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`language_uid`, ";
			$query.="`title`, ";
			$query.="`width`, ";
			$query.="`height` ";
			$query.="FROM ";
			$query.="`article_translations`";
			$query.="WHERE ";
			$query.="`article_uid`='".$_GET['article_uid']."' ";
			$query.="AND ";
			$query.="`language_uid`='".$_GET['language_uid']."' ";
			$arrTranslations = database::arrQuery($query);
		}
		$arrJson = array(
			'article'	=> $arrTranslations
		);
		echo json_encode($arrJson);
		//return $arrTranslations;
	}

	protected function getTemplateTranslation() {
		$arrTranslations = array();
		if(isset($_GET['template_uid']) && is_numeric($_GET['template_uid']) && isset($_GET['language_uid']) && is_numeric($_GET['language_uid'])) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`language_uid`, ";
			$query.="`name`, ";
			$query.="`width`, ";
			$query.="`height` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_uid`='".$_GET['template_uid']."' ";
			$query.="AND ";
			$query.="`language_uid`='".$_GET['language_uid']."' ";
			$arrTranslations = database::arrQuery($query);
		}
		$arrJson = array(
			'template'	=> $arrTranslations
		);
		echo json_encode($arrJson);
	}

	protected function getCreateTemplate() {
		$arrJson = array(
			'name'		=>'',
			'width'		=>500,
			'height'	=>500
		);
		$objJson=json_decode(json_encode($arrJson));
		$objTemplate = new template();
		$response = $objTemplate->APICreateTemplate($objJson);
		if(is_array($response)) {
			echo json_encode($response);
		} else if(is_numeric($response) && $response > 0) {
			$this->getTemplate($response);
		}
	}

	protected function getArticleTemplates() {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`width`, ";
		$query.="`height` ";
		$query.="FROM ";
		$query.="`template` ";
		$query.="WHERE ";
		$query.="`is_suitable_to_article` ='1' ";
		$query.="ORDER BY `uid`";
		$result = database::query($query);
		$arrTemplates = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				$arrTemplates[] = array(
					'uid'					=> $row['uid'],
					'name'					=> str_replace('\\','',$row['name']),
					'width'					=> $row['width'],
					'height'				=> $row['height']
				);
			}
		}
		$arrJson = array(
			'template'	=> $arrTemplates
		);
		echo json_encode($arrJson);
	}

	protected function getArticleCategories() {
		$arrCategories = article_category::getList(true);
		$arrJson = array(
			'categories'	=> $arrCategories
		);
		echo json_encode($arrJson);
	}

	protected function getCreateArticle() {
		$arrJson = array(
			'title'						=>'demo title',
			'width'						=>500,
			'height'					=>500,
			'article_template_type_uid'	=>1,
			'article_category_uid'		=>1,
			'unit_uid'					=>1,
			'template_uid'				=>1,

		);
		$objJson=json_decode(json_encode($arrJson));
		$objArticle = new article();
		$response = $objArticle->APICreateArticle($objJson);
		if(is_array($response)) {
			echo json_encode($response);
		} else if(is_numeric($response) && $response > 0) {
			$this->getArticle($response);
		}
	}

	protected function getGames() {
		$arrGames = array();
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`tagname`, ";
		$query.="`game_number` ";
		$query.="FROM ";
		$query.="`game`";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				$arrGames[] = array(
					'uid'			=>$row['uid'],
					'game_number'	=>$row['game_number'],
					'key'			=>$row['tagname'],
					'name'			=>$row['name']
				);
			}
		}
		echo json_encode(array('games'=>$arrGames));
	}
}
?>