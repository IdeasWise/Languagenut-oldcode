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
				$arrTabs_li[] = '<li><a href="#tab-'.$uid.'"><span>'.$arrData['prefix'].'</span></a></li>';

				$arrTranslations = game_translation::getByLanguageUid($uid);

				$arrHtml = array();
				$arrHtml[] = '<table width="100%" border="0" cellspacing="0" cellpadding="10" class="table_main"><tr><th>Game Name</th><th>'.$arrData['name'].'</th></tr>';
				foreach($arrGames as $game_uid=>$arrGame) {
					$arrHtml[] = '<tr><td>'.$arrGame['name'].'</td>';
					$found = false;
					foreach($arrTranslations as $translation_uid=>$arrTranslation) {
						if($arrTranslation['game_uid']==$game_uid) {
							$arrHtml[] = '<td><input type="text" name="game_'.$game_uid.'_'.$uid.'" id="game_'.$game_uid.'_'.$uid.'" value="'.$arrTranslation['name'].'" class="box" /></td>';
							$found = true;
						}
					}
					if(!$found) {
						$arrHtml[] = '<td><input type="text" name="game_'.$game_uid.'_'.$uid.'" id="game_'.$game_uid.'_'.$uid.'" value="" class="box" /></td>';
					}
					$arrHtml[] = '</tr>';
				}
				$arrHtml[] = '</table>';
				$arrTabs_div[] = '<div id="tab-'.$uid.'">'.implode('',$arrHtml).'</div>';
			}
		}

		$body		= make::tpl ('body.game_translations.list')->assign(
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