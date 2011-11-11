<?php

class account_reseller_sub_package extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete',
		'sections',
		'gamesandactivities'
	);
	private $arrPaths		= array();
	private $json_languages	= array(0);
	private $json_years		= array();
	private $json_units		= array();
	private $json_sections	= array();
	private $json_section_uids	= array();
	private $json_games		= array();

	private $parts = array();
	private $objreseller_sub_package = null;
	private $objResellerPackage = null;

	private $reseller_uid = null;

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
	protected function doAdd() {
		if(isset($this->arrPaths[3]) && isset($this->reseller_uid) && is_numeric($this->arrPaths[3]) && is_numeric($this->reseller_uid) ) {
			if($this->verifyParentPackage($this->arrPaths[3])===true) {

				$skeleton = config::getUserSkeleton();
				$body	 = make::tpl('body.account.reseller.sub_package.add');

				$body->assign('reseller_uid',$this->reseller_uid);
				$body->assign('package_uid', $this->objResellerPackage->get_uid());
				$body->assign('support_language_uid', $this->objResellerPackage->get_support_language_uid());


				$support_language_uid = 0;
				$arrLearnable = array();
				if (count($_POST) > 0) {
					$objPackage = new reseller_sub_package();
					if (($response = $objPackage->isValidCreate()) === true) {
						output::redirect(config::url('account/sub_packages/list/' . $this->arrPaths[3]));
					} else {
						if (isset($_POST['learnable_language_uid'])) {
							$arrLearnable = $_POST['learnable_language_uid'];
						}
						$body->assign($objPackage->arrForm);
					}
				}

				$arrLearnableLanguages = $this->getLearnableLanguages($this->objResellerPackage,$arrLearnable);
				$body->assign(
						array(
							'learnable_languages' => implode("", $arrLearnableLanguages)
						)
				);

				$body->assign(
						array(
							'support_languages' => $this->getSupportLanguages($this->objResellerPackage)
						)
				);

				$body->assign(
						array(
							'package.price' => $this->getPriceForm()
						)
				);

				$skeleton->assign(
						array(
							'body' => $body
						)
				);
				output::as_html($skeleton, true);
				exit;
			} else {
				// redirect back
			}
		} else {
			// redirect back
		}
	}

	protected function doEdit() {
		if(isset($this->arrPaths[4]) && is_numeric($this->arrPaths[4])) {

			if($this->verifyParentPackage($this->arrPaths[4])===true) {
				$skeleton = config::getUserSkeleton();
				$body = make::tpl('body.account.reseller.sub_package.edit');
				$uid = $this->arrPaths[3];
				$objResellerSubPackage = new reseller_sub_package($uid);
				if ($objResellerSubPackage->get_valid()) {
					$objResellerSubPackage->load();
					$this->ParsePackage($objResellerSubPackage);
				}

				$body->assign('uid', $uid);
				$body->assign('reseller_uid',$this->objResellerPackage->get_reseller_uid());
				$body->assign('package_uid', $this->objResellerPackage->get_uid());
				$body->assign('support_language_uid', $this->objResellerPackage->get_support_language_uid());

				$support_language_uid = 0;
				$arrLearnable = array();
				if (count($_POST) > 0) {
					$objPackage = new reseller_sub_package();
					if (($response = $objPackage->isValidUpdate()) === true) {
						output::redirect(config::url('account/sub_packages/list/' . $this->arrPaths[4]));
					} else {
						if (isset($objPackage->arrForm['support_language_uid'])) {
							$support_language_uid = $objPackage->arrForm['support_language_uid'];
						}
						if (isset($_POST['learnable_language_uid'])) {
							$arrLearnable = $_POST['learnable_language_uid'];
						}
						$body->assign($objPackage->arrForm);
					}
				} else {
					foreach ($objResellerSubPackage->TableData as $idx => $val) {
						$arrBody[$idx] = $val['Value'];
					}
					$support_language_uid = $arrBody['support_language_uid'];
					$arrLearnable = array();
					$arrLearnable = $this->json_languages;
					$pricing_json = $arrBody['pricing'];
					$body->assign($arrBody);
				}

				$arrLearnableLanguages = $this->getLearnableLanguages($this->objResellerPackage,$arrLearnable);
				$body->assign(
					array(
						'learnable_languages' => implode("", $arrLearnableLanguages)
					)
				);

				$body->assign(
					array(
						'support_languages' => $this->getSupportLanguages($this->objResellerPackage)
					)
				);

				$body->assign(
					array(
						'package.price' => $this->getPriceForm($uid,$pricing_json)
					)
				);

				$skeleton->assign(
					array(
						'body' => $body
					)
				);
				output::as_html($skeleton, true);
			} else {
				// redirect back
			}
		} else {
			// redirect back
		}
	}

	protected function doList() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.reseller.sub.package.list');
		$objResellerPackage = new reseller_sub_package();
		$arrPackages = $objResellerPackage->getList($this->reseller_uid,$this->arrPaths[3]);

		if ($arrPackages && count($arrPackages) > 0) {
			$rows = array();
			foreach ($arrPackages as $uid => $arrData) {
				$arrData['created_date'] = date('d/m/Y H:i:s', strtotime($arrData['created_date']));
				$row = make::tpl('body.account.reseller.sub.package.list.row')->assign($arrData);
				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}
		$body->assign('package_uid', $this->arrPaths[3]);
		$page_display_title = $objResellerPackage->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $objResellerPackage->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $objResellerPackage->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $objResellerPackage->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title', $page_display_title);
		$body->assign('page.navigation', $page_navigation);
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

	private function getSupportLanguages($objResellerPackage=null) {
		if($objResellerPackage!=null) {
			$objLanguage = new language($objResellerPackage->get_support_language_uid());
			if($objLanguage->get_valid()) {
				$objLanguage->load();
				return $objLanguage->get_name();
			}
			return ' ';
		}
		return ' ';
	}

	private function getLearnableLanguages($objResellerPackage=null, $arrLearnable = array()) {
		$arrLearnableLanguages = array();
		if(!isset($objResellerPackage->json_languages) || !is_array($objResellerPackage->json_languages)) {
			return $arrLearnableLanguages;
		}
		$query = "SELECT ";
		$query.="`name`, ";
		$query.="`uid` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.="WHERE ";
		$query.="`uid` IN (".implode(',',$objResellerPackage->json_languages).") ";
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
				$arrLearnableLanguages[] = make::tpl('body.admin.package.available_language')->assign($arr)->get_content();
			}
		}
		return $arrLearnableLanguages;
	}


	protected function doSections() {
		if (isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$objPackage = new reseller_sub_package($this->arrPaths[3]);
			if($objPackage->get_valid()) {
				$objPackage->load();

				if (isset($_POST['submit'])) {
					$objPackage->SavePackageSections(
						$objPackage->get_uid(),
						$_POST['section']
					);
					output::redirect(config::url('account/sub_packages/sections/' . $this->arrPaths[3] . '/' . $this->arrPaths[4] . '/'));
				}

					$this->ParsePackage($objPackage);
					if(isset($this->arrPaths[4]) && is_numeric($this->arrPaths[4])) {
					if($this->verifyParentPackage($this->arrPaths[4])===true) {
						$skeleton = config::getUserSkeleton();
						$body = make::tpl('body.account.reseller.sub_package.sections');
						$body->assign('uid', $objPackage->get_uid());
						$body->assign('reseller_uid',$this->objResellerPackage->get_reseller_uid());
						$body->assign('parent_package_uid', $this->objResellerPackage->get_uid());
						$body->assign('support_language_uid', $this->objResellerPackage->get_support_language_uid());

						$query = "SELECT ";
						$query.="`L`.`name`, ";
						$query.="`L`.`uid` ";
						$query.="FROM ";
						$query.="`language` AS `L` ";
						$query.="WHERE ";
						$query.="`L`.`uid` IN (".implode(',',$this->json_languages).") ";
						$query.=" ORDER BY `L`.`name`";
						$result = database::query($query);
						$arrLi = array();
						$arrDiv = array();
						if (mysql_num_rows($result)) {
							while ($row = mysql_fetch_array($result)) {

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
						$body->assign(
							array(
								'tabs.lis' => implode('', $arrLi),
								'tabs.divs' => implode('', $arrDiv)
							)
						);
						$skeleton->assign(
							array(
								'body' => $body
							)
						);
						output::as_html($skeleton, true);

					} else {
						// redirect on some place.
						$this->RedirectToList();
					}
				} else {
					// redirect on some place.
					$this->RedirectToList();
				}
			} else {
				// redirect on some place.
				$this->RedirectToList();
			}
		} else {
			// redirect on some place.
			$this->RedirectToList();
		}
	}

	private function getYears($learnable_language_uid=null) {
		if ($learnable_language_uid != null && isset($this->objResellerPackage->json_years[$learnable_language_uid]) && is_array($this->objResellerPackage->json_years[$learnable_language_uid])) {
			$l_uid = $learnable_language_uid;
			$query = "SELECT ";
			$query.="`name`, ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`years` ";
			$query.="WHERE ";
			$query.="`uid` IN(".implode(',',$this->objResellerPackage->json_years[$l_uid]).") ";
			$query.="ORDER BY `position`";
			$result = database::query($query);
			$arrLi = array();
			$arrDiv = array();
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {

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
		$l_uid = $learnable_language_uid;
		if ($year_uid != null && $learnable_language_uid != null && isset($this->objResellerPackage->json_units[$l_uid][$year_uid]) && is_array($this->objResellerPackage->json_units[$l_uid][$year_uid])) {
			$query = "SELECT ";
			$query.="`uid`,";
			$query.="`name`,";
			$query.="`unit_number` ";
			$query.="FROM ";
			$query.="`units` ";
			$query.="WHERE ";
			$query.="`active` = '1' ";
			$query.="AND ";
			$query.="`uid` IN (".implode(',',$this->objResellerPackage->json_units[$l_uid][$year_uid]).") ";
			$result = database::query($query);
			$arrLi = array();
			$arrDiv = array();
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {
					$arrLi[] = '<li><a href="#unit-' . $l_uid . '-' . $row['uid'] . '"><span>Unit ' . $row['unit_number'] . '</span></a></li>';
					$arrDiv[] = '<div id="unit-' . $l_uid . '-' . $row['uid'] . '">
									' . $row['name'] . '<br/>' . $this->getSections($row['uid'], $year_uid, $learnable_language_uid) . '
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

	private function getSections($unit_uid=null, $year_uid=null, $learnable_language_uid=null) {
		$html = ' ';
		$l_uid = $learnable_language_uid;
		if ($year_uid != null && $unit_uid != null && $learnable_language_uid != null && isset($this->objResellerPackage->json_section_uids[$l_uid][$unit_uid]) && is_array($this->objResellerPackage->json_section_uids[$l_uid][$unit_uid])) {

			$query = "SELECT ";
			$query.="`uid`,";
			$query.="`name`,";
			$query.="`section_number` ";
			$query.="FROM ";
			$query.="`sections` ";
			$query.="WHERE ";
			$query.="`active` = '1' ";
			$query.="AND ";
			$query.="`uid` IN(".implode(',',$this->objResellerPackage->json_section_uids[$l_uid][$unit_uid]).") ";
			$result = database::query($query);
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {

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
							'checked'	=>$checked
						)
					)->get_content();
				}
			}
		}
		return $html;
	}

	protected function doGamesandactivities() {
		if (isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$objPackage = new reseller_sub_package($this->arrPaths[3]);
			if($objPackage->get_valid()) {
				$objPackage->load();

				if (isset($_POST['submit'])) {
					$objPackage->SavePackageGames(
						$objPackage->get_uid(),
						$_POST['game']
					);
					output::redirect(config::url('account/sub_packages/gamesandactivities/' . $this->arrPaths[3] . '/' . $this->arrPaths[4] . '/'));
				}

				$this->ParsePackage($objPackage);
				if(isset($this->arrPaths[4]) && is_numeric($this->arrPaths[4])) {
					if($this->verifyParentPackage($this->arrPaths[4])===true) {
						$skeleton = config::getUserSkeleton();
						$body = make::tpl('body.account.sub_package.gamesandactivities');

						$body->assign('uid', $objPackage->get_uid());
						$body->assign('reseller_uid',$this->objResellerPackage->get_reseller_uid());
						$body->assign('parent_package_uid', $this->objResellerPackage->get_uid());
						$body->assign('support_language_uid', $this->objResellerPackage->get_support_language_uid());

						$body->assign(
							array(
								'div.games' => $this->getGameContent($objPackage->get_uid())
							)
						);
						$skeleton->assign(
							array(
								'body' => $body
							)
						);
						output::as_html($skeleton, true);

					} else {
						// redirect on some place
						$this->RedirectToList();
					}
				} else {
					// redirect on some place
					$this->RedirectToList();
				}
			} else {
				// redirect on some place
				$this->RedirectToList();
			}
		} else {
			// redirect on some place
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
			$query = "SELECT ";
			$query.="`L`.`name`, ";
			$query.="`L`.`uid` ";
			$query.="FROM ";
			$query.="`language` AS `L` ";
			$query.="WHERE ";
			$query.="`L`.`uid` IN (".implode(',',$this->json_languages).") ";
			$query.=" ORDER BY `L`.`name`";
			$result = database::query($query);
			$arrLi = array();
			$arrDiv = array();
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {

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
			$query = "SELECT ";
			$query.=" DISTINCT `Y`.`uid`, ";
			$query.=" `Y`.`name` ";
			$query.=" FROM ";
			$query.="`years` AS `Y` ";
			$query.=" WHERE ";
			$query.="`Y`.`uid` IN (".implode(',',$this->json_years[$language_uid]).") ";
			$query.=" ORDER BY `Y`.`position`";
			$result = database::query($query);
			$arrLi = array();
			$arrDiv = array();
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {

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
			$query = "SELECT ";
			$query.="DISTINCT `U`.`uid`, ";
			$query.="`U`.`name`, ";
			$query.="`U`.`unit_number` ";
			$query.="FROM ";
			$query.="`units` AS `U` ";
			$query.="WHERE ";
			$query.="`U`.`uid` IN (".implode(',',$this->json_units[$language_uid][$year_uid]).") ";
			$query.=" ORDER BY `U`.`unit_number`";
			$result = database::query($query);
			$arrLi = array();
			$arrDiv = array();
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {

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
			$query = "SELECT ";
			$query.="`S`.`name`, ";
			$query.="`S`.`uid`, ";
			$query.="`S`.`section_number` ";
			$query.="FROM ";
			$query.="`sections` AS `S` ";
			$query.="WHERE ";
			$query.="`S`.`uid` IN (".implode(',',$this->json_section_uids[$language_uid][$unit_uid]).") ";
			$query.=" ORDER BY `S`.`section_number`";
			$result = database::query($query);
			$arrLi = array();
			$arrDiv = array();
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {

					$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
						array(
							'link_href'	=>'#section-' . $language_uid . '_' . $unit_uid . '_' . $row['uid'],
							'lable'		=>'Section ' . $row['section_number']
						)
					)->get_content();

					$arrDiv[] = make::tpl('body.general.tabs.div')->assign(
						array(
							'tab_id'		=>'section-' . $language_uid . '_' . $unit_uid . '_' . $row['uid'],
							'tab_content'	=>$this->$endMethod($language_uid, $row['uid'],$unit_uid),
							'other_text'	=>$row['name'] . '<br/>'
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

	private function getGames($language_uid=null, $section_uid=null, $unit_uid=null) {
		$html = '';
		if ($language_uid != null && $section_uid != null && $unit_uid != null && isset($this->objResellerPackage->games[$language_uid][$unit_uid][$section_uid]) && is_array($this->objResellerPackage->games[$language_uid][$unit_uid][$section_uid])) {
			$arrGames = $this->objResellerPackage->games[$language_uid][$unit_uid][$section_uid];
			$query = "SELECT ";
			$query.="`uid`, ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`game` ";
			$query.="WHERE ";
			$query.="`uid` IN (".implode(',',$arrGames).")";
			$query.="ORDER BY `game_number`";
			$result = database::query($query);
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {
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
							'checked'	=>$checked
						)
					)->get_content();
				}
			}
		}
		return $html;
	}

	protected function doDelete() {
		if (isset($this->arrPaths[3]) && (int) $this->arrPaths[3] > 0) {
			$objPackage = new reseller_sub_package($this->arrPaths[3]);
			//$objPackage->softDelete($this->arrPaths[3]);
			$objPackage->redirectTo('admin/reseller_sub_package/list/' . $this->arrPaths[4] . '/' . $this->arrPaths[5] . '/');
		} else {
			output::redirect(config::url('admin/reseller_sub_package/list/' . $this->arrPaths[4] . '/' . $this->arrPaths[5] . '/'));
		}
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
					}
				}
			}
		}
	}

	private function RedirectToList() {
		if(isset($this->arrPaths[4]) && isset($this->arrPaths[5]) && is_numeric($this->arrPaths[4]) && is_numeric($this->arrPaths[5])) {
			output::redirect(config::url('admin/reseller_sub_package/list/' . $this->arrPaths[4] . '/' . $this->arrPaths[5] . '/'));
		}
	}
}

?>