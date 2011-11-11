<?php

class account_reseller_sub_package extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete',
		'sections',
		'gamesandactivities',
		'view',
		'defaultSettings'
	);
	private $arrPaths		= array();
	private $json_languages	= array(0);
	private $json_years		= array();
	private $json_units		= array();
	private $json_sections	= array();
	private $json_section_uids	= array();
	private $json_games		= array();
	private $games			= array();

	private $parts = array();
	private $objreseller_sub_package = null;
	private $objResellerPackage = null;

	private $reseller_uid	= null;

	private $view_mode		=false;

	public function __construct() {

		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($_SESSION['user']['uid'])) {
			$this->reseller_uid = $_SESSION['user']['uid'];
		}

		if (isset($this->arrPaths[2]) && in_array($this->arrPaths[2], $this->arrTokens)) {
			$this->token = $this->arrPaths[2];
		}

		if (in_array($this->token, $this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	protected function verifyParentPackage($package_uid=null) {
		if($this->reseller_uid!=null && $package_uid!=null) {
			$this->objResellerPackage = new reseller_package();
			return $this->objResellerPackage->isValidPackage($this->reseller_uid,$package_uid);
		}
	}
	protected function doDefaultSettings() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.reseller.package.default.settings');
		if(isset($_POST['submit'])) {
			reseller_sub_package::setDefaultPackages();
			output::redirect(config::url('account/packages/defaultSettings/'));
		}
		$body->assign('div.school.packages',$this->getPackagesByType('school'));
		$body->assign('div.homeuser.packages',$this->getPackagesByType('homeuser'));
		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}
	private function getPackagesByType($type="school") {
		$arrPackages = reseller_sub_package::getPackagesByType($type,$this->reseller_uid);
		$arrHtml = array();
		$input_name = 'is_default_school_package';
		if($type=='homeuser') {
			$input_name = 'is_default_homeuser_package';
		}
		foreach ($arrPackages as $arr) {
			$arr['Checked']='';
			$arr['input_name']=$input_name;
			$arr['id']= $input_name.'_'.$arr['uid'];
			if($type=='school' && $arr['is_default_school_package']==1) {
				$arr['Checked'] = 'checked="checked"';
			}
			if($type=='homeuser' && $arr['is_default_homeuser_package']==1) {
				$arr['Checked'] = 'checked="checked"';
			}
			$arrHtml[] = make::tpl('body.account.reseller.package.default.settings.radio')->assign($arr)->get_content();
		}
		return implode('',$arrHtml);
	}
	protected function doAdd() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.reseller.sub_package.add');
		$support_language_uid = 0;
		$arrLearnable = array();
		if (count($_POST) > 0) {
			$objResellerSubPackage = new reseller_sub_package();
			if (($response = $objResellerSubPackage->isValidCreate()) === true) {
				$this->RedirectToList();
			} else {
				if (isset($_POST['learnable_language_uid'])) {
					$arrLearnable = $_POST['learnable_language_uid'];
				}
				if (isset($_POST['support_language_uid'])) {
					$support_language_uid = $_POST['support_language_uid'];
				}
				if(isset($_POST['package_type']) && $_POST['package_type'] == 'homeuser') {
					$body->assign('homeuser_checked','checked="checked"');
				}
				$body->assign($objResellerSubPackage->arrForm);
			}
		}

		$arrLearnableLanguages = $this->getLearnableLanguages($arrLearnable);
		$body->assign(
			array(
				'learnable_languages' => implode("", $arrLearnableLanguages)
			)
		);

		$arrSupportLanguages = $this->getSupportLanguages($support_language_uid);
		$body->assign(
			array(
				'support_languages' => implode("", $arrSupportLanguages)
			)
		);
		$objCurrency = new currencies();
		$body->assign('CurrencySymbol',$objCurrency->getCurrencySymbol($_SESSION['user']['prefix']));
		/*
		$body->assign(
			array(
				'package.price' => $this->getPriceForm()
			)
		);
		*/
		$body->assign('reseller_uid',$this->reseller_uid);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	protected function doEdit() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.reseller.sub_package.edit');
		$arrBody = array();
		$uid = (isset($this->arrPaths[3]) && (int) $this->arrPaths[3] > 0) ? $this->arrPaths[3] : '';

		$support_language_uid = 0;
		$pricing_json = null;
		$arrLearnable = array();

		if ($uid != '') {
			$objResellerSubPackage = new reseller_sub_package($uid);
			if ($objResellerSubPackage->get_valid()) {
				$objResellerSubPackage->load();
				$this->HandleDifferentlyIfActivated($objResellerSubPackage);
				$this->ParsePackage($objResellerSubPackage);
				$arrBody['uid'] = $uid;
				if (count($_POST) > 0) {
					if (($response = $objResellerSubPackage->isValidUpdate()) === true) {
						$this->RedirectToList();
					} else {
						if (isset($objPackage->arrForm['support_language_uid'])) {
							$support_language_uid = $objPackage->arrForm['support_language_uid'];
						}
						if (isset($_POST['learnable_language_uid'])) {
							$arrLearnable = $_POST['learnable_language_uid'];
						}
						if(isset($_POST['package_type']) && $_POST['package_type'] == 'homeuser') {
							$body->assign('homeuser_checked','checked="checked"');
						}
						$body->assign($objResellerSubPackage->arrForm);
					}
				} else {
					foreach ($objResellerSubPackage->TableData as $idx => $val) {
						$arrBody[$idx] = $val['Value'];
					}
					$support_language_uid = $arrBody['support_language_uid'];

					if($arrBody['package_type'] == 'homeuser') {
						$body->assign('homeuser_checked','checked="checked"');
					}
					$arrLearnable = array();
					$arrLearnable = $this->json_languages;
					//$pricing_json = $arrBody['pricing'];
					$body->assign($arrBody);
				}

				$arrLearnableLanguages = $this->getLearnableLanguages($arrLearnable);
				$body->assign(
					array(
						'learnable_languages' => implode("", $arrLearnableLanguages)
					)
				);

				$arrSupportLanguages = $this->getSupportLanguages($support_language_uid);
				$body->assign(
					array(
						'support_languages' => implode("", $arrSupportLanguages)
					)
				);
				/*
				$body->assign(
						array(
							'package.price' => $this->getPriceForm($uid,$pricing_json)
						)
				);
				*/
				$objCurrency = new currencies();
				$body->assign('CurrencySymbol',$objCurrency->getCurrencySymbol($_SESSION['user']['prefix']));
			}
		} else {
			output::redirect(config::url('account/packages/list/'));
		}

		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);

	}

	protected function doList() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.reseller.sub.package.list');
		$objResellerPackage = new reseller_sub_package();
		$arrPackages = $objResellerPackage->getList($this->reseller_uid);

		if ($arrPackages && count($arrPackages) > 0) {
			$rows = array();
			foreach ($arrPackages as $uid => $arrData) {
				$arrData['created_date'] = date('d/m/Y H:i:s', strtotime($arrData['created_date']));
				$xhtml = 'body.account.reseller.sub.package.list.row';
				if($arrData['is_active']==1) {
					$xhtml = 'body.account.reseller.sub.package.list.active.row';
				}
				$row = make::tpl($xhtml)->assign($arrData);
				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}

		$page_display_title = $objResellerPackage->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $objResellerPackage->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $objResellerPackage->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $objResellerPackage->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title', $page_display_title);
		$body->assign('page.navigation', $page_navigation);
		$body->assign('reseller_uid',$this->reseller_uid);
		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}


	private function getPriceForm($package_uid=null,$pricing_json=null) {
		$arrPriceAndVat = array();
		if($pricing_json!=null) {
			$objJson = json_decode($pricing_json);
			if(is_array($objJson->pricing) && count($objJson->pricing)) {
				foreach($objJson->pricing as $data) {
					$arrPriceAndVat[$data->locale]['price'] = $data->price;
					$arrPriceAndVat[$data->locale]['vat'] = $data->vat;
				}
			}
		}
		$query = "SELECT ";
		$query.="`prefix` AS `locale`";
		$query.="FROM ";
		$query.="`language` ";
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";
		$result = database::query($query);
		if (mysql_error() == '' && mysql_num_rows($result)) {
			$arrLi = array();
			$arrDiv = array();
			while ($row = mysql_fetch_array($result)) {

				if(is_array($arrPriceAndVat) && count($arrPriceAndVat) > 0) {
					$row['price']	= $arrPriceAndVat[$row['locale']]['price'];
					$row['vat']		= $arrPriceAndVat[$row['locale']]['vat'];
				} else {
					$row['price']	=null;
					$row['vat']		=null;
				}

				if (($package_uid == null || is_null($row['price']) || $row['price'] == 0) && isset($_POST['price'][$row['locale']])) {
					$row['price'] = $_POST['price'][$row['locale']];
				}

				if (($package_uid == null || is_null($row['vat']) || $row['vat'] == 0) && isset($_POST['vat'][$row['locale']])) {
					$row['vat'] = $_POST['vat'][$row['locale']];
				}

				$arrLi[] = '<li><a href="#locale-' . $row['locale'] . '"><span>' . $row['locale'] . '</span></a></li>';
				$arrDiv[] = make::tpl('body.admin.package.price')->assign($row)->get_content();
			}
			return make::tpl('body.admin.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getSupportLanguages($support_language_uid=null) {
		$arrSupportLanguages = array();
		$arrLanguage = language::getAllSupportLanguages();
		$iCount = 0;
		if (count($arrLanguage) && is_array($arrLanguage)) {
			foreach ($arrLanguage as $arr) {
				if ($iCount % 3 == 0) {
					$arrSupportLanguages[] = '<br />';
				}
				$iCount++;
				if ($support_language_uid == $arr['uid']) {
					$arr['Checked'] = 'checked="checked"';
				}
				$arrSupportLanguages[] = make::tpl('body.admin.package.support_languages')->assign($arr)->get_content();
			}
		}
		return $arrSupportLanguages;
	}

	private function getLearnableLanguages($arrLearnable = array()) {
		$arrLearnableLanguages = array();
		$arrLanguage = language::getAllAvailableLanguages();
		$iCount = 0;
		if (count($arrLanguage) && is_array($arrLanguage)) {
			foreach ($arrLanguage as $arr) {
				if ($iCount % 3 == 0) {
					$arrLearnableLanguages[] = '<br />';
				}
				$iCount++;
				if (count($arrLearnable) && in_array($arr['uid'], $arrLearnable)) {
					$arr['Checked'] = 'checked="checked"';
				}
				$arrLearnableLanguages[] = make::tpl('body.admin.package.available_language')->assign($arr)->get_content();
			}
		}
		return $arrLearnableLanguages;
	}

	protected function doSections() {
		@ini_set('memory_limit', '256M');
		if (isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$objResellerSubPackage = new reseller_sub_package($this->arrPaths[3]);
			if ($objResellerSubPackage->get_valid()) {
				$objResellerSubPackage->load();
				$this->HandleDifferentlyIfActivated($objResellerSubPackage);
				$skeleton = config::getUserSkeleton();
				$body = make::tpl('body.account.reseller.sub_package.sections');

				if (isset($_POST['submit'])) {
					//$objPackageSections = new package_sections();
					//$objPackageSections->saveSections();
					if(isset($_POST['package_uid']) && isset($_POST['section'])) {
						$objResellerSubPackage->SavePackageSections(
							$_POST['package_uid'],
							$_POST['section']
						);
					}
					if (isset($this->arrPaths[3])) {
						output::redirect(config::url('account/packages/sections/' . $this->arrPaths[3] . '/'));
					} else {
						$this->RedirectToList();
					}
				}
				$this->ParsePackage($objResellerSubPackage);
				$body->assign(
						array(
							'tabs'			=>$this->getLanguages(),
							'reseller_uid'	=>$this->reseller_uid,
							'uid'			=>$objResellerSubPackage->get_uid()
						)
				);
				$skeleton->assign(
						array(
							'body' => $body
						)
				);
				output::as_html($skeleton, true);
			} else {
				$this->RedirectToList();
			}
		} else {
			$this->RedirectToList();
		}
	}

	private function getLanguages() {
		if(is_array($this->json_languages) && count($this->json_languages)) {
			$arrLi	= array();
			$arrDiv	= array();
			$arrLanguages = language::getFilteredLanguages($this->json_languages);
			if (is_array($arrLanguages) && count($arrLanguages)) {
				foreach( $arrLanguages as $row ) {
					$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
						array(
							'link_href'	=>'#language-' . $row['uid'],
							'lable'		=>$row['name']
						)
					)->get_content();

					$arrDiv[] = make::tpl('body.general.tabs.div')->assign(
						array(
							'tab_id'		=>'language-' . $row['uid'],
							'tab_content'	=>$this->getYears($row['uid'])
						)
					)->get_content();
				}
			}
			return make::tpl('body.admin.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getYears($learnable_language_uid=null) {
		if ($learnable_language_uid != null) {
			$l_uid = $learnable_language_uid;
			$arrYears = years::getFilteredYears();
			$arrLi = array();
			$arrDiv = array();
			if (is_array($arrYears) && count($arrYears)) {
				foreach ($arrYears as $row) {
					$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
							array(
								'link_href'	=>'#year-' . $l_uid . '-' . $row['uid'],
								'lable'		=>$row['name']
							)
						)->get_content();

					$arrDiv[] = make::tpl('body.general.tabs.div')->assign(
						array(
							'tab_id'		=>'year-' . $l_uid . '-' . $row['uid'],
							'tab_content'	=>$this->getUnitTabs($row['uid'], $learnable_language_uid)
						)
					)->get_content();

				}
			}
			return make::tpl('body.admin.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getUnitTabs($year_uid=null, $learnable_language_uid=null) {
		if ($year_uid != null && $learnable_language_uid != null) {
			$l_uid = $learnable_language_uid;
			$arrUnits = units::getFilteredUnits($year_uid);
			$arrLi = array();
			$arrDiv = array();
			$id ='';
			foreach ( $arrUnits as $row ) {

				$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
						array(
							'link_href'	=>'#unit-' . $l_uid . '-' . $row['uid'],
							'lable'		=>'Unit ' . $row['unit_number']
						)
					)->get_content();
				$id = $l_uid . '_' . $year_uid . '_' . $row['uid'];
				$arrDiv[] = make::tpl('body.section.tabs.div')->assign(
					array(
						'tab_id'		=>'unit-' . $l_uid . '-' . $row['uid'],
						'tab_content'	=>$this->getSections($row['uid'], $year_uid, $learnable_language_uid),
						'other_text'	=>$row['name'] . '<br/>',
						'id'			=>$id
					)
				)->get_content();

			}

			return make::tpl('body.admin.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getSections($unit_uid=null, $year_uid=null, $learnable_language_uid=null) {
		$html = ' ';
		if ($year_uid != null && $unit_uid != null && $learnable_language_uid != null) {
			$l_uid = $learnable_language_uid;
			$class = 'checkbox_'.$l_uid.'_'.$unit_uid;
			$arrSections = sections::getFilteredSections($unit_uid);

			foreach ($arrSections as $row ) {
				$checked = '';
				$id = $l_uid.'_'.$unit_uid.'_'.$row['uid'];
				if(is_array($this->json_sections) && count($this->json_sections)) {
					if(in_array($id,$this->json_sections)) {
						$checked=' checked="checked" ';
					}
				}
				$html.= make::tpl('body.package.sections.checkbox')->assign(
					array(
						'id'		=>$id,
						'name'		=>$row['name'],
						'uid'		=>$row['uid'],
						'checked'	=>$checked,
						'class'		=>$class
					)
				)->get_content();
			}
		}
		return $html;
	}
	
	protected function doGamesandactivities() {
		@ini_set('memory_limit', '256M');
		if (isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$objResellerSubPackage = new reseller_sub_package($this->arrPaths[3]);
			if ($objResellerSubPackage->get_valid()) {
				$objResellerSubPackage->load();
				$this->HandleDifferentlyIfActivated($objResellerSubPackage);
				$skeleton = config::getUserSkeleton();
				$body = make::tpl('body.account.sub_package.gamesandactivities');

				if (isset($_POST['submit'])) {
					if(isset($_POST['package_uid']) && isset($_POST['game'])) {
						$objResellerSubPackage->SavePackageGames(
							$objResellerSubPackage->get_uid(),
							$_POST['game']
						);
					}
					if (isset($this->arrPaths[3])) {
						output::redirect(config::url('account/packages/gamesandactivities/' . $this->arrPaths[3] . '/'));
					} else {
						$this->RedirectToList();
					}
				}
				$this->ParsePackage($objResellerSubPackage);
				$body->assign(
						array(
							'div.games'		=>$this->getGameContent($objResellerSubPackage->get_uid()),
							'reseller_uid'	=>$this->reseller_uid,
							'uid'			=>$objResellerSubPackage->get_uid()
						)
				);
				$skeleton->assign(
						array(
							'body' => $body
						)
				);
				output::as_html($skeleton, true);
			} else {
				$this->RedirectToList();
			}
		} else {
			$this->RedirectToList();
		}
	}

	

	private function getGameContent($package_uid=null) {
		if ($package_uid != null) {
			return $this->getPackageLearnableLanguages($package_uid, 'getGames');
		}
		return ' ';
	}

	private function getPackageLearnableLanguages($package_uid=null, $endMethod=null) {
		if ($package_uid != null && $endMethod != null) {
			$arrLanguages = language::getFilteredLanguages($this->json_languages);
			$arrLi = array();
			$arrDiv = array();
			foreach ($arrLanguages as $row) {
				$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
					array(
						'link_href'	=>'#language-' . $row['uid'],
						'lable'		=>$row['name']
					)
				)->get_content();

				$arrDiv[] = make::tpl('body.general.tabs.div')->assign(
					array(
						'tab_id'		=>'language-' . $row['uid'],
						'tab_content'	=>$this->getPackageYears($row['uid'], $package_uid, $endMethod)
					)
				)->get_content();

			}
			return make::tpl('body.admin.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getPackageYears($language_uid=null, $package_uid=null, $endMethod=null) {
		if ($language_uid != null && $package_uid != null && $endMethod != null) {
			$arrYears = years::getFilteredYears();
			$arrLi = array();
			$arrDiv = array();

			foreach($arrYears as $row) {
				// following code will create years 
				$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
					array(
						'link_href'	=>'#year-' . $language_uid . '_' . $row['uid'],
						'lable'		=>$row['name']
					)
				)->get_content();

				$arrDiv[] = make::tpl('body.general.tabs.div')->assign(
					array(
						'tab_id'		=>'year-' . $language_uid . '_' . $row['uid'],
						'tab_content'	=>$this->getPackageUnits($language_uid, $package_uid, $row['uid'], $endMethod)
					)
				)->get_content();

			}

			return make::tpl('body.admin.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getPackageUnits($language_uid=null, $package_uid=null, $year_uid=null, $endMethod=null) {
		if ($language_uid != null && $package_uid != null && $year_uid != null && $endMethod != null && isset($this->json_units[$language_uid]) && is_array($this->json_units[$language_uid])) {
			$arrUnits = units::getFilteredUnits($year_uid,$this->json_units[$language_uid]);
			$arrLi = array();
			$arrDiv = array();

			foreach ($arrUnits as $row) {

				$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
					array(
						'link_href'	=>'#units-' . $language_uid . '_' . $row['uid'],
						'lable'		=>'Unit ' . $row['unit_number']
					)
				)->get_content();

				$arrDiv[] = make::tpl('body.general.tabs.div')->assign(
					array(
						'tab_id'		=>'units-' . $language_uid . '_' . $row['uid'],
						'tab_content'	=>$this->getPackageSection($language_uid, $package_uid, $row['uid'], $endMethod),
						'other_text'	=>$row['name'] . '<br/>'
					)
				)->get_content();

			}

			return make::tpl('body.admin.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getPackageSection($language_uid=null, $package_uid=null, $unit_uid=null, $endMethod=null) {

		if ($language_uid != null && $package_uid != null && $unit_uid != null && $endMethod != null && isset($this->json_section_uids[$language_uid][$unit_uid]) && is_array($this->json_section_uids[$language_uid][$unit_uid])) {
			$arrSections = sections::getFilteredSections($unit_uid,$this->json_section_uids[$language_uid][$unit_uid]);
			$arrLi = array();
			$arrDiv = array();
			$id='';
			$xhtml = 'body.section.tabs.div';
			if($this->view_mode) {
				$xhtml = 'body.general.tabs.div';
			}
			foreach ($arrSections as $row ) {
			$id = $language_uid . '_' . $unit_uid . '_' . $row['uid'];
				$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
					array(
						'link_href'	=>'#section-' . $language_uid . '_' . $unit_uid . '_' . $row['uid'],
						'lable'		=>'Section ' . $row['section_number']
					)
				)->get_content();

				$arrDiv[] = make::tpl($xhtml)->assign(
					array(
						'tab_id'		=>'section-' . $language_uid . '_' . $unit_uid . '_' . $row['uid'],
						'tab_content'	=>$this->$endMethod($language_uid, $row['uid'],$unit_uid),
						'other_text'	=>$row['name'] . '<br/>',
						'id'			=>$id
					)
				)->get_content();

			}

			return make::tpl('body.admin.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getGames($language_uid=null, $section_uid=null, $unit_uid=null) {
		$html = '';
		if ($language_uid != null && $section_uid != null && $unit_uid != null) {
			$arrGames = game::getFilteredGames();
			$class = 'checkbox_'.$language_uid . '_' . $unit_uid . '_' . $section_uid;
			foreach ($arrGames as $row) {
				$checked = '';
				$id = $language_uid.'_'.$unit_uid.'_'.$section_uid.'_'.$row['uid'];
				if(is_array($this->json_games) && count($this->json_games)) {
					if(in_array($id,$this->json_games)) {
						$checked=' checked="checked" ';
					}
				}
				$html.= make::tpl('body.package.games.checkbox')->assign(
					array(
						'id'		=>$id,
						'name'		=>$row['name'],
						'uid'		=>$row['uid'],
						'checked'	=>$checked,
						'class'		=>$class
					)
				)->get_content();
			}
		}
		return $html;
	}

	private function getGamesView($language_uid=null, $section_uid=null, $unit_uid=null) {
		$html = '';
		if ($language_uid != null && $section_uid != null && $unit_uid != null && isset($this->games[$language_uid][$unit_uid][$section_uid]) && is_array($this->games[$language_uid][$unit_uid][$section_uid])) {
			$arrGames = game::getFilteredGames($this->games[$language_uid][$unit_uid][$section_uid]);
			$game_number = 1;
			foreach ($arrGames as $row) {
				$html.= make::tpl('body.package.games.nocheckbox')->assign(
					array(
						'name'			=>$row['name'],
						'game_number'	=>$game_number++
					)
				)->get_content();
			}
		}
		return $html;
	}

	protected function doDelete() {
		$this->RedirectToList();
		/*
		if (isset($this->arrPaths[3]) && (int) $this->arrPaths[3] > 0) {
			$objPackage = new reseller_sub_package($this->arrPaths[3]);
			$objPackage->redirectTo('account/reseller_sub_package/list/' . $this->arrPaths[3] . '/' . $this->arrPaths[3] . '/');
		} else {
			output::redirect(config::url('account/reseller_sub_package/list/' . $this->arrPaths[3] . '/' . $this->arrPaths[3] . '/'));
		}
		*/
	}

	private function ParsePackage($objPackage=null) {
		if($objPackage!=null) {
			@ini_set('memory_limit', '256M');
			if($objPackage->get_sections() != '') {
				$this->objJson = json_decode($objPackage->get_sections());

				if(isset($this->objJson->sections->l)) {
					foreach($this->objJson->sections->l as $arrLanguage) {
						$learnable_language_uid = $arrLanguage->uid;
						foreach($arrLanguage->u as $arrUnit) {
							$this->json_units[$learnable_language_uid][] = $arrUnit->uid;
							foreach($arrUnit->s as $section_uid) {
								$this->json_sections[] = $arrLanguage->uid.'_'.$arrUnit->uid.'_'.$section_uid;
								$this->json_section_uids[$arrLanguage->uid][$arrUnit->uid][] = $section_uid;
							}
						}
					}
				}
			}

			if($objPackage->get_learnable_language() != '') {
				$this->objJson = json_decode($objPackage->get_learnable_language());
				if(isset($this->objJson->language_uids) && is_array($this->objJson->language_uids)) {
					$this->json_languages = $this->objJson->language_uids;
				}
			}

			if($objPackage->get_games() != '') {
				$this->objJson = json_decode($objPackage->get_games());
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
			}
		}
	}

	private function HandleDifferentlyIfActivated($objResellerSubPackage=null) {
		if($objResellerSubPackage!=null) {
			if($objResellerSubPackage->get_is_active()==1) {
				output::redirect(config::url('account/packages/view/' . $this->arrPaths[3] . '/'));
			}
		}
	}

	private function doView() {
		@ini_set('memory_limit', '256M');
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$objResellerSubPackage = new reseller_sub_package($this->arrPaths[3]);
			if ($objResellerSubPackage->get_valid()) {
				$objResellerSubPackage->load();
				$this->ParsePackage($objResellerSubPackage);
				$this->view_mode=true;
				$skeleton = config::getUserSkeleton();
				$body = make::tpl('body.account.package.complete.view');
				$languageName='';
				$objLanguage = new language($objResellerSubPackage->get_support_language_uid());
				if($objLanguage->get_valid()) {
					$objLanguage->load();
					$languageName=$objLanguage->get_name();
				}
				$objCurrency = new currencies();
				$body->assign(
					array(
						'name'	=>$objResellerSubPackage->get_name(),
						'price'	=>$objCurrency->getCurrencyFormat($_SESSION['user']['prefix'],$objResellerSubPackage->get_price()),
						'vat'	=>$objResellerSubPackage->get_vat(),
						'lname'	=>$languageName
					)
				);
				$body->assign(
					array(
						'tabs'=>$this->getPackageLearnableLanguages($objResellerSubPackage->get_uid(),'getGamesView')
					)
				);

				$skeleton->assign(
						array(
							'body' => $body
						)
				);
				output::as_html($skeleton, true);
			}
		}
	}

	private function RedirectToList() {
		output::redirect(config::url('account/packages/'));
	}
}

?>