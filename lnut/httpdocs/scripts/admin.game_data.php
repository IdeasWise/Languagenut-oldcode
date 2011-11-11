<?php

class admin_game_data extends Controller {

	private $token		= 'list';
	private $arrTokens	= array (
		'list',
		'delete'
	);
	private $arrPaths	= array();//
	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($this->arrPaths[3]) && in_array($this->arrPaths[3], $this->arrTokens)) {
			$this->token =  $this->arrPaths[3];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	protected function doDelete() {
		if(isset($this->arrPaths[4]) && (int)$this->arrPaths[4] > 0) {
			$objGamedataTranslations = new gamedata_translations($this->arrPaths[4]);
			$objGamedataTranslations->delete();
			$objGamedataTranslations->redirectTo('admin/game_data/'.$this->arrPaths[2].'/');
		} else {
			output::redirect(config::url('admin/game_data/'.$this->arrPaths[2].'/'));
		}
	}

	protected function doList () {
		if(isset($this->arrPaths[2]) && is_numeric($this->arrPaths[2])) {
			$objGame = new game($this->arrPaths[2]);
			if($objGame->get_valid()) {
				$objGame->load();
				$skeleton	= make::tpl ('skeleton.admin');
				$body		= make::tpl ('body.admin.game_data');
				$body->assign('game_name',$objGame->get_name());
				$body->assign('game_uid',$objGame->get_uid());
				$objLanguage = new language();
				$gamedata_language_uid=14;
				if(isset($_POST['gamedata_language_uid'])) {
					$gamedata_language_uid = $_POST['gamedata_language_uid'];
				}
				
				$body->assign('language',$objLanguage->LanguageSelectBox('gamedata_language_uid',$gamedata_language_uid));

				$objGamedata = new gamedata();
				if(isset($_POST['save_default_pair'])) {
					if($objGamedata->doSave()===true) {
						$objGamedata->redirectTo('admin/game_data/'.$this->arrPaths[2].'/');
					} else {
						$body->assign($objGamedata->arrForm);
					}
				}

				$body->assign($objGamedata->getGameData(
					$objGame->get_uid(),
					$gamedata_language_uid
				));


				$objGamedataTranslations = new gamedata_translations();
				if(isset($_POST['add_new_pair'])) {
					if($objGamedataTranslations->doSave()===true) {
						$objGamedataTranslations->redirectTo('admin/game_data/'.$this->arrPaths[2].'/');
					} else {
						$body->assign($objGamedataTranslations->arrForm);
					}
				}

				if(isset($_POST['save_changes'])) {
					if($objGamedataTranslations->doSaveTranslations()===true) {
						$objGamedataTranslations->redirectTo('admin/game_data/'.$this->arrPaths[2].'/');
					} else {
						$body->assign($objGamedataTranslations->arrForm);
					}
				}

				$body->assign(
					'currnet_gamedata_translations',
					$this->getGameDataTranslation(
						$objGamedataTranslations,
						$objGamedataTranslations->getGameData(
							$objGame->get_uid(),
							$gamedata_language_uid
						)
					)
				);
				$skeleton->assign (
					array (
						'body' => $body
					)
				);
				output::as_html($skeleton,true);
			} else {
				output::redirect(config::url('admin/games/list/'));
			}
		} else {
			output::redirect(config::url('admin/games/list/'));
		}
	}

	public function getGameDataTranslation($objGamedataTranslations=null,$arrGameData=array()) {
		if($objGamedataTranslations==null || count($arrGameData)==0) {
			return '&nbsp;';
		} else {
			$body = make::tpl('body.admin.game_data_translation');
			$body->assign($objGamedataTranslations->arrForm);
			$arrRows = array();
			foreach($arrGameData as $arr) {
				if(isset($_POST['key'][$arr['uid']])) {
					$arr['key']		= $_POST['key'][$arr['uid']];
					$arr['value']	= $_POST['value'][$arr['uid']];
				}
				$arrRows[]=make::tpl('body.admin.game_data_translation.row')->assign($arr)->get_content();
			}
			$body->assign('rows',implode("",$arrRows));
			return $body->get_content();
		}
		return '&nbsp;';
	}
}

?>