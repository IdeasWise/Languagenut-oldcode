<?php

class admin_exercisetype extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'list',
		'edit',
		'add',
		'delete'
	);
	private $parts = array();
	private $objexercisetype = null;

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

	protected function doAdd() {
		$skeleton = new xhtml('skeleton.admin');
		$skeleton->load();

		$body = new xhtml('body.admin.exercisetype.add');
		$body->load();
		$body->assign('title', 'Activities > <a href="{{ uri }}admin/exercisetype/list/">Exercise Type</a> > Add');
		if (count($_POST) > 0) {
			$this->objexercisetype = new exercisetype();
			if (($response = $this->objexercisetype->isValidCreate($_POST)) === true) {
				output::redirect(config::url('admin/exercisetype/list/'));
			} else {
				$body->assign($response);
			}
		}


		$tabs_li = array();
		$tabs_div = array();

		$arrLocales = language::getPrefixes();
		if (count($arrLocales) > 0) {
			foreach ($arrLocales as $uid => $arrData) {
				$lang_form = "";
				$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : "";

				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
				$lang_form.='<table>';
				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='name';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="name_' . $arrData['prefix'] . '" value="' . $nameVal . '" >';
				$lang_form.='</td>';
				$lang_form.='</tr>';

				$lang_form.='</table>';
				$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form . '</div>';
			}
		}

		$body->assign(
				array(
					'tabs' => implode('', $tabs_div),
					'locales' => implode('', $tabs_li),
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

		$body = new xhtml('body.admin.exercisetype.edit');
		$body->load();
		$body->assign('title', 'Activities > <a href="{{ uri }}admin/exercisetype/list/">Exercise Type</a> > Update');
		$exercise_type_uid = (isset($this->parts[3]) && (int) $this->parts[3] > 0) ? $this->parts[3] : '';

		if ($exercise_type_uid != '') {

			$this->objexercisetype = new exercisetype($exercise_type_uid);
			$this->objexercisetype->load();

			$arrqaetopic = $this->objexercisetype->getFields();


			if (count($arrqaetopic) > 0) {

				if (count($_POST) > 0) {
					$_POST['exercise_type_uid'] = $exercise_type_uid;
					if (($arrqaetopic = $this->objexercisetype->isValidUpdate($_POST)) === true) {
						output::redirect(config::url('admin/exercisetype/list/'));
					} else {
						$body->assign($arrqaetopic);
					}
				}

				$body->assign('exercisetype_uid', $exercise_type_uid);
				$body->assign('name', $arrqaetopic['name']);
				$body->assign('token', $arrqaetopic['token']);
				$body->assign('available_select_0', ($arrqaetopic['available'] == "0") ? 'selected="selected"' : '');
				$body->assign('available_select_1', ($arrqaetopic['available'] == "1") ? 'selected="selected"' : '');
			}
		} else {
			output::redirect(config::url('admin/exercisetype/list/'));
		}

		$tabs_li = array();
		$tabs_div = array();

		$arrLocales = language::getPrefixes();

		if (count($arrLocales) > 0) {
			foreach ($arrLocales as $uid => $arrData) {
				$exercisetypeData = $this->objexercisetype->getListByLocale($exercise_type_uid, $uid);
				$lang_form = "";

				$nameVal = (isset($_POST['name_' . $arrData['prefix']])) ? $_POST['name_' . $arrData['prefix']] : $exercisetypeData["name"];

				$tabs_li[] = '<li><a href="#tab-' . $uid . '"><span>' . $arrData['prefix'] . '</span></a></li>';
				$lang_form.='<table>';
				$lang_form.='<tr>';
				$lang_form.='<td>';
				$lang_form.='name';
				$lang_form.='</td>';
				$lang_form.='<td>';
				$lang_form.='<input type="text" name="name_' . $arrData['prefix'] . '" value="' . $nameVal . '" >';
				$lang_form.='</td>';
				$lang_form.='</tr>';
//	
				$lang_form.='</table>';
				$tabs_div[] = '<div id="tab-' . $uid . '">' . $lang_form . '</div>';
			}
		}

		$body->assign(
				array(
					'tabs' => implode('', $tabs_div),
					'locales' => implode('', $tabs_li),
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

		if (isset($this->parts[3]) && (int) $this->parts[3] > 0) {
			$objexercisetype = new exercisetype($this->parts[3]);

			$sql = "DELETE FROM `exercise_type_translation`";
			$sql.=" WHERE ";
			$sql.=" exercise_type_uid='{$this->parts[3]}'";
			$res = database::query($sql);

			$objexercisetype->delete();

			$objexercisetype->redirectTo('admin/exercisetype/list/');
		} else {
			output::redirect(config::url('admin/exercisetype/list/'));
		}
	}

	protected function doList() {
		$skeleton = new xhtml('skeleton.admin');
		$skeleton->load();
		$hide = 'style="visibility:hidden;"';
		$body = new xhtml('body.admin.exercisetype.list');
		$body->load();

		$this->objexercisetype = new exercisetype();
		$arrexercisetype = $this->objexercisetype->getListByname();
		$i = 0;
		if ($arrexercisetype && count($arrexercisetype) > 0) {
			$rows = array();
			foreach ($arrexercisetype as $exercise_type_uid => $arrData) {
				$i++;

				$row = new xhtml('body.admin.exercisetype.list.row');
				$row->load();
				$row->assign(array(
					'exercisetype_skill_uid' => $exercise_type_uid,
					'name' => stripslashes($arrData['name']),
					'available' => ($arrData['available'] == '0') ? 'No' : 'Yes'
				));
				$rows[] = $row->get_content();
			}
			$body->assign('rows', implode('', $rows));
		}
		$page_display_name = $this->objexercisetype->get_page_name('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $this->objexercisetype->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $this->objexercisetype->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ') . $this->objexercisetype->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		$body->assign('page.display.name', $page_display_name);
		$body->assign('page.navigation', $page_navigation);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);

		output::as_html($skeleton, true);
	}

}

?>