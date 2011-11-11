<?php

class admin_package extends Controller {

	private $token = 'view';
	private $arrTokens = array(
		'view'
	);
	private $arrPaths		= array();	
	private $game_html		=null;

	public function __construct() {
		$this->doView();
	}
	
	protected function doView() {
		$skeleton = make::tpl('skeleton.admin');
		$body = make::tpl('body.admin.package.complete.view');

		$body->assign(
			array(
				'learnable_languages'=>$this->viewAllLearnableLanguages()
			)
		);

		$body->assign(
			array(
				'support_languages'=>$this->viewAllSupportLanguages()
			)
		);

		$body->assign(
			array(
				'tabs'=>$this->viewAllLearnableLanguageTabs()
			)
		);

		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	private function getGames($language_uid=null, $section_uid=null, $unit_uid=null) {
		if($this->game_html === null) {
			$arrGames = game::getGamesByGameNumber();
			$html='';
			foreach($arrGames as $row) {
				$html.= make::tpl('body.package.games.nocheckbox')->assign(
					$row
				)->get_content();
			}
			return $html;
		} else {
			return $this->game_html;
		}
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
			$result = database::query($query);
			$arrLi = array();
			$arrDiv = array();
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {

					$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
							array(
								'link_href'	=>'#unit-' . $l_uid . '-' . $row['uid'],
								'lable'		=>'Unit ' . $row['unit_number']
							)
						)->get_content();

					$arrDiv[] = make::tpl('body.general.tabs.div')->assign(
						array(
							'tab_id'		=>'unit-' . $l_uid . '-' . $row['uid'],
							'tab_content'	=>$this->getSections($row['uid'], $year_uid, $learnable_language_uid),
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

	private function getSections($unit_uid=null, $year_uid=null, $learnable_language_uid=null) {
		if ($year_uid != null && $unit_uid != null && $learnable_language_uid != null) {
			$l_uid = $learnable_language_uid;
			$query = "SELECT ";
			$query.="`uid`,";
			$query.="`name`,";
			$query.="`section_number` ";
			$query.="FROM ";
			$query.="`sections` ";
			$query.="WHERE ";
			$query.="`active` = '1' ";
			$query.="AND ";
			$query.="`unit_uid` = '" . $unit_uid . "'";
			$result = database::query($query);
			$arrLi = array();
			$arrDiv = array();
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {

					$arrLi[] = make::tpl('body.admin.general.tabs.li')->assign(
							array(
								'link_href'	=>'#section-' . $l_uid . '-' . $row['uid'],
								'lable'		=>'Section ' . $row['section_number']
							)
						)->get_content();

					$arrDiv[] = make::tpl('body.general.tabs.div')->assign(
						array(
							'tab_id'		=>'section-' . $l_uid . '-' . $row['uid'],
							'tab_content'	=>$this->getGames($l_uid,$row['uid'],$unit_uid),
							//'tab_content'	=>$this->getSections($row['uid'], $year_uid, $learnable_language_uid),
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
		return $html;
	}

	private function viewAllLearnableLanguages() {
		$arrLanguage = language::getAllAvailableLanguages();
		$arrLearnableLanguages = array();
		$body	=make::tpl('body.admin.package.language.ul');
		foreach ($arrLanguage as $arr) {
			$arrLearnableLanguages[] = make::tpl('body.admin.package.language.li')->assign($arr)->get_content();
		}
		$body->assign(
			array(
				'language_li'=>implode('',$arrLearnableLanguages)
			)
		);
		return $body->get_content();
	}

	private function viewAllSupportLanguages() {
		$arrLanguage = language::getAllSupportLanguages();
		$arrLearnableLanguages = array();
		$body	=make::tpl('body.admin.package.language.ul');
		foreach ($arrLanguage as $arr) {
			$arrLearnableLanguages[] = make::tpl('body.admin.package.language.li')->assign($arr)->get_content();
		}
		$body->assign(
			array(
				'language_li'=>implode('',$arrLearnableLanguages)
			)
		);
		return $body->get_content();
	}

	private function viewAllLearnableLanguageTabs() {
		$arrLanguage = language::getAllAvailableLanguages();
		$arrLi = array();
		$arrDiv = array();
		foreach ($arrLanguage as $row) {
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
		return make::tpl('body.admin.package.tabs.inner')->assign(
			array(
				'tabs.lis' => implode('', $arrLi),
				'tabs.divs' => implode('', $arrDiv)
			)
		)->get_content();
	}
}

?>