<?php

class account_reseller_manage_school_package extends Controller {

	private $token = 'orderedpackages';
	private $arrTokens = array(
		'orderedpackages',
		'delete',
		'sections',
		'gamesandactivities',
		'view',
		'defaultSettings',
		'makeabuyrequest',
		'pendingorders',
		'cancelrequest',
		'activepackages',
		'activate'
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
	private $school_uid		= null;

	private $view_mode		=false;

	public function __construct() {

		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($_SESSION['user']['uid'])) {
			$this->reseller_uid = $_SESSION['user']['uid'];
		}

		if (isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$this->school_uid = $this->arrPaths[3];
		}

		if (isset($this->arrPaths[4])) {
			$this->token = $this->arrPaths[4];
			$this->token = str_replace(array('_','-'),array('',''),$this->token);
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

	protected function doCancelrequest() {
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5])) {
			$objSchoolPackages = new school_packages();
			$objSchoolPackages->CancelPurchaseRequest($this->arrPaths[5],$this->reseller_uid,$this->school_uid);
			output::redirect(config::url('account/package-management/ordered-packages/'.$this->school_uid.'/'));
		}
	}

	protected function doActivate() {
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5])) {
			$objSchoolPackages = new school_packages();
			$objSchoolPackages->ActivatePackage($this->arrPaths[5],$this->reseller_uid,$this->school_uid);
			output::redirect(config::url('account/package-management/ordered-packages/'.$this->school_uid.'/'));
		}
	}

	protected function doActivepackages() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.school.active.package.list');
		$objSchoolPackages = new school_packages();
		$arrPackages = $objSchoolPackages->getSchoolActivePackages($this->reseller_uid,$this->school_uid);

		if ($arrPackages && count($arrPackages) > 0) {
			$rows = array();
			foreach ($arrPackages as $uid => $arrData) {
				$row = make::tpl('body.account.school.active.package.list.row')->assign($arrData);
				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}

		$page_display_title = $objSchoolPackages->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $objSchoolPackages->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $objSchoolPackages->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $objSchoolPackages->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title', $page_display_title);
		$body->assign('page.navigation', $page_navigation);
		$body->assign('reseller_uid',$this->reseller_uid);
		$body->assign('school_uid',$this->school_uid);
		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}

	protected function doOrderedpackages() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.school.pending.order.list');
		$objSchoolPackages = new school_packages();
		$arrPackages = $objSchoolPackages->getListPendingPackageOrders($this->reseller_uid,$this->school_uid);

		if ($arrPackages && count($arrPackages) > 0) {
			$rows = array();
			foreach ($arrPackages as $uid => $arrData) {
				$row = make::tpl('body.account.school.pending.order.list.row')->assign($arrData);
				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}

		$page_display_title = $objSchoolPackages->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $objSchoolPackages->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $objSchoolPackages->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $objSchoolPackages->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title', $page_display_title);
		$body->assign('page.navigation', $page_navigation);
		$body->assign('reseller_uid',$this->reseller_uid);
		$body->assign('school_uid',$this->school_uid);
		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}

	protected function doList() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.schooladmin.order.package.list');
		$objResellerPackage = new reseller_sub_package();
		$arrPackages = $objResellerPackage->getListActivatedPackages($this->reseller_uid);

		if ($arrPackages && count($arrPackages) > 0) {
			$rows = array();
			foreach ($arrPackages as $uid => $arrData) {
				$row = make::tpl('body.account.schooladmin.order.package.list.row')->assign($arrData);
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
			$class = 'checkbox_'.$l_uid . '_' . $year_uid . '_' . $unit_uid;
			$arrSections = sections::getFilteredSections($unit_uid);

			foreach ($arrSections as $row ) {
				$checked = '';
				$id = $l_uid . '_' . $year_uid . '_' . $unit_uid . '_' . $row['uid'];
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
		if ($language_uid != null && $package_uid != null && $endMethod != null && isset($this->json_years[$language_uid]) && is_array($this->json_years[$language_uid])) {
			$arrYears = years::getFilteredYears($this->json_years[$language_uid]);
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
		if ($language_uid != null && $package_uid != null && $year_uid != null && $endMethod != null && isset($this->json_units[$language_uid][$year_uid]) && is_array($this->json_units[$language_uid][$year_uid])) {
			$arrUnits = units::getFilteredUnits($year_uid,$this->json_units[$language_uid][$year_uid]);
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

			if($objPackage->get_sections() != '') {
				$this->objJson = json_decode($objPackage->get_sections());

				if(isset($this->objJson->sections)) {
					foreach($this->objJson->sections as $data) {
						$this->json_sections[] = $data->section_pair;
						$this->json_years[$data->learnable_language_uid][] = $data->year_uid;
						$this->json_units[$data->learnable_language_uid][$data->year_uid][] = $data->unit_uid;
						$this->json_section_uids[$data->learnable_language_uid][$data->unit_uid][] = $data->section_uid;
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
				if(isset($this->objJson->games)) {
					foreach($this->objJson->games as $data) {
						$this->json_games[] = $data->game_pair;
						$this->games[$data->learnable_language_uid][$data->unit_uid][$data->section_uid][] = $data->game_uid;
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
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5])) {
			$objResellerSubPackage = new reseller_sub_package($this->arrPaths[5]);
			if ($objResellerSubPackage->get_valid()) {
				$objResellerSubPackage->load();
				$this->ParsePackage($objResellerSubPackage);
				$this->view_mode=true;
				$skeleton = config::getUserSkeleton();
				$body = make::tpl('body.account.school.package.complete.view');
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
						'vat'			=>$objResellerSubPackage->get_vat(),
						'school_uid'	=>$this->school_uid,
						'lname'			=>$languageName
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
		output::redirect(config::url('account/order-packages/list/'));
	}
}

?>