<?php

class account_schooladmin_package extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'edit',
		'delete',
		'sections',
		'gamesandactivities',
		'packageadd',
	);
	private $parts = array();
	private $objschooladmin_sub_package = null;

	public function __construct() {

		parent::__construct();

		$this->parts = config::get('paths');

		if (isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
			$this->token = $this->parts[2];
		}

		if (in_array($this->token, $this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	protected function doPackageadd() {
		if (!isset($this->parts[3])) {
			output::redirect(config::url('account/schooladmin_sub_package/packageadd/' . $_SESSION["user"]["uid"] . '/'));
		}
		$skeleton = config::getUserSkeleton();
		$body = new xhtml('body.account.schooladmin_package.add');
		$body->load();

		$this->objPackage = new package();
		$this->objschooladminPackage = new schooladmin_package();

		if (isset($this->parts[4]) && $this->parts[4] == "add") {
			$this->objschooladminPackage = new schooladmin_package();
			if (($response = $this->objschooladminPackage->insertOrUpdate($this->parts[3], $_POST)) === true) {
				output::redirect(config::url('account/schooladmin_sub_package/packageadd/' . $this->parts[3] . '/'));
			}
		} else if (isset($this->parts[4]) && $this->parts[4] == "delete") {
			$this->objschooladminPackage = new schooladmin_package();
			$response = $this->objschooladminPackage->deleteBeforeUpdate($this->parts[3], $this->parts[5]);
			output::redirect(config::url('account/schooladmin_sub_package/packageadd/' . $this->parts[3] . '/'));
		}

		// $packageList=$this->objPackage->getList();
		$availablePackageList = $this->objschooladminPackage->getAvailablePackageList($this->parts[3]);
		$updatedAvailablePackageList = $this->objschooladminPackage->getUpdatedAvailablePackageList($this->parts[3]);

		$selectedPackages = $this->objschooladminPackage->getPackageIds($this->parts[3]);


		$availablePackageHtml = "";
		$updateAvailablePackageHtml = "";

		foreach ($availablePackageList as $package) {
			$availablePackageHtml.='<tr>';
			$availablePackageHtml.='<td>';
			$availablePackageHtml.=$package["name"];
			$availablePackageHtml.='</td>';
			$availablePackageHtml.='<td>';
			$availablePackageHtml.='<a href="' . config::url('account/schooladmin_sub_package/list/' . $this->parts[3] . '/' . $package["uid"] . '/') . '">Sub Package</a> | ';
			$availablePackageHtml.='<a href="' . config::url('account/schooladmin_sub_package/packageadd/' . $this->parts[3] . '/delete/' . $package["uid"] . '/') . '">Delete</a>';
			$availablePackageHtml.=' | ';
			$availablePackageHtml.= ( array_search($package["uid"], $updatedAvailablePackageList) === false) ? "-" : '<a href="javascript:;" onclick="make_checked(\'pack_' . $package["uid"] . '\')">Update available</a>';
			$availablePackageHtml.= ( array_search($package["uid"], $updatedAvailablePackageList) === false) ? "" : '<div style="display:none"><input type="checkbox" id="pack_' . $package["uid"] . '" name="packages[]" value="' . $package["uid"] . '"  /></div>';
			$availablePackageHtml.='</td>';
			$availablePackageHtml.='</tr>';
		}

		$availablePackageHtml = (!empty($availablePackageHtml)) ? '<table width="100%" border="0" cellspacing="0" cellpadding="10" class="table_main"><tr><th>Package Name</th><th></th></tr>' . $availablePackageHtml . "</table>" : '<p>You have not available any packages</p>';
//		$updateAvailablePackageHtml = (!empty($updateAvailablePackageHtml)) ? $updateAvailablePackageHtml : "No new updates in packages";

		$objschooladmin = new profile_schooladmin ();
		$schooladminName = $objschooladmin->getschooladminNameByUid($this->parts[3]);

		$body->assign('schooladmin_name', $schooladminName);

//		$body->assign('updated_available_packages', $updateAvailablePackageHtml);
		$body->assign('available_packages', $availablePackageHtml);
		$body->assign('schooladmin_uid', $this->parts[3]);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	protected function doEdit() {
		$skeleton = config::getUserSkeleton();

		$body = new xhtml('body.account.schooladmin_sub_package.edit');
		$body->load();

		$school_uid = $_SESSION['user']['uid'];
		$package_uid = $this->parts[4];
		$objschooladminSubPackage = new schooladmin_package($package_uid);

		if ($objschooladminSubPackage->get_valid()) {
			$objschooladminSubPackage->load();
		}


		$body->assign('package_uid', $package_uid);
		$body->assign('school_uid', $school_uid);

		$support_language_uid = 0;
		$arrLearnable = array();
		if (count($_POST) > 0) {
			$objPackage = new schooladmin_package();

			if (($response = $objPackage->isValidUpdate($package_uid, $school_uid, $objschooladminSubPackage->get_reseller_package_uid(), $objschooladminSubPackage->get_reseller_sub_package_uid())) === true) {
				output::redirect(config::url('account/schooladmin_sub_package/'));
			} else {
				if (isset($objPackage->arrForm['support_language_uid'])) {
					$support_language_uid = $objPackage->arrForm['support_language_uid'];
				}
				if (isset($_POST['learnable_language_uid'])) {
					$arrLearnable = $_POST['learnable_language_uid'];
				}
				$body->assign($objPackage->arrForm);
			}
		}

		$selectedLearnableLanguage = $objschooladminSubPackage->getLearnableLanguages($package_uid);
		$arrLearnableLanguages = $this->getLearnableLanguages($objschooladminSubPackage->get_reseller_package_uid(), $selectedLearnableLanguage);
		$body->assign(
				array(
					'name' => $objschooladminSubPackage->get_name()
				)
		);
		$body->assign(
				array(
					'learnable_languages' => implode("", $arrLearnableLanguages)
				)
		);

		$arrSupportLanguages = $this->getSupportLanguages($package_uid, $support_language_uid);
		$body->assign(
				array(
					'support_languages' => $arrSupportLanguages
				)
		);

		$body->assign(
				array(
					'package.price' => $this->getEditPriceForm($package_uid)
				)
		);

		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	private function getEditPriceForm($packageUid=0) {
		$query = "SELECT ";
		$query.="`prefix` AS `locale`, ";
		$query.="( ";
		$query.="SELECT ";
		$query.="`price` ";
		$query.="FROM ";
		$query.="`schooladmin_package_price` ";
		$query.="WHERE ";
		$query.="`schooladmin_package_price`.`locale` = `language`.`prefix` ";
		$query.="AND ";
		$query.="`schooladmin_package_price`.`package_uid`='{$packageUid}' ";
		$query.="LIMIT 1";
		$query.=") AS `price`, ";

		$query.="( ";
		$query.="SELECT ";
		$query.="`vat` ";
		$query.="FROM ";
		$query.="`schooladmin_package_price` ";
		$query.="WHERE ";
		$query.="`schooladmin_package_price`.`locale` = `language`.`prefix` ";
		$query.="AND ";
		$query.="`schooladmin_package_price`.`package_uid`='{$packageUid}' ";
		$query.="LIMIT 1";
		$query.=") AS `vat` ";

		$query.="FROM ";
		$query.="`language` ";
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";
		$result = database::query($query);
		if (mysql_error() == '' && mysql_num_rows($result)) {
			$arrLi = array();
			$arrDiv = array();
			while ($row = mysql_fetch_array($result)) {

				if (($packageUid = 0 || is_null($row['price']) || $row['price'] == 0) && isset($_POST['price'][$row['locale']])) {
					$row['price'] = $_POST['price'][$row['locale']];
				}

				if (($packageUid = 0 || is_null($row['vat']) || $row['vat'] == 0) && isset($_POST['vat'][$row['locale']])) {
					$row['vat'] = $_POST['vat'][$row['locale']];
				}

				$arrLi[] = '<li><a href="#locale-' . $row['locale'] . '"><span>' . $row['locale'] . '</span></a></li>';
				$arrDiv[] = make::tpl('body.account.package.price')->assign($row)->get_content();
			}
			return make::tpl('body.account.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	protected function doList() {

		$skeleton = config::getUserSkeleton();

		$hide = 'style="visibility:hidden;"';
		$body = new xhtml('body.account.schooladmin_sub_package.list');
		$body->load();

		$school_uid = $_SESSION['user']['uid'];
		$body->assign('school_uid', $school_uid);
		$this->objschooladmin_sub_package = new schooladmin_package();
		$arrschooladminpackage = $this->objschooladmin_sub_package->getAvailablePackageList($school_uid);
		$i = 0;
		if ($arrschooladminpackage && count($arrschooladminpackage) > 0) {
			$rows = array();
			foreach ($arrschooladminpackage as $arrData) {
				$i++;

				$row = new xhtml('body.account.schooladmin_sub_package.list.row');
				$row->load();
				$row->assign(array(
					'package_uid' => $arrData['uid'],
					'name' => stripslashes($arrData['name'])
				));

				$row->assign('school_uid', $school_uid);

				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}
		$page_display_name = $this->objschooladmin_sub_package->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $this->objschooladmin_sub_package->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objschooladmin_sub_package->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objschooladmin_sub_package->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		$body->assign('page.display.name', $page_display_name);
		$body->assign('page.navigation', $page_navigation);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);

		output::as_html($skeleton, true);
	}

	private function getGameContent($package_uid=null) {
		if ($package_uid != null) {
			return $this->getPackageLearnableLanguages($package_uid, 'getGames');
		}
		return ' ';
	}

	private function getPackageLearnableLanguages($package_uid=null, $endMethod=null) {
		if ($package_uid != null && $endMethod != null) {

			$query = "SELECT ";
			$query.=" `L`.`name`, ";
			$query.=" `L`.`uid` ";
			$query.=" FROM ";
			$query.=" `language` AS `L`, ";
			$query.=" `schooladmin_package_language` AS `PL`";
			$query.=" WHERE ";
			$query.=" `PL`.`learnable_language_uid` = `L`.`uid` ";
			$query.=" AND ";
			$query.=" `PL`.`package_uid` = '{$package_uid}'";
			$query.=" ORDER BY `L`.`name`";
			$result = database::arrQuery($query);
			$arrLi = array();
			$arrDiv = array();
			if (count($result) > 0) {
				foreach ($result as $row) {
					$arrLi[] = '<li><a href="#language-' . $row['uid'] . '"><span>' . $row['name'] . '</span></a></li>';
					$arrDiv[] = '<div id="language-' . $row['uid'] . '">
									' . $this->getPackageYears($row['uid'], $package_uid, $endMethod) . '
								</div>';
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

	private function getPackageYears($language_uid=null, $package_uid=null, $endMethod=null) {
		if ($language_uid != null && $package_uid != null && $endMethod != null) {
			$query = "SELECT ";
			$query.="DISTINCT `Y`.`uid`, ";
			$query.="`Y`.`name` ";
			$query.="FROM ";
			$query.="`years` AS `Y`, ";
			$query.="`schooladmin_package_sections` AS `PS`";
			$query.=" WHERE ";
			$query.="`PS`.`year_uid` = `Y`.`uid` ";
			$query.=" AND `PS`.`package_uid` = '{$package_uid}'";
			$query.=" AND ";
			$query.=" `PS`.`learnable_language_uid` = '" . $language_uid . "'";
			$query.=" ORDER BY `Y`.`position`"; //exit;

			$result = database::arrQuery($query);
			$arrLi = array();
			$arrDiv = array();
			if (count($result) > 0) {
				foreach ($result as $row) {
					$arrLi[] = '<li><a href="#year-' . $language_uid . '_' . $row['uid'] . '"><span>' . $row['name'] . '</span></a></li>';
					$arrDiv[] = '<div id="year-' . $language_uid . '_' . $row['uid'] . '">
									' . $this->getPackageUnits($language_uid, $package_uid, $row['uid'], $endMethod) . '
								</div>';
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

	private function getPackageUnits($language_uid=null, $package_uid=null, $year_uid=null, $endMethod=null) {
		if ($language_uid != null && $package_uid != null && $year_uid != null && $endMethod != null) {
			$query = "SELECT ";
			$query.=" DISTINCT `U`.`uid`, ";
			$query.=" `U`.`name`, ";
			$query.=" `U`.`unit_number` ";
			$query.=" FROM ";
			$query.=" `units` AS `U`, ";
			$query.=" `schooladmin_package_sections` AS `PS`";
			$query.=" WHERE ";
			$query.=" `PS`.`unit_uid` = `U`.`uid` ";
			$query.=" AND `PS`.`package_uid` = '{$package_uid}'";
			$query.=" AND ";
			$query.="`PS`.`year_uid` = '" . $year_uid . "'";
			$query.=" AND ";
			$query.="`PS`.`learnable_language_uid` = '" . $language_uid . "'";
			$query.=" ORDER BY `U`.`unit_number`";
			$result = database::arrQuery($query);
			$arrLi = array();
			$arrDiv = array();
			if (count($result) > 0) {
				foreach ($result as $row) {
					$arrLi[] = '<li><a href="#units-' . $language_uid . '_' . $row['uid'] . '"><span>Unit ' . $row['unit_number'] . '</span></a></li>';
					$arrDiv[] = '<div id="units-' . $language_uid . '_' . $row['uid'] . '">
									' . $row['name'] . '<br/>' . $this->getPackageSection($language_uid, $package_uid, $row['uid'], $endMethod) . '
								</div>';
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

	private function getPackageSection($language_uid=null, $package_uid=null, $unit_uid=null, $endMethod=null) {
		if ($language_uid != null && $package_uid != null && $unit_uid != null && $endMethod != null) {
			$query = "SELECT ";
			$query.="`S`.`name`, ";
			$query.="`S`.`uid`, ";
			$query.="`S`.`section_number` ";
			$query.=" FROM ";
			$query.=" `sections` AS `S`, ";
			$query.=" `schooladmin_package_sections` AS `PS`";
			$query.=" WHERE ";
			$query.=" `PS`.`section_uid` = `S`.`uid` ";
			$query.=" AND `PS`.`package_uid` = '{$package_uid}' ";
			$query.=" AND ";
			$query.="`PS`.`learnable_language_uid` = '" . $language_uid . "'";
			$query.=" AND ";
			$query.="`PS`.`unit_uid` = '" . $unit_uid . "'";
			$query.=" ORDER BY `S`.`section_number`";
			$result = database::arrQuery($query);
			$arrLi = array();
			$arrDiv = array();
			if (count($result) > 0) {
				foreach ($result as $row) {
					$arrLi[] = '<li><a href="#section-' . $language_uid . '_' . $unit_uid . '_' . $row['uid'] . '"><span>Section ' . $row['section_number'] . '</span></a></li>';
					$arrDiv[] = '<div id="section-' . $language_uid . '_' . $unit_uid . '_' . $row['uid'] . '">
									' . $row['name'] . '<br/>' . $this->$endMethod($language_uid, $row['uid']) . '
								</div>';
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

	private function getGames($language_uid=null, $section_uid=null) {
		$html = '';
		if ($language_uid != null && $section_uid != null) {
			$objPackageGames = new schooladmin_package_games();

			$objPackageSections = new schooladmin_package($this->parts[4]);
			if ($objPackageSections->get_valid()) {
				$objPackageSections->load();
			}			
			$reseller_package_uid = $objPackageSections->get_reseller_package_uid();
			$reseller_sub_package_uid = $objPackageSections->get_reseller_sub_package_uid();
			$packuid = 0;
			$table_name = 'reseller_sub_package';
			if ($reseller_sub_package_uid > 0) {
				$table_name = 'reseller_sub_package';
				$packuid = $reseller_sub_package_uid;
			} else {
				$table_name = 'reseller_package';
				$packuid = $reseller_package_uid;
			}
			$school_uid = $_SESSION['user']['uid'];

			$query = "SELECT ";
			$query.="`uid`, ";
			$query.="`name` ";
			$query.="FROM ";
			$query.=" `game` ";
			$query.=" WHERE ";
			$query.=" uid IN ( ";
			$query.=" SELECT game_uid FROM {$table_name}_games";
			$query.=" WHERE ";
			$query.=" package_uid = '{$packuid}'";
			$query.=" AND learnable_language_uid='{$language_uid}'";
			$query.=" AND section_uid='{$section_uid}'";
			$query.=" ) ";
			$query.="ORDER BY `game_number`";
			
			$result = database::arrQuery($query);
			if (count($result)>0) {
				foreach($result as $row) {
					$html.='<p><label for="game-' . $language_uid . '-' . $row['uid'] . '">';
					$html.='<input type="checkbox" value="' . $row['uid'] . '" ';
					$html.='id="game-' . $language_uid . '-' . $row['uid'] . '" ';
					$html.='name="game[' . $language_uid . '_' . $section_uid . '_' . $row['uid'] . ']" ';
					$html.=$objPackageGames->checkExist($this->parts[4], $language_uid, $section_uid, $row['uid']) . '/> ';
					$html.=$row['name'];
					$html.=' </label></p>';
				}
			}
		}
		return $html;
	}

	private function getYears($learnable_language_uid=null) {
		if ($learnable_language_uid != null) {
			$l_uid = $learnable_language_uid;
			$query = "SELECT ";
			$query.="`name`, ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`years` ";
			$query.="ORDER BY `position`";
			$result = database::arrQuery($query);
			$arrLi = array();
			$arrDiv = array();
			if (count($result) > 0) {
				foreach ($result as $row) {
					$arrLi[] = '<li><a href="#year-' . $l_uid . '-' . $row['uid'] . '"><span>' . $row['name'] . '</span></a></li>';
					$arrDiv[] = '<div id="year-' . $l_uid . '-' . $row['uid'] . '">
									' . $this->getUnitTabs($row['uid'], $learnable_language_uid) . '
								</div>';
				}
			}
			return make::tpl('body.account.package.tabs.inner')->assign(
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
			$query = "SELECT ";
			$query.="`uid`,";
			$query.="`name`,";
			$query.="`unit_number` ";
			$query.="FROM ";
			$query.="`units` ";
			$query.="WHERE ";
			$query.="`active` = '1' ";
			$query.="AND ";
			$query.="`year_uid` = '" . $year_uid . "'";
			$result = database::arrQuery($query);
			$arrLi = array();
			$arrDiv = array();
			if (count($result) > 0) {
				foreach ($result as $row) {
					$arrLi[] = '<li><a href="#unit-' . $l_uid . '-' . $row['uid'] . '"><span>Unit ' . $row['unit_number'] . '</span></a></li>';
					$arrDiv[] = '<div id="unit-' . $l_uid . '-' . $row['uid'] . '">
									' . $row['name'] . '<br/>' . $this->getSections($row['uid'], $year_uid, $learnable_language_uid) . '
								</div>';
				}
			}
			return make::tpl('body.account.package.tabs.inner')->assign(
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

			$objPackageSections = new schooladmin_package($this->parts[4]);
			if ($objPackageSections->get_valid()) {
				$objPackageSections->load();
			}

			$l_uid = $learnable_language_uid;
			$reseller_package_uid = $objPackageSections->get_reseller_package_uid();
			$reseller_sub_package_uid = $objPackageSections->get_reseller_sub_package_uid();
			$packuid = 0;
			$table_name = 'reseller_sub_package';
			if ($reseller_sub_package_uid > 0) {
				$table_name = 'reseller_sub_package';
				$packuid = $reseller_sub_package_uid;
			} else {
				$table_name = 'reseller_package';
				$packuid = $reseller_package_uid;
			}
			$school_uid = $_SESSION['user']['uid'];
			$query = "SELECT ";
			$query.="`uid`,";
			$query.="`name`,";
			$query.="`section_number` ";
			$query.="FROM ";
			$query.="`sections` ";
			$query.="WHERE ";
			$query.="`active` = '1' ";
			$query.="AND ";
			$query.="uid IN (";
			$query.=" SELECT `section_uid` FROM `{$table_name}_sections`						
						WHERE 
						`package_uid` = '{$packuid}'
						";
			$query.=")";
			$query.="AND ";
			$query.="`unit_uid` = '" . $unit_uid . "'";

			$result = database::arrQuery($query);
			if (count($result) > 0) {
				foreach ($result as $row) {
					$html.='<p><label for="section-' . $l_uid . '-' . $row['uid'] . '">';
					$html.='<input type="checkbox" value="' . $row['uid'] . '" ';
					$html.='id="section-' . $l_uid . '-' . $row['uid'] . '" ';
					$html.='name="section[' . $l_uid . '_' . $year_uid . '_' . $unit_uid . '_' . $row['uid'] . ']" ';
					$html.=$objPackageSections->checkExist($this->parts[4], $l_uid, $row['uid']) . '/> ';
					$html.=$row['name'];
					$html.=' </label></p>';
				}
			}
		}
		return $html;
	}

	private function getPriceForm($package_uid=null) {
		$query = "SELECT ";
		$query.="`prefix` AS `locale`, ";
		$query.="( ";
		$query.="SELECT ";
		$query.="`price` ";
		$query.="FROM ";
		$query.="`schooladmin_sub_package_price` ";
		$query.="WHERE ";
		$query.="`schooladmin_sub_package_price`.`locale` = `language`.`prefix` ";
		$query.="AND ";
		$query.="`package_uid` = '" . $package_uid . "'";
		$query.="LIMIT 1";
		$query.=") AS `price`, ";
		$query.="( ";
		$query.="SELECT ";
		$query.="`vat` ";
		$query.="FROM ";
		$query.="`schooladmin_sub_package_price` ";
		$query.="WHERE ";
		$query.="`schooladmin_sub_package_price`.`locale` = `language`.`prefix` ";
		$query.="AND ";
		$query.="`package_uid` = '" . $package_uid . "'";
		$query.="LIMIT 1";
		$query.=") AS `vat` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";
		$result = database::query($query);
		if (mysql_error() == '' && mysql_num_rows($result)) {
			$arrLi = array();
			$arrDiv = array();
			while ($row = mysql_fetch_array($result)) {
				if (($package_uid == null || is_null($row['price']) || $row['price'] == 0) && isset($_POST['price'][$row['locale']])) {
					$row['price'] = $_POST['price'][$row['locale']];
				}

				if (($package_uid == null || is_null($row['vat']) || $row['vat'] == 0) && isset($_POST['vat'][$row['locale']])) {
					$row['vat'] = $_POST['vat'][$row['locale']];
				}

				$arrLi[] = '<li><a href="#locale-' . $row['locale'] . '"><span>' . $row['locale'] . '</span></a></li>';
				$arrDiv[] = make::tpl('body.account.package.price')->assign($row)->get_content();
			}
			return make::tpl('body.account.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getSupportLanguages($packageUid, $support_language_uid=null) {
		$arrSupportLanguages = "";
		$query = "SELECT ";
		$query.="`support_language_uid` ";
		$query.=" FROM ";
		$query.=" `schooladmin_package` ";
		$query.=" WHERE ";
		$query.=" uid = '{$packageUid}' ";
		$arrLanguage = database::arrQuery($query);
		$iCount = 0;
		if (count($arrLanguage) && is_array($arrLanguage)) {
			foreach ($arrLanguage as $arr) {
				$arrSupportLanguages .= '<input name="support_language_uid" type="hidden" value="' . $arr["support_language_uid"] . '" />';
			}
		}
		return $arrSupportLanguages;
	}

	private function getLearnableLanguages($resellerpackageUid, $arrLearnable = array()) {
		$arrLearnableLanguages = array();
		$query = "SELECT ";
		$query.="`name`, ";
		$query.="`uid` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="WHERE ";
		$query.="`uid` IN (";
		$query.="SELECT ";
		$query.=" learnable_language_uid";
		$query.=" FROM ";
		$query.="`reseller_package_language` ";
		$query.="WHERE ";
		$query.=" package_uid IN ( ";
		$query.=" SELECT uid FROM";
		$query.=" `reseller_package`";
		$query.=" WHERE";
		$query.=" uid='{$resellerpackageUid}'";
		$query.=" ) ";
		$query.=" ) ";
		$query.="ORDER BY `name` ";

		$arrLanguage = database::arrQuery($query);

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
				$arrLearnableLanguages[] = make::tpl('body.account.package.available_language')->assign($arr)->get_content();
			}
		}
		return $arrLearnableLanguages;
	}

	protected function doSections() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.schooladmin_sub_package.tab');

		$school_uid = $_SESSION['user']['uid'];
		$package_uid = $this->parts[4];
		$body->assign('school_uid', $school_uid);
		$body->assign('package_uid', $package_uid);

		if (isset($_POST['submit'])) {
			$objPackageSections = new schooladmin_package();
			$objPackageSections->saveSections();
			if (isset($school_uid)) {
				if (!isset($_SESSION['section_save_success'])) {
					$_SESSION['section_save_success'] = 1;
				}
				output::redirect(config::url('account/schooladmin_sub_package/'));
			} else {
				output::redirect(config::url('account/schooladmin_sub_package/'));
			}
		}
		if (isset($package_uid) && is_numeric($package_uid)) {
			$objPackage = new schooladmin_package($package_uid);

			if ($objPackage->get_valid()) {
				$objPackage->load();
				$query = "SELECT ";
				$query.="`L`.`name`, ";
				$query.="`L`.`uid` ";
				$query.="FROM ";
				$query.="`language` AS `L`, ";
				$query.="`schooladmin_package_language` AS `PL`,";
				$query.="`schooladmin_package` AS `SP`";
				$query.="WHERE ";
				$query.="`PL`.`learnable_language_uid` = `L`.`uid` ";
				$query.=" AND ";
				$query.="`PL`.`package_uid` = '{$package_uid}' ";
				$query.=" AND ";
				$query.="`SP`.`school_uid` = '{$school_uid}' ";
				$query.=" AND ";
				$query.="`SP`.`uid` = `PL`.`package_uid` ";
				$query.=" ORDER BY `L`.`name`";
				$result = database::arrQuery($query);
				$arrLi = array();
				$arrDiv = array();
				if (count($result) > 0) {
					foreach ($result as $row) {
						$arrLi[] = '<li><a href="#language-' . $row['uid'] . '"><span>' . $row['name'] . '</span></a></li>';
						$arrDiv[] = '<div id="language-' . $row['uid'] . '">
										' . $this->getYears($row['uid']) . '
									</div>';
					}
				}
				$display = "style='display:none;'";
				if (isset($_SESSION['section_save_success'])) {
					$display = '';
					unset($_SESSION['section_save_success']);
				}
				$body->assign(
						array(
							'tabs.lis' => implode('', $arrLi),
							'tabs.divs' => implode('', $arrDiv),
							'package_uid' => $this->parts[3],
							'name' => $objPackage->get_name(),
							'display' => $display
						)
				);
				$skeleton->assign(
						array(
							'body' => $body
						)
				);
				output::as_html($skeleton, true);
			} else {
				output::redirect(config::url('account/schooladmin_sub_package/'));
			}
		} else {
			output::redirect(config::url('account/schooladmin_sub_package/'));
		}
	}

	protected function doGamesandactivities() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.schooladmin_sub_package.gamesandactivities.tabs');
		$school_uid = $_SESSION['user']['uid'];
		$package_uid = $this->parts[4];

		if (isset($package_uid) && is_numeric($package_uid)) {
			$objPackage = new schooladmin_package($package_uid);
			if ($objPackage->get_valid()) {
				$objPackage->load();
				if (isset($_POST['submit'])) {
					$objPackageGames = new schooladmin_package_games();
					$objPackageGames->saveGames();

					$objPackageActivity = new schooladmin_package_activity();
					$objPackageActivity->saveActivity();

					if (isset($package_uid)) {
						if (!isset($_SESSION['option_save_success'])) {
							$_SESSION['option_save_success'] = 1;
						}
						output::redirect(config::url('account/schooladmin_sub_package/'));
					} else {
						output::redirect(config::url('account/schooladmin_sub_package/'));
					}
				}

				$body->assign('school_uid', $school_uid);
				$body->assign('package_uid', $package_uid);
				$display = "style='display:none;'";
				if (isset($_SESSION['option_save_success'])) {
					$display = '';
					unset($_SESSION['option_save_success']);
				}

				// for skilltype
				$skillTypeLi = "";
				$skillTypeDiv = "";
				$skill_levels = activity_skill::getAllActivitySkills();
				if (!empty($skill_levels)) {
					foreach ($skill_levels as $uid => $data) {
						$data["name_id"] = strtolower(str_replace(" ", "_", $data["name"]));

						$skillTypeLi.='<li><a href="#' . $data["name_id"] . '"><span>' . $data["name"] . '</span></a></li>';

						$skillTypeDiv.='<div id="' . $data["name_id"] . '">';
						$skillTypeDiv.=$this->getActivityContent($package_uid, $data['uid']);
						$skillTypeDiv.='</div>';
					}
				}

				$body->assign(
						array(
							'div.games' => $this->getGameContent($package_uid),
							'name' => $objPackage->get_name(),
							'display' => $display,
							'package_uid' => $package_uid,
							'skill_type_li' => $skillTypeLi,
							'skill_type_div' => $skillTypeDiv
						)
				);
				$skeleton->assign(
						array(
							'body' => $body
						)
				);
				output::as_html($skeleton, true);
			} else {
				output::redirect(config::url('account/schooladmin_sub_package/'));
			}
		} else {
			output::redirect(config::url('account/schooladmin_sub_package/'));
		}
	}

	protected function doDelete() {
		if (isset($this->parts[4]) && (int) $this->parts[4] > 0) {
			$school_uid = $_SESSION['user']['uid'];
			$objPackage = new schooladmin_package($this->parts[4]);
			$objPackage->softDelete($this->parts[4], $school_uid);
			$objPackage->redirectTo('account/schooladmin_sub_package/');
		} else {
			output::redirect(config::url('account/schooladmin_sub_package/'));
		}
	}

	// function for skill type

	private function getActivityContent($schooladmin_package_uid=null, $skill_type=null) {
		if ($schooladmin_package_uid != null && $skill_type != null) {
			$html = $this->getschooladminPackageLearnableLanguagesForSkillType($schooladmin_package_uid, $skill_type, 'getActivity');
			return $html;
		}
		return ' ';
	}

	private function getschooladminPackageLearnableLanguagesForSkillType($schooladmin_package_uid=null, $skill_type=null, $endMethod=null) {
		if ($schooladmin_package_uid != null && $endMethod != null) {

			$objPackage = new schooladmin_package($schooladmin_package_uid);
			$objPackage->load();
			$reseller_package_uid = $objPackage->get_reseller_package_uid();
			$reseller_sub_package_uid = $objPackage->get_reseller_sub_package_uid();
			$packuid = 0;
			$school_uid = $_SESSION['user']['uid'];
			$table_name = 'reseller_sub_package';
			if ($reseller_sub_package_uid > 0) {
				$table_name = 'reseller_sub_package';
				$packuid = $reseller_sub_package_uid;
			} else {
				$table_name = 'reseller_package';
				$packuid = $reseller_package_uid;
			}

			$query = "SELECT ";
			$query.="`L`.`name`, ";
			$query.="`L`.`uid` ";
			$query.="FROM ";
			$query.="`language` AS `L`, ";
			$query.=" `{$table_name}_language` AS `PL` ";
			$query.=" WHERE ";
			$query.="`PL`.`learnable_language_uid` = `L`.`uid` ";
			$query.=" AND ";
			$query.="`PL`.`package_uid` = '{$packuid}'";
			$query.=" ORDER BY `L`.`name`";
			$result = database::arrQuery($query);			
			
			$arrLi = array();
			$arrDiv = array();
			if (count($result) > 0) {
				foreach ($result as $row) {
					$arrLi[] = '<li><a href="#Alanguage-' . $row['uid'] . '_' . $skill_type . '"><span>' . $row['name'] . '</span></a></li>';
					$arrDiv[] = '<div id="Alanguage-' . $row['uid'] . '_' . $skill_type . '">
									' . $this->getschooladminPackageYearsForSkillType($row['uid'], $schooladmin_package_uid, $skill_type, $endMethod) . '
								</div>';
				}
			}
			// return  make::tpl('body.admin.schooladmin_package.tabs.inner')->assign(
			return make::tpl('body.admin.package.tabs.inner')->assign(
					array(
						'tabs.lis' => implode('', $arrLi),
						'tabs.divs' => implode('', $arrDiv)
					)
			)->get_content();
		}
		return ' ';
	}

	private function getschooladminPackageYearsForSkillType($language_uid=null, $schooladmin_package_uid=null, $skill_type=null, $endMethod=null) {
		if ($language_uid != null && $schooladmin_package_uid != null && $endMethod != null) {
			$package_uid = $this->parts[4];

			$query = "SELECT ";
			$query.=" DISTINCT `Y`.`uid`, ";
			$query.=" `Y`.`name` ";
			$query.=" FROM ";
			$query.="`years` AS `Y`, ";
			$query.="`schooladmin_package_sections` AS `PS`";
			$query.=" WHERE ";
			$query.="`PS`.`year_uid` = `Y`.`uid` ";
			$query.=" AND `PS`.`package_uid` ='{$package_uid}'";
			$query.=" AND ";
			$query.=" `PS`.`learnable_language_uid` = '" . $language_uid . "'";
			$query.=" ORDER BY `Y`.`position`"; //exit;

			$result = database::arrQuery($query);
			$arrLi = array();
			$arrDiv = array();
			if (count($result) > 0) {
				foreach ($result as $row) {
					$arrLi[] = '<li><a href="#Ayear-' . $language_uid . '_' . $row['uid'] . '_' . $skill_type . '"><span>' . $row['name'] . '</span></a></li>';
					$arrDiv[] = '<div id="Ayear-' . $language_uid . '_' . $row['uid'] . '_' . $skill_type . '">
									' . $this->getschooladminPackageUnitsForSkillType($language_uid, $schooladmin_package_uid, $row['uid'], $skill_type, $endMethod) . '
								</div>';
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

	private function getschooladminPackageUnitsForSkillType($language_uid=null, $schooladmin_package_uid=null, $year_uid=null, $skill_type=null, $endMethod=null) {
		if ($language_uid != null && $schooladmin_package_uid != null && $year_uid != null && $endMethod != null) {
			$package_uid = $this->parts[4];

			$query = "SELECT ";
			$query.=" DISTINCT `U`.`uid`, ";
			$query.=" `U`.`name`, ";
			$query.=" `U`.`unit_number` ";
			$query.=" FROM ";
			$query.=" `units` AS `U`, ";
			$query.=" `schooladmin_package_sections` AS `PS`";
			$query.=" WHERE ";
			$query.="`PS`.`unit_uid` = `U`.`uid` ";
			$query.=" AND `PS`.`package_uid` = '{$package_uid}' ";
			$query.=" AND ";
			$query.="`PS`.`year_uid` = '" . $year_uid . "'";
			$query.=" AND ";
			$query.="`PS`.`learnable_language_uid` = '" . $language_uid . "'";
			$query.=" ORDER BY `U`.`unit_number`";
			$result = database::arrQuery($query);
			$arrLi = array();
			$arrDiv = array();
			if (count($result) > 0) {
				foreach ($result as $row) {
					$arrLi[] = '<li><a href="#Aunits-' . $language_uid . '_' . $row['uid'] . '_' . $skill_type . '"><span>Unit ' . $row['unit_number'] . '</span></a></li>';
					$arrDiv[] = '<div id="Aunits-' . $language_uid . '_' . $row['uid'] . '_' . $skill_type . '">
									' . $row['name'] . '<br/>' . $this->getActivity($language_uid, $row['uid'], $skill_type, $endMethod) . '
								</div>';
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

	private function getActivity($language_uid=null, $unit_uid=null, $skill_type=null, $endMethod=null) {
		$objschooladmin_packageActivity = new schooladmin_package_activity();
		if ($unit_uid != null && $skill_type != null) {

			$package_uid = $this->parts[4];
			$objPackage = new schooladmin_package($package_uid);
			$objPackage->load();
			$reseller_package_uid = $objPackage->get_reseller_package_uid();
			$reseller_sub_package_uid = $objPackage->get_reseller_sub_package_uid();
			$packuid = 0;
			$school_uid = $_SESSION['user']['uid'];
			$table_name = 'reseller_sub_package';
			if ($reseller_sub_package_uid > 0) {
				$table_name = 'reseller_sub_package';
				$packuid = $reseller_sub_package_uid;
			} else {
				$table_name = 'reseller_package';
				$packuid = $reseller_package_uid;
			}

			$query = "SELECT * FROM";
			$query.=" `activity` ACT";
			$query.=" WHERE ";
			$query.=" ACT.uid IN (";

			$query.=" SELECT activity_uid FROM";
			$query.=" {$table_name}_activity RPA ";
			$query.=" WHERE ";
			$query.=" RPA.package_uid = '{$packuid}'";

			$query.=" )";
			$query.=" AND ACT.unit_uid='{$unit_uid}'";
			$query.=" AND ACT.skill_level_uid='{$skill_type}'";

			$result = database::arrQuery($query);
			$html = "";

			if (count($result) > 0) {
				foreach ($result as $row) {
					$html.='<p><label for="activity-' . $unit_uid . '-' . $row['uid'] . '">';
					$html.='<input type="checkbox" value="' . $row['uid'] . '" ';
					$html.='id="activity-' . $unit_uid . '-' . $row['uid'] . '" ';
					$html.='name="activity[' . $unit_uid . '_' . $skill_type . '_' . $row['uid'] . '_' . $language_uid . ']" ';
					$html.=$objschooladmin_packageActivity->checkExist($package_uid, $language_uid, $row['uid']) . '/> ';
					$html.=$row['name'];
					$html.=' </label></p>';
				}
			}

			return $html;
		}
		return ' ';
	}

// end function for skill type
}

?>