<?php

class controller_game_translations extends Controller {

	public function __construct () {
		parent::__construct();
		$this->index();
	}

	protected function index() {
		if(count($_POST) > 0) {
			game_translation::updateGameTranslation();
			output::redirect(config::admin_uri('game_translations/'));
		}
		if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1) {
			$skeleton	= make::tpl ('skeleton.admin');
			$arrLocales = language::getPrefixes();
		} else {
			$skeleton	= make::tpl ('skeleton.account.translator');
			$arrLocales = profile_translator::getPrefixes();
		}

		$objGame		= new game();
		$arrGames		= $objGame->getListByName();
		$arrTabs_li		= array();
		$arrTabs_div	= array();

		if(count($arrLocales) > 0) {
			foreach($arrLocales as $uid=>$arrData) {
				$arrTabs_li[] = make::tpl('body.admin.tabs.li')->assign(
					array(
						'tab_id'	=>$uid,
						'lable'		=>$arrData['prefix']
					)
				)->get_content();
				$arrTranslations = game_translation::getByLanguageUid($uid);

				$arrHtml = array();

				foreach($arrGames as $game_uid=>$arrGame) {
					//$arrHtml[] = '<tr><td>'.$arrGame['name'].'</td>';
					$found = false;
					foreach($arrTranslations as $translation_uid=>$arrTranslation) {
						if($arrTranslation['game_uid']==$game_uid) {
							$Html = make::tpl('body.admin.game_translation.row')->assign(
								array(
									'game_uid'			=>$game_uid,
									'uid'				=>$uid,
									'game_name'			=>$arrGame['name'],
									'translation_name'	=>$arrTranslation['name'],
									'instruction'		=>$arrTranslation['instruction']
								)
							);
							$found = true;
						}
					}
					if(!$found) {
						//$arrHtml[] = '<td><input type="text" name="game_'.$game_uid.'_'.$uid.'" id="game_'.$game_uid.'_'.$uid.'" value="" class="box" /></td>';
						$Html = make::tpl('body.admin.game_translation.row')->assign(
							array(
								'game_uid'			=>$game_uid,
								'uid'				=>$uid,
								'game_name'			=>$arrGame['name'],
								'translation_name'	=>'',
								'instruction'		=>''
							)
						);
					}
					$arrHtml[] = $Html->get_content();
				}

				$GameTable=make::tpl('body.game_translation.table')->assign(
					array(
						'language_name'	=>$arrData['name'],
						'table_content'	=>implode("",$arrHtml)
					)
				);

				$arrTabs_div[] = make::tpl('body.admin.tabs.div')->assign(
					array(
						'tab_id'		=>$uid,
						'tab_content'	=>$GameTable->get_content()
					)
				)->get_content();
			}
		}

		$body		= make::tpl ('body.account.game_translations.list')->assign(
			array(
				'tabs'	=> implode('',$arrTabs_div),
				'locales'=>implode('',$arrTabs_li),
				'form.action'	=> config::admin_uri('game_translations/')
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);

		output::as_html($skeleton,true);
	}
}

?>