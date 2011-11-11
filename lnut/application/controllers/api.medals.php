<?php

/**
 * api.medals.php
 */

class API_Medals extends Controller {

	public function __construct () {
		parent::__construct();

		$method = 'Names';
		$arrPaths = config::get('paths');
		if(isset($arrPaths[2]) && isset($arrPaths[3]) && !empty($arrPaths[2]) && !empty($arrPaths[3])) {
			$newMethod = $arrPaths[2].ucfirst($arrPaths[3]);
			if(method_exists($this,$newMethod)) {
				$method = $newMethod;
			}
		}
		$this->$method();
	}
	
	public function Names() {
		$language_uid = 14;
		if(isset($_REQUEST['language_uid']) && is_numeric($_REQUEST['language_uid'])) {
			$language_uid = (int)$_REQUEST['language_uid'];
		}
		$query ="SELECT ";
		$query.="`C`.`tag`, ";
		$query.="`CT`.`text` ";
		$query.="FROM ";
		$query.="`certificate_messages` AS `C`, ";
		$query.="`certificate_messages_translations` AS `CT` ";
		$query.="WHERE ";
		$query.="`message_uid`=`C`.`uid` ";
		$query.="AND ";
		$query.="`locale`='".language::getPrefix($language_uid)."' ";
		$query.="AND ";
		$query.="`C`.`tag` IN ('gold.medal','silver.medal','bronze.medal') ";
		$result = database::query($query);
		$arrMedals = array(
			'gold.medal'	=>'Gold',
			'silver.medal'	=>'Silver',
			'bronze.medal'	=>'Bronze'
		);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($arrRow = mysql_fetch_array($result)) {
				$arrMedals[$arrRow['tag']] = stripslashes($arrRow['text']);
			}
		}
		echo json_encode($arrMedals);
	}

	public function getMedals() {
		/*
		SELECT `G`.`uid` as `g_uid`, `G`.`name`, MAX(`score_right`) AS `score`, `section_uid` FROM `gamescore` AS `GS`, `game` AS `G` where `user_uid` = '4673' and `language_uid` = '7' and `unit_uid` = '1' and `is_unit_test` = '0' GROUP BY `section_uid`,`G`.`uid`
		*/
		$unit_uid = null;
		if(isset($_REQUEST['unit_uid']) && is_numeric($_REQUEST['unit_uid'])) {
			$unit_uid = $_REQUEST['unit_uid'];
		}
		$language_uid=null;
		if(isset($_REQUEST['language_uid']) && is_numeric($_REQUEST['language_uid'])) {
			$language_uid = $_REQUEST['language_uid'];
		}

		$user_uid = null;
		if(isset($_SESSION['user']['uid'])) {
			$user_uid = $_SESSION['user']['uid'];
		}
		if(isset($_REQUEST['user_uid']) && is_numeric($_REQUEST['user_uid'])) {
			$user_uid = $_REQUEST['user_uid'];
		}

		$query ="SELECT ";
		$query.="`G`.`uid` AS `g_uid`, ";
		$query.="`G`.`name`, ";
		$query.="MAX(`score_right`) AS `score`, ";
		$query.="`section_uid` ";
		$query.="FROM ";
		$query.="`gamescore` AS `GS`, ";
		$query.="`game` AS `G` ";
		$query.="WHERE ";
		$query.="`user_uid` = '".mysql_real_escape_string($user_uid)."' ";
		$query.="AND ";
		$query.="`language_uid` = '".mysql_real_escape_string($language_uid)."' ";
		$query.="AND ";
		$query.="`unit_uid` = '".mysql_real_escape_string($unit_uid)."' ";
		$query.="AND ";
		$query.="`is_unit_test` = '0' ";
		$query.="GROUP BY ";
		$query.="`GS`.`section_uid`, ";
		$query.="`G`.`uid`";
		echo $query.="ORDER BY `GS`.`section_uid` ";

		$result = database::query($query);

		$arrSections= array();
		$arrJson	= array();
		$array_index=0;
		if(mysql_error() == '' && mysql_num_rows($result)) {
			while($arrRow=mysql_fetch_array($result)) {
				if(!in_array($arrRow['section_uid'],$arrSections)) {
					$arrSections[] = $arrRow['section_uid'];
					$array_index = count($arrJson);
					$arrJson[] = array(
						'section_uid'	=>$arrRow['section_uid'],
						'medals'		=>array()
					);
				}
				$medalToken='';
				if($arrRow['score'] >= 85) {
					$medalToken='gold.medal';
				} else if($arrRow['score'] >= 70) {
					$medalToken='silver.medal';
				} else if($arrRow['score'] >= 50) {
					$medalToken='bronze.medal';
				}

				$arrJson[$array_index]['medals'][] = array(
					'game_uid'		=>$arrRow['g_uid'],
					//'game_name'		=>$arrRow['name'],
					'game_score'	=>$arrRow['score'],
					'medal'			=>$medalToken
				);
				
				
			}
		}

		if(count($arrJson)) {
			echo json_encode(
				array(
					'unit_uid'=>$unit_uid,
					'sections'=>$arrJson
				)
			);
		} else {
			echo json_encode(
				array(
					'success'=>'false'
				)
			);
		}
	}

	public function submitGamescore() {
		//mail('workstation@mystream.co.uk','json parse request',print_r($_POST,true),'From: developer@languagenut.com');

		if(isset($_POST['scoreData'])||isset($_POST['gameData'])) {
			$jsonScore = (isset($_POST['scoreData']) ? $_POST['scoreData'] : $_POST['gameData']);

			$objScore = json_decode($jsonScore);

			$objGameScore = new gamescore();

			if(($response=$objGameScore->create($objScore))!==false) {

				// calculate score value based on the game uid
				$scoreNum = -1;
				if($response['game_uid']==1) {
					$scoreNum = ($response['score_right']*100/($response['score_right']+$response['score_wrong']));
				} else if($response['game_uid']==2) {
					$scoreNum = ($response['score_right']*100/($response['score_right']+$response['score_wrong']));
				} else if($response['game_uid']==3) {
					$scoreNum = ($response['score_right']*100/($response['score_right']+$response['score_wrong']));
				} else if($response['game_uid']==4) {
					$scoreNum = ($response['score_right']*100/($response['score_right']+$response['score_wrong']));
				} else {
					$scoreNum = -1;
				}
				$scoreNum = number_format($scoreNum,2);

				$locale = config::get('locale');

				$query = "SELECT ";
				$query.= "(SELECT `text` FROM `certificate_messages_translations` WHERE `message_uid`=1 AND `locale`='$locale' LIMIT 1) AS `goldmedal`, ";
				$query.= "(SELECT `text` FROM `certificate_messages_translations` WHERE `message_uid`=2 AND `locale`='$locale' LIMIT 1) AS `silvermedal`, ";
				$query.= "(SELECT `text` FROM `certificate_messages_translations` WHERE `message_uid`=3 AND `locale`='$locale' LIMIT 1) AS `bronzemedal` ";

				$result = database::query($query);

				$goldMedal = '';
				$silverMedal = '';
				$bronzeMedal = '';

				//mail('workstation@mystream.co.uk','lnut-gamescores.php',$query,'From: developer@languagenut.com');

				if($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
					while($row = mysql_fetch_assoc($result)) {
						$goldMedal = stripslashes($row['goldmedal']);
						$silverMedal = stripslashes($row['silvermedal']);
						$bronzeMedal = stripslashes($row['bronzemedal']);
					}
				} else {
					echo mysql_error();
				}
				$phrase = '';
				$medalNum='';
				$medalToken='';
				if($scoreNum >= 85) {
					// get gold phrase
					$phrase = $goldMedal;
					$medalNum = 3;
					$medalToken='gold.medal';
				} else if($scoreNum >= 70) {
					// get silver phrase
					$phrase = $silverMedal;
					$medalNum = 2;
					$medalToken='silver.medal';
				} else if($scoreNum >= 50) {
					// get bronze phrase
					$phrase = $bronzeMedal;
					$medalNum = 1;
					$medalToken='bronze.medal';
				} else {
					// no phrase
					$phrase = '';
					$medalNum = 0;
				}
				/*
				echo json_encode(array(
					'success'		=> 'true',
					'game_uid'		=> $response['game_uid'],
					'language_uid'	=> $response['language_uid'],
					'user_uid'		=> $response['user_uid'],
					'time'			=> $response['time'],
					'score_for'		=> $response['score_right'],
					'score_against'	=> $response['score_wrong'],
					'medal'			=> $medalNum,
					'medalText'		=> $phrase,
					'score'			=> $scoreNum,
					'vocabulary'	=> $response['vocab']
				));
				*/
				echo json_encode(array(
					'success'		=> 'true',
					'game_uid'		=> $response['game_uid'],
					'language_uid'	=> $response['language_uid'],
					'score_for'		=> $response['score_right'],
					'score_against'	=> $response['score_wrong'],
					'medal'			=> $medalToken,
					'score'			=> $scoreNum
				));
			} else {
				echo json_encode(array(
					'success'		=> 'false'
				));
			}
		} else {
			echo json_encode(array(
				'success'		=> 'false'
			));
		}
	}
}


?>