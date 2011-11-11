<?php

class account_schoolteacher_package extends Controller {

	private $token = 'manage';
	private $arrTokens = array(
		'manage',
		'delete',
		'view',
		'edit',
		'sections',
		'gamesandactivities',
		'remove'
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

	private $objSchoolPackages = null;

	private $reseller_uid	= null;
	private $class_uid		= null;
	private $class_name		= null;

	private $student_uid	= null;
	private $student_user_uid=null;

	private $student_pair	= null;
	private $view_mode		=false;

	public function __construct() {

		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($_SESSION['user']['reseller_uid'])) {
			$this->reseller_uid = $_SESSION['user']['reseller_uid'];
		}

		if (isset($this->arrPaths[3])) {
			$arrID = explode('_',$this->arrPaths[3]);
			if(is_array($arrID) && count($arrID) == 3) {
				$this->student_pair = $this->arrPaths[3];
				$class_uid = $arrID[2];
				$student_uid = $arrID[0];
				$objClasses = new classes($class_uid);
				if($objClasses->get_valid()) {
					$objClasses->load();
					$this->class_uid = $objClasses->get_uid();
					$this->class_name = $objClasses->get_name();
					if(classes_student::isValidClassStudent($this->class_uid,$student_uid)==true) {

						$objProfileStudent = new profile_student($student_uid);
						if($objProfileStudent->get_valid()) {
							$objProfileStudent->load();
							$this->student_uid = $objProfileStudent->get_uid();
							$this->student_user_uid = $objProfileStudent->get_iuser_uid();
						} else {
							output::redirect(config::url('account/class/list/'));
						} 
					} else {
						output::redirect(config::url('account/class/list/'));
					}
				}
			} else {
				output::redirect(config::url('account/class/list/'));
			}
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
			$this->objSchoolPackages = new school_packages();
			return $this->objSchoolPackages->isValidPackage($package_uid);
		}
	}

	protected function doManage() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.schoolteacher.student.package.management');
		if(isset($_POST['assign_now']) && isset($_POST['package'])) {
			$objStudentPackages = new student_packages();
			$objStudentPackages->AssignPackagesToStudent($this->student_user_uid);
			output::redirect(config::url('account/student-package-management/manage/'.$this->student_pair.'/'));
		}

		$body->assign('div.assign.packages',$this->getAvailablePackages());
		$body->assign('div.manage.packages',$this->getTabManagePackagePermission());
		$body->assign('class_name',$this->class_name);
		$body->assign('class_uid',$this->class_uid);

		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}

	private function getAvailablePackages() {
		$arrHtml = array();
		$objSchoolPackages = new school_packages();
		$arrPackages = $objSchoolPackages->getSchoolActivePackagesForStudent($this->student_user_uid);
		if(is_array($arrPackages) && count($arrPackages)) {
			$body=make::tpl('body.account.schoolteacher.student.packages');
			foreach ($arrPackages as $row) {
				$row['student_pair'] = $this->student_pair;
				$arrHtml[] = make::tpl('body.student.package.checkbox')->assign($row)->get_content();
			}
			$body->assign('package.list',implode('',$arrHtml));
			$body->assign('class_uid',$this->class_uid);
			$body->assign('student_pair',$this->student_pair);
			return $body->get_content();
		}
		return 'There is not any package available to this school or click on manage package permission tab to manage active package in this student.';
	}

	private function getTabManagePackagePermission() {
		$arrHtml = array();

		$objStudentPackages = new student_packages();
		$arrPackages = $objStudentPackages->getStudentActivePackages($this->student_user_uid);
		if(is_array($arrPackages) && count($arrPackages)) {
			$body = make::tpl('body.account.schoolteacher.manage.student.package.list');
			$rows = array();
			foreach ($arrPackages as $uid => $arrData) {
				$arrData['student_pair'] = $this->student_pair;
				$row = make::tpl('body.account.schoolteacher.manage.student.package.list.row')->assign($arrData);
					$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
			return $body->get_content();
		}
		return 'There is no any package associated to this student, You can click on `Assign Package` tab to assign package to this student.';
	}

	protected function doList() {
		$skeleton = config::getUserSkeleton();
		$body = make::tpl('body.account.schoolteacher.package.list');
		$objSchoolPackages = new school_packages();
		$arrPackages = $objSchoolPackages->getSchoolActivePackages($this->reseller_uid);

		if ($arrPackages && count($arrPackages) > 0) {
			$rows = array();
			foreach ($arrPackages as $uid => $arrData) {
				$row = make::tpl('body.account.schoolteacher.package.list.row')->assign($arrData);
				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}

		$page_display_title = $objSchoolPackages->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $objSchoolPackages->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $objSchoolPackages->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $objSchoolPackages->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

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

	protected function doEdit() {
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5])) {
			$uid = $this->arrPaths[5];
			$objStudentPackages = new student_packages($uid);
			if ($objStudentPackages->get_valid()) {
				$objStudentPackages->load();
				if($this->verifyParentPackage($objStudentPackages->get_package_uid())===true && $objStudentPackages->get_student_user_uid() == $this->student_user_uid) {
					$this->ParsePackage($objStudentPackages);
					$skeleton = config::getUserSkeleton();
					$body = make::tpl('body.account.schoolteacher.student.package.edit');
					$body->assign('uid', $uid);
					$arrLearnable = array();
					if (count($_POST) > 0) {
						if (($response = $objStudentPackages->isValidUpdate()) === true) {
							output::redirect(config::url('account/student-package-management/manage/'.$this->student_pair.'/#manage'));
						} else {
							if (isset($_POST['learnable_language_uid'])) {
								$arrLearnable = $_POST['learnable_language_uid'];
							}
							$body->assign($objStudentPackages->arrForm);
						}
					} else {
						foreach ($objStudentPackages->TableData as $idx => $val) {
							$arrBody[$idx] = $val['Value'];
						}
						$arrLearnable = array();
						$arrLearnable = $this->json_languages;
						$body->assign($arrBody);
					}

					$arrLearnableLanguages = $this->getLearnableLanguages($arrLearnable);
					$body->assign(
						array(
							'learnable_languages' => implode("", $arrLearnableLanguages)
						)
					);

					$body->assign(
						array(
							'support_languages' => $this->getSupportLanguages()
						)
					);
					$body->assign(
						array(
							'class_name'=>$this->class_name,
							'class_uid'=>$this->class_uid,
							'student_pair'=>$this->student_pair
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
					$this->RedirectToList();
				}
			} else {
				// redirect back
				$this->RedirectToList();
			}
		} else {
			// redirect back
			$this->RedirectToList();
		}
	}

	protected function doRemove() {
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5])) {
			$uid = $this->arrPaths[5];
			$objStudentPackages = new student_packages($uid);
			if ($objStudentPackages->get_valid()) {
				$objStudentPackages->load();
				if($objStudentPackages->get_student_user_uid() == $this->student_user_uid) {
					$objStudentPackages->set_remove_date(date('Y-m-d H:i:s'));
					$objStudentPackages->set_removed_by_uid($_SESSION['user']['uid']);
					$objStudentPackages->save();
					output::redirect(config::url('account/student-package-management/manage/'.$this->student_pair.'/#manage'));
				} else {
					// redirect back
					$this->RedirectToList();
				}
			} else {
				// redirect back
				$this->RedirectToList();
			}
		} else {
			// redirect back
			$this->RedirectToList();
		}
	}

	protected function doSections() {
		@ini_set('memory_limit', '256M');
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5])) {
			$uid = $this->arrPaths[5];
			$objStudentPackages = new student_packages($uid);
			if ($objStudentPackages->get_valid()) {
				$objStudentPackages->load();
				if($this->verifyParentPackage($objStudentPackages->get_package_uid())===true && $objStudentPackages->get_student_user_uid() == $this->student_user_uid) {

					if (isset($_POST['submit'])) {
						$objStudentPackages->SavePackageSections(
							$objStudentPackages->get_uid(),
							$_POST['section']
						);
						output::redirect(config::url('account/student-package-management/manage/'.$this->student_user_uid.'/sections/'.$objStudentPackages->get_uid().'/'));
					}

					$this->ParsePackage($objStudentPackages);
					$skeleton = config::getUserSkeleton();
					$body = make::tpl('body.account.schoolteacher.student.package.sections');
					$body->assign('uid', $objStudentPackages->get_uid());

					$body->assign(
						array(
							'tabs'			=>$this->getLanguages(),
							'uid'			=>$objStudentPackages->get_uid(),
							'class_name'=>$this->class_name,
							'class_uid'=>$this->class_uid,
							'student_pair'=>$this->student_pair
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
					$this->RedirectToList();
				}
			} else {
				// redirect back
				$this->RedirectToList();
			}
		} else {
			// redirect back
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
		if ($learnable_language_uid != null && isset($this->objSchoolPackages->json_years[$learnable_language_uid]) && is_array($this->objSchoolPackages->json_years[$learnable_language_uid])) {
			$l_uid = $learnable_language_uid;

			$arrYears = years::getFilteredYears($this->objSchoolPackages->json_years[$l_uid]);
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
		$l_uid = $learnable_language_uid;
		if ($year_uid != null && $learnable_language_uid != null && isset($this->objSchoolPackages->json_units[$l_uid][$year_uid]) && is_array($this->objSchoolPackages->json_units[$l_uid][$year_uid])) {
			
			$arrUnits = units::getFilteredUnits($year_uid,$this->objSchoolPackages->json_units[$l_uid][$year_uid]);
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
		$l_uid = $learnable_language_uid;
		if ($year_uid != null && $unit_uid != null && $learnable_language_uid != null && isset($this->objSchoolPackages->json_section_uids[$l_uid][$unit_uid]) && is_array($this->objSchoolPackages->json_section_uids[$l_uid][$unit_uid])) {

			$class = 'checkbox_'.$l_uid . '_' . $year_uid . '_' . $unit_uid;
			$arrSections = sections::getFilteredSections($unit_uid,$this->objSchoolPackages->json_section_uids[$l_uid][$unit_uid]);

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
		@ini_set('memory_limit', '256M');
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5])) {
			$uid = $this->arrPaths[5];
			$objStudentPackages = new student_packages($uid);
			if ($objStudentPackages->get_valid()) {
				$objStudentPackages->load();
				if($this->verifyParentPackage($objStudentPackages->get_package_uid())===true && $objStudentPackages->get_student_user_uid() == $this->student_user_uid) {

					if (isset($_POST['submit'])) {
						$objStudentPackages->SavePackageGames(
							$objStudentPackages->get_uid(),
							$_POST['game']
						);
						output::redirect(config::url('account/student-package-management/manage/'.$this->student_pair.'/gamesandactivities/'.$objStudentPackages->get_uid().'/'));
					}

					$skeleton = config::getUserSkeleton();
					$body = make::tpl('body.account.schoolteacher.student.package.gamesandactivities');
					$this->ParsePackage($objStudentPackages);
					$body->assign(
							array(
								'div.games'	=>$this->getGameContent($objStudentPackages->get_uid()),
								'uid'			=>$objStudentPackages->get_uid(),
								'class_name'	=>$this->class_name,
								'class_uid'		=>$this->class_uid,
								'student_pair'=>$this->student_pair
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
					$this->RedirectToList();
				}
			} else {
				// redirect back
				$this->RedirectToList();
			}
		} else {
			// redirect back
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

		if ($language_uid != null && $section_uid != null && $unit_uid != null && isset($this->objSchoolPackages->games[$language_uid][$unit_uid][$section_uid]) && is_array($this->objSchoolPackages->games[$language_uid][$unit_uid][$section_uid])) {
			$arrGameUid = $this->objSchoolPackages->games[$language_uid][$unit_uid][$section_uid];

			$arrGames = game::getFilteredGames($arrGameUid);
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
	}

	private function ParsePackage($objPackage=null) {
		if($objPackage!=null) {
			@ini_set('memory_limit', '256M');
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


	private function doView() {
		@ini_set('memory_limit', '256M');
		if(isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5])) {
			$objResellerSubPackage = new reseller_sub_package($this->arrPaths[5]);
			if ($objResellerSubPackage->get_valid()) {
				$objResellerSubPackage->load();
				$this->ParsePackage($objResellerSubPackage);
				$this->view_mode=true;
				$skeleton = config::getUserSkeleton();
				$body = make::tpl('body.account.schoolteacher.student.package.complete.view');
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
				$body->assign('class_uid',$this->class_uid);
				$body->assign('class_name',$this->class_name);
				$body->assign('student_pair',$this->student_pair);
				$skeleton->assign(
						array(
							'body' => $body
						)
				);
				output::as_html($skeleton, true);
			}
		}
	}

	private function getSupportLanguages() {
		$objLanguage = new language($this->objSchoolPackages->get_support_language_uid());
		if($objLanguage->get_valid()) {
			$objLanguage->load();
			return $objLanguage->get_name();
		}
			return ' ';
	}
	private function getLearnableLanguages($arrLearnable = array()) {
		$arrLearnableLanguages = array();
		if(!isset($this->objSchoolPackages->json_languages) || !is_array($this->objSchoolPackages->json_languages)) {
			return $arrLearnableLanguages;
		}
		$arrLanguage = language::getFilteredLanguages($this->objSchoolPackages->json_languages);
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


	private function RedirectToList() {
		if(isset($this->class_uid) && $this->class_uid!=null) {
			output::redirect(config::url('account/student-package-management/manage/'.$this->class_uid.'/#manage'));
		} else {
			output::redirect(config::url('account/order-packages/list/'));
		}
	}
}

?>