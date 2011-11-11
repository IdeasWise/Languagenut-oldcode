<?php

/**
 * api.games.php
 */

class API_Games extends Controller {

	public function __construct () {
		parent::__construct();

		$method = 'getInvalidLink';
		$arrPaths = config::get('paths');
		if(isset($arrPaths[2]) && isset($arrPaths[3]) && !empty($arrPaths[2]) && !empty($arrPaths[3])) {
			$newMethod = $arrPaths[2].ucfirst($arrPaths[3]);
			if(method_exists($this,$newMethod)) {
				$method = $newMethod;
			}
		}
		$this->$method();
	}

	private function getInvalidLink() {
		die('Invalid Link!!!');
	}
	
	private function getData() {
		$arrJson = array('success'=>'false');
		if(isset($_REQUEST['language_uid'])) {

			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`language` ";
			$query.="WHERE ";
			$query.="`uid`='".mysql_real_escape_string($_REQUEST['language_uid'])."' ";
			$query.="LIMIT 0,1 ";
			$result = database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				$query ="SELECT ";
				$query.="`uid`, ";
				$query.="`name` ";
				$query.="FROM `game` ";
				$result = database::query($query);
				$arrJson = array();
				if(mysql_error()=='' && mysql_num_rows($result)) {
					while($arrRow=mysql_fetch_array($result)) {
						$arrJson[] = array(
							'game_uid'		=>$arrRow['uid'],
							'game_name'		=>$arrRow['name'],
							'instructions'	=>game_translation::getInstruction($arrRow['uid'],$_REQUEST['language_uid']),
							'game_data'	=>gamedata_translations::getGameDataTranslationForAPI(
								$arrRow['uid'],
								$_REQUEST['language_uid']
							)
						);
					}
				}
			} else {
				$arrJson['message'] = 'Invalid language_uid.';
			}
		}
		echo json_encode($arrJson);

	}
}


?>