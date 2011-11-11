<?php

class languages extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete',
		'check',
		'Check2',
		'managetranslation'
	);
	private $arrPaths = array();
	private $classesToTranslate = array(
		'activity_skill_translation',
		'activity_translation',
		'article_template_translation',
		'certificate_messages_translations',
		'difficulty_level_translation',
		'exercise_qae_topic_content_question_option_translation',
		'exercise_qae_topic_content_question_translation',
		'exercise_qae_topic_content_translation',
		'exercise_qae_topic_translation',
		'exercise_type_translation',
		'exercise_writing_translation',
		'flash_translations_locales',
		'game_translation',
		'language_translation',
		'notification_event_translation',
		'package_price',
		'page_index_tab_children_translations',
		'page_index_tab_contact_translations',
		'page_index_tab_culture_translations',
		'page_index_tab_games_translations',
		'page_index_tab_songs_translations',
		'page_index_tab_teachers_translations',
		'page_index_tab_welcome_translations',
		'page_messages',
		'page_privacy_translations',
		'page_subscribe_homeuser_stage_1_translations',
		'page_subscribe_homeuser_stage_2_translations',
		'page_subscribe_homeuser_stage_3_translations',
		'page_subscribe_homeuser_stage_4_translations',
		'page_subscribe_school_stage_1_translations',
		'page_subscribe_school_stage_2_translations',
		'page_subscribe_school_stage_3_translations',
		'page_subscribe_school_stage_4_translations',
		'page_subscribe_school_stages_translations',
		'page_subscribe_select_translations',
		'page_terms_translations',
		'page_widget_translations',
		'qae_topic_translation',
		'reference_material_type_translation',
		'reseller_package_price',
		'reseller_sub_package_price',
		'school_registration_templates_translations',
		'sections_translations',
		'sections_vocabulary_translations',
		'send_application_translation',
		'speaking_and_listening_translation',
		'template_translation',
		'units_translations',
		'years_translations',
	);

	public function __construct() {
		parent::__construct();
		$this->arrPaths = config::get('paths');
		if (isset($this->arrPaths[2])) {
			$this->token = str_replace(array('-'), array(''), $this->arrPaths[2]);
		}
		if (in_array($this->token, $this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		} else {
			$this->doList();
		}
	}

	private function doManagetranslation() {
		if (isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$objLanguage = new language($this->arrPaths[3]);
			if($objLanguage->get_valid()) {
				$objLanguage->load();
				if(isset($_POST['form_submit_button'])) {
					if(isset($_POST['uid']) && is_numeric($_POST['uid']) && isset($_POST['from_language_uid']) && is_numeric($_POST['from_language_uid']) && $_POST['from_language_uid'] != $_POST['uid'] && $_POST['from_language_uid'] > 0) {
						$objLanguage->CopyTranslation($_POST['from_language_uid'], $objLanguage->get_uid());
					}
					output::redirectTo("admin/language/list/");
				}
				$skeleton = make::tpl('skeleton.admin');
				$body = make::tpl('body.admin.language.manage-translation');
				$body->assign(
					array(
						'uid'	=>$objLanguage->get_uid(),
						'name'	=>$objLanguage->get_name()
					)
				);
				$body->assign("from_language_uid", $objLanguage->LanguageSelectBox('from_language_uid',14,$objLanguage->get_uid()));
				$skeleton->assign(
					array(
						'body' => $body
					)
				);
				output::as_html($skeleton, true);
			} else {
				output::redirectTo("admin/language/list/");
			}
		} else {
			output::redirectTo("admin/language/list/");
		}
	}

	public function doCheck() {
		$tableList = $this->classesToTranslate;

		foreach ($tableList as $key => $table) {
			if (class_exists($table)) {
				$sql="select count(*) as total from {$table}";
				$res=database::arrQuery($sql);
				$tot=(isset($res[0]["total"]))?$res[0]["total"]:"0";
				echo "<br>".$table.":".$tot;
			}
		}
	}
	public function doCheck2() {
		$tableList = $this->classesToTranslate;

		foreach ($tableList as $key => $table) {
			if (class_exists($table)) {
				$sql="select count(*) as total from {$table}";
				$res=database::arrQuery($sql);
				$tot=(isset($res[0]["total"]))?$res[0]["total"]:"0";
				echo "<br>".$table.":".$tot;
			}
		}
	}

	protected function doAdd() {
		$skeleton = make::tpl('skeleton.admin');
		$body = make::tpl('body.admin.language.add');
		$body->assign('display_translate', 'block');
		$arrBody = array();
		$arrBody['title'] = 'Add Language';
		$arrBody['btnval'] = 'Add';
		$currency_select = "";
		$objLanguage = new language();
		if (isset($_POST['form_submit_button'])) {

			if ($objLanguage->doSave()) {
				if (isset($_POST['translate']) && $_POST['translate'] == '1') {
					if(isset($_POST['from_language_uid']) && $_POST['from_language_uid']>0) {
						$objLanguage->CopyTranslation($_POST['from_language_uid'],$objLanguage->arrForm["uid"]);
					} else {
						$objLanguage->CopyTranslation(14,$objLanguage->arrForm["uid"]);
					}
				}
				output::redirectTo("admin/language/");
			} else {
				$body->assign($objLanguage->arrForm);
				$currency_select = $objLanguage->arrForm["currency_uid"];
			}
		}
		$arrBody['li.display'] = ' style="display:none;" ';

		// currency dropdown
		$objCurrencies = new currencies();
		$currencyDD = $objCurrencies->CurrencySelectBox("currency_uid", $currency_select, "currency_uid");

		$body->assign("currency_uid_select", $currencyDD);
		$body->assign("from_language_uid", $objLanguage->LanguageSelectBox('from_language_uid',14));
		$body->assign($arrBody);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	protected function doEdit() {
		$skeleton = make::tpl('skeleton.admin');
		$body = make::tpl('body.admin.language.add-edit');
		$body->assign('display_translate', 'none');
		$arrBody = array();
		$arrBody['title'] = 'Update Language';
		$arrBody['btnval'] = 'Update';
		$objLanguageTranslation = new language_translation();
		$arrBody['language.div'] = $objLanguageTranslation->getTranslationForm($this->arrPaths[3]);
		$arrBody['language.uploades'] = plugin_certificate::pdf_uploads($this->arrPaths[3]);
		if (isset($_POST['form_submit_button'])) {
			$objLanguage = new language();
			if ($objLanguage->doSave()) {
// redirect to invoice list if all does well;
				$objLanguage->redirectTo('admin/language/list');
			} else {

				$body->assign($objLanguage->arrForm);
				$objCurrencies = new currencies();
				$currencyDD = $objCurrencies->CurrencySelectBox("currency_uid", $objLanguage->arrForm['currency_uid'], "currency_uid");
				if (!empty($currencyDD)) {
					$arrBody['currency_uid_select'] = $currencyDD;
				}
			}
		} else {
			if ($this->arrPaths[3] > 0) {
				$objLanguage = new language($this->arrPaths[3]);
				$objLanguage->load();
				foreach ($objLanguage->TableData as $idx => $val) {
					$arrBody[$idx] = $val['Value'];
				}
				$arrBody['uid'] = $this->arrPaths[3];
				// currency dropdown
				$objCurrencies = new currencies();
				$currencyDD = $objCurrencies->CurrencySelectBox("currency_uid", $arrBody['currency_uid'], "currency_uid");
				if (!empty($currencyDD)) {
					$arrBody['currency_uid_select'] = $currencyDD;
				}
				if ($arrBody['active'] == 0) {
					$arrBody['active'] = 'checked="checked"';
				}
				if ($arrBody['available'] == 0) {
					$arrBody['available'] = 'checked="checked"';
				}
				if ($arrBody['is_learnable'] == 0) {
					$arrBody['is_learnable'] = 'checked="checked"';
				}
				if ($arrBody['is_support'] == 0) {
					$arrBody['is_support'] = 'checked="checked"';
				}
				if ($arrBody['show_reseller_codes'] == 0) {
					$arrBody['show_reseller_codes'] = 'checked="checked"';
				}
			}
		}



		$body->assign($arrBody);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	protected function doDelete() {
		if ($this->arrPaths[3] > 0) {
			$objLanguage = new language($this->arrPaths[3]);
			$objLanguage->delete();
// redirect to invoice list if all does well;
			$objLanguage->redirectTo('admin/language/list/');
		}
	}

	protected function doList() {

		$skeleton = make::tpl('skeleton.admin');
		$body = make::tpl('body.admin.language.list');
		$arrList = array();
		$objLanguage = new language();
		$arrList = $objLanguage->getList();
		$arrRows = array();
		if (!empty($arrList)) {
			foreach ($arrList as $uid => $data) {
				$arrRows[] = make::tpl('body.admin.language.list.row')->assign($data)->get_content();
			}
		}
		$page_display_title = $objLanguage->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $objLanguage->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $objLanguage->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $objLanguage->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		$body->assign('page.display.title', $page_display_title);
		$body->assign('page.navigation', $page_navigation);
		$body->assign('list.rows', implode('', $arrRows));
		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

}

?>