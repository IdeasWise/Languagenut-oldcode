<?php

	class admin_activity extends Controller {

		private $token = 'list';
		private $arrTokens = array(
		'list',
		'edit',
		'reference-materials',
		'exercise',
		'article',
		'add',
		'delete'
		);
		private $parts = array();

		public function __construct() {
			parent::__construct();

			$this->parts = config::get('paths');

			if (isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
				$this->token = $this->parts[2];
			}

			if (in_array($this->token, $this->arrTokens)) {
				$this->token = str_replace(" ", "", ucwords(str_replace("-", " ", $this->token)));
				$method = 'do' . $this->token;
				$this->$method();
			}
		}

		protected function doAdd() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.activity.add');
			$body->load();

			if (count($_POST) > 0) {
				$objActivity = new activity();
				if (($response = $objActivity->isValidCreate($_POST)) === true) {
					output::redirect(config::url('admin/activity/reference-materials/' . $objActivity->get_uid() . "/"));
				} else {
					$body->assign($response);
				}
			}

			// difficulty level dropdown
			$difficulty_uids = array("" => "Please Select");
			$difficultylevels = array();
			$difficultylevels = difficultylevel::getAllDifficulties();
			if (!empty($difficultylevels)) {
				foreach ($difficultylevels as $uid => $data) {
					$difficulty_uids[$data['uid']] = $data['name'];
				}
			}

			// exercise type dropdown
			$exercise_type_uids = array("" => "Please Select");
			$exercise_types = array();
			$exercise_types = exercisetype::getAllExerciseTypes();
			if (!empty($exercise_types)) {
				foreach ($exercise_types as $uid => $data) {
					$exercise_type_uids[$data['uid']] = $data['name'];
				}
			}

			// material type dropdown
			$material_type_uids = array("" => "Please Select");
			$reference_material_types = array();
			$reference_material_types = referencematerialtype::getAllMaterialTypes();
			if (!empty($reference_material_types)) {
				foreach ($reference_material_types as $uid => $data) {
					$material_type_uids[$data['uid']] = $data['name'];
				}
			}

			// skill level dropdown
			$skill_level_uids = array("" => "Please Select");
			$skill_levels = array();
			$skill_levels = activity_skill::getAllActivitySkills();
			if (!empty($skill_levels)) {
				foreach ($skill_levels as $uid => $data) {
					$skill_level_uids[$data['uid']] = $data['name'];
				}
			}

			// unit dropdown
			$unit_uids = array("" => "Please Select");
			$units = array();
			$units = units::getUnits();
			if (!empty($units)) {
				foreach ($units as $uid => $data) {
					$unit_uids[$data['uid']] = $data['name'];
				}
			}

			$tabs_li = array();
			$tabs_div = array();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {
					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$lang_form = new xhtml('admin.activity.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);

					$tabs_li[] = $localeLi->get_content();
					$tabs_div[] =$lang_form->get_content();
					//				$lang_form = "";
					//				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
					//				$lang_form.='<table>';
					//				$lang_form.='<tr>';
					//				$lang_form.='<td>';
					//				$lang_form.='Name:';
					//				$lang_form.='</td>';
					//				$lang_form.='<td>';
					//				$lang_form.='<input type="text" name="name_' . $arrData['prefix'] . '" value="" class="box">';
					//				$lang_form.='</td>';
					//				$lang_form.='</tr>';
					//				$lang_form.='<tr>';
					//				$lang_form.='<td>';
					//				$lang_form.='Available:';
					//				$lang_form.='</td>';
					//				$lang_form.='<td>';
					//				$lang_form.='<select name="available_' . $arrData['prefix'] . '">';
					//				$lang_form.='<option value="1">Yes</option>';
					//				$lang_form.='<option value="0">No</option>';
					//				$lang_form.='</select>';
					//				$lang_form.='</td>';
					//				$lang_form.='</tr>';
					//				$lang_form.='</table>';
					//				$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form . '</div>';
				}
			}

			$body->assign(
			array(
			'tabs' => implode('', $tabs_div),
			'locales' => implode('', $tabs_li),
			'title' => '<a href="{{ uri }}admin/activity/list/">Activities</a> > Add',
			'difficulty_uid' => format::to_select(array("name" => "difficulty_uid", "id" => "difficulty_uid", "options_only" => false), $difficulty_uids),
			'exercise_type_uid' => format::to_select(array("name" => "exercise_type_uid", "id" => "exercise_type_uid", "options_only" => false), $exercise_type_uids),
			'material_type_uid' => format::to_select(array("name" => "material_type_uid", "id" => "material_type_uid", "options_only" => false), $material_type_uids),
			'skill_level_uid' => format::to_select(array("name" => "skill_level_uid", "id" => "skill_level_uid", "options_only" => false), $skill_level_uids),
			'unit_uid' => format::to_select(array("name" => "unit_uid", "id" => "unit_uid", "options_only" => false), $unit_uids)
			)
			);

			$skeleton->assign(
			array(
			'body' => $body
			)
			);
			output::as_html($skeleton, true);
		}

		protected function doEdit() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.activity.edit');
			$body->load();

			$activity_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';
			$objActivity = null;

			if ($activity_uid != '') {

				$objActivity = new activity($activity_uid);
				$objActivity->load();

				$arrqaetopic = $objActivity->getFields();

				if (count($arrqaetopic) > 0) {
					if (count($_POST) > 0) {
						$_POST['activity_uid'] = $activity_uid;
						if (($arrqaetopic = $objActivity->isValidUpdate($_POST)) === true) {
							output::redirect(config::url('admin/activity/reference-materials/' . $activity_uid . "/"));
						} else {
							$body->assign($arrqaetopic);
						}
					}
				}
			} else {
				output::redirect(config::url('admin/activity/list/'));
			}

			// difficulty level dropdown
			$difficulty_uids = array("" => "Please Select");
			$difficultylevels = array();
			$difficultylevels = difficultylevel::getAllDifficulties();
			if (!empty($difficultylevels)) {
				foreach ($difficultylevels as $uid => $data) {
					$difficulty_uids[$data['uid']] = $data['name'];
				}
			}

			// exercise type dropdown
			$exercise_type_uids = array("" => "Please Select");
			$exercise_types = array();
			$exercise_types = exercisetype::getAllExerciseTypes();
			if (!empty($exercise_types)) {
				foreach ($exercise_types as $uid => $data) {
					$exercise_type_uids[$data['uid']] = $data['name'];
				}
			}

			// material type dropdown
			$material_type_uids = array("" => "Please Select");
			$reference_material_types = array();
			$reference_material_types = referencematerialtype::getAllMaterialTypes();
			if (!empty($reference_material_types)) {
				foreach ($reference_material_types as $uid => $data) {
					$material_type_uids[$data['uid']] = $data['name'];
				}
			}

			// skill level dropdown
			$skill_level_uids = array("" => "Please Select");
			$skill_levels = array();
			$skill_levels = activity_skill::getAllActivitySkills();
			if (!empty($skill_levels)) {
				foreach ($skill_levels as $uid => $data) {
					$skill_level_uids[$data['uid']] = $data['name'];
				}
			}

			// unit dropdown
			$unit_uids = array("" => "Please Select");
			$units = array();
			$units = units::getUnits();
			if (!empty($units)) {
				foreach ($units as $uid => $data) {
					$unit_uids[$data['uid']] = $data['name'];
				}
			}

			$tabs_li = array();
			$tabs_div = array();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "tab-");
					$localeLi->assign("uid", $uid);
					$localeLi->assign("prefix", $arrData['prefix']);

					$lang_form = new xhtml('admin.activity.langform');
					$lang_form->load();
					$lang_form->assign("uid", $uid);
					$lang_form->assign("prefix", $arrData['prefix']);
					if($activityData['available']){
						$lang_form->assign("selected_1",'selected="selected"');
					}
					else{
						$lang_form->assign("selected_0",'selected="selected"');
					}

					$tabs_li[] = $localeLi->get_content();
					$tabs_div[] =$lang_form->get_content();

					//				$activityData = $objActivity->getListByLocale($activity_uid, $uid);
					//
					//				$lang_form = "";
					//				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
					//				$lang_form.='<table>';
					//				$lang_form.='<tr>';
					//				$lang_form.='<td>';
					//				$lang_form.='Name:';
					//				$lang_form.='</td>';
					//				$lang_form.='<td>';
					//				$lang_form.='<input type="text" name="name_' . $arrData['prefix'] . '" value="' . $activityData['name'] . '" class="box">';
					//				$lang_form.='</td>';
					//				$lang_form.='</tr>';
					//				$lang_form.='<tr>';
					//				$lang_form.='<td>';
					//				$lang_form.='Available:';
					//				$lang_form.='</td>';
					//				$lang_form.='<td>';
					//				$lang_form.='<select name="available_' . $arrData['prefix'] . '">';
					//				$lang_form.='<option value="1"' . ($activityData['available'] ? ' selected="selected"' : '') . '>Yes</option>';
					//				$lang_form.='<option value="0"' . (!$activityData['available'] ? ' selected="selected"' : '') . '>No</option>';
					//				$lang_form.='</select>';
					//				$lang_form.='</td>';
					//				$lang_form.='</tr>';
					//				$lang_form.='</table>';
					//				$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form . '</div>';
				}
			}

			$body->assign(
			array(
			'tabs' => implode('', $tabs_div),
			'locales' => implode('', $tabs_li),
			'title' => '<a href="{{ uri }}admin/activity/list/">Activities</a> > ' . $objActivity->get_name(),
			'activity_uid' => $activity_uid,
			'name' => $objActivity->get_name(),
			'available_select_0' => (!$objActivity->get_available()) ? 'selected="selected"' : '',
			'available_select_1' => ($objActivity->get_available()) ? 'selected="selected"' : '',
			'difficulty_uid' => format::to_select(array("name" => "difficulty_uid", "id" => "difficulty_uid", "options_only" => false), $difficulty_uids, $objActivity->get_difficulty_uid()),
			'exercise_type_uid' => format::to_select(array("name" => "exercise_type_uid", "id" => "exercise_type_uid", "options_only" => false), $exercise_type_uids, $objActivity->get_exercise_type_uid()),
			'material_type_uid' => format::to_select(array("name" => "material_type_uid", "id" => "material_type_uid", "options_only" => false), $material_type_uids, $objActivity->get_material_type_uid()),
			'skill_level_uid' => format::to_select(array("name" => "skill_level_uid", "id" => "skill_level_uid", "options_only" => false), $skill_level_uids, $objActivity->get_skill_level_uid()),
			'unit_uid' => format::to_select(array("name" => "unit_uid", "id" => "unit_uid", "options_only" => false), $unit_uids, $objActivity->get_unit_uid())
			)
			);

			$skeleton->assign(
			array(
			'body' => $body
			)
			);
			output::as_html($skeleton, true);
		}

		protected function doDelete() {
			$uid=$this->parts[3];
			$objActivity = new activity();
			$objActivity->softDelete($uid);
			output::redirect(config::url('admin/activity/list/'));
		}

		protected function doList() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.activity.list');
			$body->load();

			$objActivity = new activity();
			$arractivity = $objActivity->getListByname();
			$i = 0;
			if ($arractivity && count($arractivity) > 0) {
				$rows = array();
				foreach ($arractivity as $uid => $arrData) {
					$i++;

					$row = new xhtml('body.admin.activity.list.row');
					$row->load();
					$row->assign(array(
					'uid' => $uid,
					'name' => stripslashes($arrData['name']),
					'available' => ($arrData['available'] == '0') ? 'No' : 'Yes'
					));
					$rows[] = $row->get_content();
				}
				$body->assign('rows', implode('', $rows));
			}
			$page_display_name = $objActivity->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
			$page_navigation = $objActivity->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $objActivity->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $objActivity->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$body->assign('page.display.name', $page_display_name);
			$body->assign('page.navigation', $page_navigation);
			$skeleton->assign(
			array(
			'body' => $body
			)
			);

			output::as_html($skeleton, true);
		}

		protected function doArticle() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.activity.article.select');
			$body->load();

			$activity_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';
			$objActivity = null;
			$material_type = "";

			$objExerciseWriting = new exercise_writing();
			$ExerciseArticle=$objExerciseWriting->getListByActivity($activity_uid);
			if ($activity_uid != '') {

				$objActivity = new activity($activity_uid);
				$objActivity->load();

				$material_type_uid = $objActivity->get_material_type_uid();
				$reference_material_type = new referencematerialtype($material_type_uid);
				$reference_material_type->load();
				$material_type = $reference_material_type->get_token();

				$arrqaetopic = $objActivity->getFields();

				if (count($arrqaetopic) > 0) {
					if (count($_POST) > 0) {
						$_POST['activity_uid'] = $activity_uid;

						switch ($material_type) {
							case "article":
								$_POST["exercise_writing_uid"]=(isset($ExerciseArticle['uid']))?$ExerciseArticle['uid']:0;
								if (($arrqaetopic = $objExerciseWriting->insertOrUpdate($_POST)) === true) {
									output::redirect(config::url('admin/activity/list/' . $activity_uid . "/"));
								} else {
									$body->assign($arrqaetopic);
								}
								break;
						}

						if (($arrqaetopic = $objActivityReferenceMaterials->isValidArticleUpdate($_POST)) === true) {
							output::redirect(config::url('admin/activity/exercise/' . $activity_uid . "/"));
						} else {
							$body->assign($arrqaetopic);
						}
					}
				}
			} else {
				output::redirect(config::url('admin/activity/list/'));
			}

			$response = "";
			switch ($material_type) {
				case "story":
				case "song":
					$response = $this->getLanguagesTab();
					break;
				case "article":
					$response = $this->getExerciseArticleList($activity_uid);
					break;
				case "none":
					output::redirect(config::url('admin/activity/list/' . $activity_uid . "/"));
					break;
			}

			$body->assign(
			array(
			'response' => $response,
			'activity_uid' => $activity_uid,
			'activity_name' => $objActivity->get_name()
			)
			);

			$skeleton->assign(
			array(
			'body' => $body
			)
			);

			output::as_html($skeleton, true);
		}

		protected function doReferenceMaterials() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.activity.reference.materials');
			$body->load();

			$activity_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';
			$objActivity = null;
			$material_type = "";

			$objActivityReferenceMaterials = new activity_reference_materials();
			if ($activity_uid != '') {

				$objActivity = new activity($activity_uid);
				$objActivity->load();

				$material_type_uid = $objActivity->get_material_type_uid();
				$reference_material_type = new referencematerialtype($material_type_uid);
				$reference_material_type->load();
				$material_type = $reference_material_type->get_token();

				$arrqaetopic = $objActivity->getFields();

				if (count($arrqaetopic) > 0) {
					if (count($_POST) > 0) {
						$_POST['activity_uid'] = $activity_uid;

						switch ($material_type) {
							case "article":
								if (($arrqaetopic = $objActivityReferenceMaterials->isValidArticleUpdate($_POST)) === true) {
									output::redirect($objActivity->redirectToUrl($activity_uid));

								} else {
									$body->assign($arrqaetopic);
								}
								break;
						}

						if (($arrqaetopic = $objActivityReferenceMaterials->isValidArticleUpdate($_POST)) === true) {
							output::redirect(config::url('admin/activity/exercise/' . $activity_uid . "/"));
						} else {
							$body->assign($arrqaetopic);
						}
					}
				}
			} else {
				output::redirect(config::url('admin/activity/list/'));
			}

			$response = "";
			switch ($material_type) {
				case "story":
				case "song":
					$response = $this->getLanguagesTab();
					break;
				case "article":
					$response = $this->getArticleList($activity_uid);
					break;
				case "none":
					output::redirect(config::url('admin/activity/exercise/' . $activity_uid . "/"));
					break;
			}

			$body->assign(
			array(
			'response' => $response,
			'activity_uid' => $activity_uid,
			'activity_name' => $objActivity->get_name()
			)
			);

			$skeleton->assign(
			array(
			'body' => $body
			)
			);

			output::as_html($skeleton, true);
		}

		protected function getArticleList($activity_uid) {

			$objActivity = null;
			if ($activity_uid != '') {

				$objActivity = new activity($activity_uid);
				$objActivity->load();
			} else {
				output::redirect(config::url('admin/activity/list/'));
			}

			// article dropdown
			$article_uids = array("" => "Please Select");
			$articles = array();
			$articles = article::getArticles($objActivity->get_unit_uid());

			if (!empty($articles)) {
				foreach ($articles as $uid => $data) {
					$article_uids[$data['uid']] = $data['title'];
				}
			}

			return make::tpl('body.admin.activity.article')->assign(
			array(
			'article_uid' => format::to_select(array("name" => "article_uid", "id" => "article_uid", "options_only" => false), $article_uids, $objActivity->get_material_uid())
			)
			)->get_content();
		}
		protected function getExerciseArticleList($activity_uid) {

			$objActivity = null;
			if ($activity_uid != '') {

				$objActivity = new activity($activity_uid);
				$objActivity->load();
			} else {
				output::redirect(config::url('admin/activity/list/'));
			}

			// article dropdown
			$article_uids = array("" => "Please Select");
			$articles = array();
			$articles = article::getArticles($objActivity->get_unit_uid());

			if (!empty($articles)) {
				foreach ($articles as $uid => $data) {
					$article_uids[$data['uid']] = $data['title'];
				}
			}
			$objExerciseArticle=new exercise_writing();
			$ExerciseArticle=$objExerciseArticle->getListByActivity($activity_uid);

			return make::tpl('body.admin.activity.article')->assign(
			array(
			'article_uid' => format::to_select(array("name" => "article_uid", "id" => "article_uid", "options_only" => false), $article_uids, $ExerciseArticle["article_uid"])
			)
			)->get_content();
		}

		protected function doExercise() {
			$skeleton = new xhtml('skeleton.admin');
			$skeleton->load();

			$body = new xhtml('body.admin.activity.edit');
			$body->load();

			$activity_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

			if ($activity_uid != '') {

				$objActivity = new activity($activity_uid);
				$objActivity->load();

				$arrqaetopic = $objActivity->getFields();

				if (count($arrqaetopic) > 0) {
					if (count($_POST) > 0) {
						$_POST['activity_uid'] = $activity_uid;
						if (($arrqaetopic = $objActivity->isValidUpdate($_POST)) === true) {
							output::redirect(config::url('admin/activity/reference-materials/' . $activity_uid . "/"));
						} else {
							$body->assign($arrqaetopic);
						}
					}
				}
			} else {
				output::redirect(config::url('admin/activity/list/'));
			}
		}

		private function getLanguagesTab($activity_uid = 0) {
			$tabs_li = array();
			$tabs_div = array();
			$arrLocales = language::getPrefixes();
			if (count($arrLocales) > 0) {
				foreach ($arrLocales as $uid => $arrData) {
					$get_years = $this->getYearsTab($activity_uid, $uid);
					$tabs_li[] = '<li><a href="#tab-' . $activity_uid . '-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
					$tabs_div[] = '<div id="tab-' . $activity_uid . '-' . $uid . '">' . $get_years . '</div>';
				}
			}

			return make::tpl('body.admin.activity.reference.materials.tabs.inner')->assign(
			array(
			'tabs.lis' => implode('', $tabs_li),
			'tabs.divs' => implode('', $tabs_div)
			)
			)->get_content();
		}

		private function getYearsTab($activity_uid = 0, $language_uid = 0) {
			$tabs_li = array();
			$tabs_div = array();
			$arrYears = years::getYears();
			if (count($arrYears) > 0) {
				foreach ($arrYears as $uid => $arrData) {
					$get_units = $this->getUnitsTab($activity_uid, $language_uid, $arrData['uid']);
					$tabs_li[] = '<li><a href="#year-tab-' . $activity_uid . '-' . $language_uid . '-' . $arrData['uid'] . '"><span>' . $arrData['name'] . '</span></a></li>';
					$tabs_div[] = '<div id="year-tab-' . $activity_uid . '-' . $language_uid . '-' . $arrData['uid'] . '">' . $get_units . '</div>';
				}
			}

			return make::tpl('body.admin.activity.reference.materials.tabs.inner')->assign(
			array(
			'tabs.lis' => implode('', $tabs_li),
			'tabs.divs' => implode('', $tabs_div)
			)
			)->get_content();
		}

		private function getUnitsTab($activity_uid = 0, $language_uid = 0, $unit_uid = 0) {
			$tabs_li = array();
			$tabs_div = array();
			$arrUnits = units::getUnits();
			if (count($arrUnits) > 0) {
				foreach ($arrUnits as $uid => $arrData) {
					$lang_get_units = "again testing";
					$tabs_li[] = '<li><a href="#unit-tab-' . $activity_uid . '-' . $language_uid . '-' . $unit_uid . '-' . $arrData['uid'] . '"><span>' . $arrData['name'] . '</span></a></li>';
					$tabs_div[] = '<div id="unit-tab-' . $activity_uid . '-' . $language_uid . '-' . $unit_uid . '-' . $arrData['uid'] . '">' . $lang_get_units . '</div>';
				}
			}

			return make::tpl('body.admin.activity.reference.materials.tabs.inner')->assign(
			array(
			'tabs.lis' => implode('', $tabs_li),
			'tabs.divs' => implode('', $tabs_div)
			)
			)->get_content();
		}

	}

?>