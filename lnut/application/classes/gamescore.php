<?php

class gamescore extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getTopByUserGameLanguage ($user_uid=null,$game_uid=null,$language_uid=null) {

		$response = array ();
		if($user_uid && $game_uid && $language_uid) {
			$sql = "SELECT ";
			$sql.= "`uid`, ";
			$sql.= "`user_uid`, ";
			$sql.= "`game_uid`, ";
			$sql.= "`language_uid`, ";
			$sql.= "`time`, ";
			$sql.= "`score_right`, ";
			$sql.= "`score_wrong`, ";
			$sql.= "`recorded_dts` ";
			$sql.= "FROM ";
			$sql.= "`gamescore` ";
			$sql.= "WHERE ";
			$sql.= "`user_uid`='".mysql_real_escape_string($user_uid)."' ";
			$sql.= "AND `game_uid`='".mysql_real_escape_string($game_uid)."' ";
			$sql.= "AND `language_uid`='".mysql_real_escape_string($language_uid)."' ";
			$sql.= "ORDER BY ";
			$sql.= "`score_right` DESC, ";
			$sql.= "`time` ASC ";
			$sql.= "LIMIT 1";

			$res = database::query($sql);

			if($res && mysql_error()=='' && mysql_num_rows($res) > 0) {
				$response = mysql_fetch_assoc($res);
				$response['vocab'] = gamescore_vocab::getByGame($game_uid);
			}
		}

		return $response;

	}

	public function create ($jsonObject) {

		$user_uid		= $_SESSION['user']['uid'];
		$game_uid		= $jsonObject->game_uid;
		$language_uid	= $jsonObject->language_uid;
		$support_uid	= $jsonObject->support_language_uid;
		$is_unit_test	= (isset($jsonObject->is_unit_test) ? $jsonObject->is_unit_test : 0);
		$time			= $jsonObject->time;
		$score_for		= $jsonObject->score_right;
		$score_against	= $jsonObject->score_wrong;
		$vocab			= array ();

		$unit_uid		= 0;
		$section_uid	= 0;

		foreach($jsonObject->vocab as $vocabObj) {

			if(0 === $unit_uid && 0 === $section_uid) {
				$objSectionsVocabulary = new sections_vocabulary($vocabObj->vocab_uid);
				$objSectionsVocabulary->load();
				//mail('workstation@mystream.co.uk','LNUT gamescore',print_r($objSectionsVocabulary,true),'From: developer@languagenut.com');
				$section_uid = $objSectionsVocabulary->get_section_uid();

				$objSection = new sections($section_uid);
				$objSection->load();
				//mail('workstation@mystream.co.uk','LNUT gamescore',print_r($objSection,true),'From: developer@languagenut.com');
				$unit_uid = $objSection->get_unit_uid();
			}

			$vocab[$vocabObj->vocab_uid] = array (
				'for'		=> $vocabObj->score_right,
				'against'	=> $vocabObj->score_wrong,
				'time'		=> $vocabObj->time
			);
		}

		//mail('workstation@mystream.co.uk','LNUT PARSE',"user_uid:$user_uid \ngame_uid:$game_uid \nlanguage_uid:$language_uid \nis_unit_test:$is_unit_test \ntime:$time \nscore_for:$score_for \nscore_against:$score_against \nsection_uid:$section_uid \nunit_uid:$unit_uid ",'From: developer:languagenut.com');

		if($user_uid !== '' && $game_uid !== '' && $language_uid !== '' && $time !== '' && $score_for !== '' && $score_against !== '' && count($vocab) > 0) {

			$spelling_right = 0;
			$spelling_wrong = 0;
			/*
			if( ($score_for+$score_against) > 10) { // > 10 means a spelling game
				$spelling_right 	= $score_for;
				$spelling_wrong 	= $score_against;
				$total 			= 0;
				$total 			= ($score_for+$score_against);
				$score_for 		= round( (($score_for * 10 )/$total) );
				$score_against 	= (10  - $score_for) ;
			}
			*/
			if( ($score_for+$score_against) > 10) { // > 10 means a spelling game
				$spelling_right		= $score_for;
				$spelling_wrong		= $score_against;
				$total				= 0;
				$total				= ($score_for+$score_against);
				$score_for			= round( (($score_for * 10 )/$total) );
				$score_against		= (10 - $score_for);
			}
			// To set 0 to 100 logic we're multipling score with 10 because in previous logic we have 0 to 10
			$score_for		= ($score_for*10);
			$score_against	= ($score_against*10);
			// end of above multipling score....

			$this->set_user_uid($user_uid);
			$this->set_game_uid($game_uid);
			$this->set_language_uid($language_uid);
			$this->set_support_language_uid($support_uid);
			$this->set_unit_uid($unit_uid);
			$this->set_section_uid($section_uid);
			$this->set_is_unit_test($is_unit_test);
			$this->set_time($time);

			$this->set_score_right($score_for);
			$this->set_score_wrong($score_against);

			$this->set_spelling_right($spelling_right);
			$this->set_spelling_wrong($spelling_wrong);

			$this->set_recorded_dts(date('Y-m-d H:i:s'));

			$entry_uid = $this->insert();

			// FOLLOWING METHOD WILL CHECK USER'S MEDALS IF THAT USER GOT 4 BRONZ MEDAL THEN IT ENABLES NEXT SECTION FOR THE USER.
			$objUserSectionRights = new user_section_rights();
			$objUserSectionRights->EnableNextSectionBasedOnBronzeMedal($user_uid,$section_uid);

			if(mysql_error()!='') {
				mail('workstation@mystream.co.uk','LNUT: gamescore add error',mysql_error(),'From: developer@languagenut.com');
			}

			$objGameVocab = new gamescore_vocab();
			$objGameVocab->add($entry_uid, $language_uid, $support_uid, $vocab);

			$newVocab = array ();

			foreach($vocab as $uid=>$arr) {
				$newVocab[] = array (
					'vocab_uid'		=> $uid,
					'score_right'	=> $arr['for'],
					'score_wrong'	=> $arr['against'],
					'time'			=> $arr['time']
				);
			}

			return array(
				'game_uid'		=> $game_uid,
				'language_uid'	=> $language_uid,
				'user_uid'		=> $user_uid,
				'time'			=> $time,
				'score_right'	=> $score_for,
				'score_wrong'	=> $score_against,
				'vocab'			=> $newVocab
			);
		}

		return false;

	}

	public function isValidUpdate ($arrData=array()) {

		$game_uid				= (isset($arrData['game_uid']) && (int)$arrData['game_uid'] > 0) ? $arrData['game_uid'] : '';
		$name					= (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';
		$active_trynow			= (isset($arrData['active_trynow']) && (int)$arrData['active_trynow'] > -1) ? $arrData['active_trynow'] : '';
		$active_subscription	= (isset($arrData['active_subscription']) && (int)$arrData['active_subscription'] > -1) ? $arrData['active_subscription'] : '';

		if($game_uid != '' && $name != '' && $active_trynow != '' && $active_subscription != '') {

			$this->__construct($game_uid);
			$this->load();

			$this->arrFields['name']['Value']				= $name;
			$this->arrFields['active_trynow']['Value']		= $active_trynow;
			$this->arrFields['active_subscription']['Value']= $active_subscription;

			$this->save();

			return true;
		} else {
			$arrData['name'] = $name;
			$arrData['active_trynow'] = $active_trynow;
			$arrData['active_subscription'] = $active_subscription;
			$arrData['message'] = 'Please complete all fields';
		}

		return $arrData;

	}

	public function getFields () {
		$response = array();

		foreach($this->arrFields as $key=>$val) {
			$response[$key] = $this->arrFields[$key]['Value'];
		}

		return $response;
	}
	private function ResetPasswordsOfAllStudentsInClass($class_uid=null) {
		if($class_uid!=null && is_numeric($class_uid)) {
			$sql = "SELECT ";
			$sql .= "`S`.`uid`, ";
			$sql .= "`S`.`iuser_uid`, ";
			$sql .= "CONCAT(`vlastname`,' ',`vfirstname` ) as Name, ";
			$sql .= "`U`.`email`, ";
			$sql .= "`U`.`locale`, ";
			$sql .= "`S`.`wordbank_word` ";
			$sql .= "FROM `profile_student` as S, ";
			$sql .= "`classes_student` as SC, ";
			$sql .= "`user` AS `U` ";
			$sql .= "WHERE ";
			$sql .= "`U`.`uid` = `S`.`iuser_uid` ";
			$sql .= "and `U`.`deleted` != '1' ";
			$sql .= "and `S`.`uid` = `SC`.`student_uid` ";
			$sql .= "and `SC`.`class_uid` = '".$class_uid."' ORDER BY `vlastname` ";
			$result = database::query($sql);

			/* OLD RESET PASSWORD LOGIC
			$wordbank = new wordbank();

			if(mysql_num_rows( $result )) {
				while( $row = mysql_fetch_array($result)) {
					$word		= '';
					$word_term	= '';
					$word		= $wordbank->getRandomWord($row['locale']);
					if(is_array($word)) {
						$word_term = $word['term'];
					}
					if($word_term && $word_term !== null) {
						$word_term.= rand(0,10).rand(0,10).rand(0,10);
					}

					$student = new profile_student($row['uid']);
					$student->load();
					$student->set_wordbank_word($word_term);
					$student->save();

					$user = new user($row['iuser_uid']);
					$user->load();
					$user->set_password(md5($word_term));
					$user->save();
				}
			}

			*/
			// NEW LOGIC
			if(mysql_num_rows( $result )>0) {
				$objClassesStudent = new classes_student();
				while( $row = mysql_fetch_array($result)) {
					$objClassesStudent->setStudentPasswordForThisClass(
						$class_uid,
						$row['uid'],
						$row['locale']
					);
				}
			}
		}
	}

	public function getStudentsByClass($class_uid) {
		if(isset($_POST['form_reset_button'])) {
			$this->ResetPasswordsOfAllStudentsInClass($class_uid);
		}

		/*
		$query="SELECT `wordbank_word`,`student_uid` FROM `classes_student` AS `CS`, `profile_student` AS `PS` WHERE `student_uid` = `PS`.`uid` GROUP BY `student_uid`";
		$result=database::query($query);
		while($row=mysql_fetch_array($result)) {
			$query="UPDATE `classes_student` SET `student_password`='".$row['wordbank_word']."' WHERE `student_uid`='".$row['student_uid']."'";
			database::query($query);
		}
		*/

		$sql = "SELECT ";
		$sql .= "`S`.`uid`, ";
		$sql .= "`S`.`iuser_uid`, ";
		$sql .= "CONCAT(`vlastname`,' ',`vfirstname` ) AS `Name`, ";
		$sql .= "`U`.`email`, ";
		$sql .= "`U`.`locale`, ";
		$sql .= "`SC`.`student_password` AS `wordbank_word` ";
		$sql .= "FROM `profile_student` AS `S`, ";
		$sql .= "`classes_student` AS `SC`, ";
		$sql .= "`user` AS `U` ";
		$sql .= "WHERE ";
		$sql .= "`U`.`uid` = `S`.`iuser_uid` ";
		$sql .= "and `U`.`deleted` != '1' ";
		$sql .= "and `S`.`uid` = `SC`.`student_uid` ";
		$sql .= "and `SC`.`class_uid` = '".$class_uid."' ORDER BY `vlastname` ";
		$result = database::query($sql);
		$data = array();
		if(mysql_num_rows( $result )) {
			while( $row = mysql_fetch_array($result)) {
				$data[] = $row;
			}
		}
		return $data;
	}

	public function getClassStudents($class_uid) {

		$query = "SELECT count(`SC`.`uid`) FROM ";
		$query.= "FROM ";
		$query.= "`profile_student` AS `S`, ";
		$query.= "`classes_student` AS `SC`, ";
		$query.= "`user` AS `U` ";
		$query.= "WHERE ";
		$query.= "`U`.`uid` = `S`.`iuser_uid` ";
		$query.= "and `U`.`deleted` != '1' ";
		$query.= "and `S`.`uid` = `SC`.`student_uid` ";
		$query.= "and `SC`.`class_uid` = '".$class_uid."'";
		$this->setPagination( $query );

		$query= "SELECT ";
		$query.= "`S`.`uid`, ";
		$query.= "`S`.`iuser_uid`, ";
		$query.= "`SC`.`class_uid`, ";
		$query.= "CONCAT(`vlastname`,' ',`vfirstname` ) AS `Name`, ";
		$query.= "`U`.`email`, ";
		$query.= "`U`.`locale`, ";
		$query.= "`SC`.`student_password` AS `wordbank_word` ";
		$query.= "FROM ";
		$query.= "`profile_student` AS `S`, ";
		$query.= "`classes_student` AS `SC`, ";
		$query.= "`user` AS `U` ";
		$query.= "WHERE ";
		$query.= "`U`.`uid` = `S`.`iuser_uid` ";
		$query.= "and `U`.`deleted` != '1' ";
		$query.= "and `S`.`uid` = `SC`.`student_uid` ";
		$query.= "and `SC`.`class_uid` = '".$class_uid."' ORDER BY `vlastname` ";
		$query.= "LIMIT ".$this->get_limit();
		return database::arrQuery($query);
	}

	public function getClassUsersAndScores ($class_uid,$language_uid,$unit_uid,$sections ) {

		$html = '';
		$sql = "SELECT ";
		$sql .= "`S`.`uid`, ";
		$sql .= "`S`.`iuser_uid`, ";
		$sql .= "CONCAT(`vlastname`,', ',`vfirstname` ) AS `Name`, ";
		$sql .= "`U`.`email` ";
		$sql .= "FROM `profile_student` AS `S`, ";
		$sql .= "`classes_student` AS `SC`, ";
		$sql .= "`user` AS `U` ";
		$sql .= "WHERE ";
		$sql .= "`U`.`uid` = `S`.`iuser_uid` ";
		$sql .= "and `U`.`deleted` != '1' ";
		$sql .= "and `S`.`uid` = `SC`.`student_uid` ";
		$sql .= "and `SC`.`class_uid` = '".$class_uid."' ";
		$sql .= "ORDER BY `vlastname` ";
		$result = database::query($sql);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$html .='<tr>';
				$html .='<td>'.$row['Name'].'</td>';

				if(!empty($sections)) {
					foreach($sections as $uid=>$data) {
						//SELECT `G`.`uid` as g_uid  FROM `game` as `G` WHERE 1
						// SECTION'S SCORES SECTION START HERE
						$query = "SELECT ";
						$query .= "`G`.`uid` as `g_uid`, ";
						$query .= "`G`.`name`, ";
						$query .= "`G`.`class_name`, ";
						$query .= "( ";
							$query .= "SELECT ";
							$query .= "CONCAT(`score_right`,'-',`uid`) ";
							$query .= "FROM `gamescore` ";
							$query .= "where ";
							$query .= "`game_uid` = `g_uid` ";
							$query .= "and `user_uid` = '".$row['iuser_uid']."' ";
							$query .= "and `language_uid` = '".$language_uid."' ";
							$query .= "and `section_uid` = '".$data['uid']."' ";
							$query .= "and `is_unit_test` = '0' ";
							$query .= " ORDER BY `score_right` DESC LIMIT 0,1";
						$query .= ") as `g_score` ";
						$query .= "FROM `game` as `G` where `G`.`playable` = '1' ORDER BY `G`.`game_number`";

						$result2 = database::query($query);
						if($result2 && mysql_error()=='' && mysql_num_rows($result2) > 0) {
							while($score = mysql_fetch_assoc($result2)) {
								$class = '';
								$score['gscore_uid'] = '';
								if( !is_null( $score['g_score'] ) )
									list($score['g_score'], $score['gscore_uid']) = explode('-',$score['g_score']);

								if( is_numeric($score['g_score'])) {
									if($score['g_score'] <= 40 )
										$class = 'medal_bronze';
									else if($score['g_score'] <= 80 )
										$class = 'medal_silver';
									else if($score['g_score'] > 80 )
										$class = 'medal_gold';
								}
								if(!is_null($score['g_score'])) {
									$html .='<td class="'.$class.'" title="Game: '.$score['name'].' "><a href="'.config::admin_uri('classes/get-certificate/'.$class_uid.'/'.$language_uid.'/'.$score['gscore_uid']).'" target="_blank">'.$score['g_score'].'</a></td>';
								} else {
									$html .='<td class="'.$class.'" title="Game: '.$score['name'].' ">-</td>';
								}
							}
						}
						// SECTION'S SCORES SECTION END HERE

					}

					// UNIT'S SCORE SECTION START HERE...

						$query = "SELECT ";
						$query .= "`G`.`uid` as `g_uid`, ";
						$query .= "`G`.`name`, ";
						$query .= "`G`.`class_name`, ";
						$query .= "( ";
							$query .= "SELECT ";
							$query .= "CONCAT(`score_right`,'-',`uid`) ";
							$query .= "FROM `gamescore` ";
							$query .= "where ";
							$query .= "`game_uid` = `g_uid` ";
							$query .= "and `user_uid` = '".$row['iuser_uid']."' ";
							$query .= "and `language_uid` = '".$language_uid."' ";
							$query .= "and `unit_uid` = '".$unit_uid."' ";
							$query .= "and `is_unit_test` = '1'";
							$query .= " ORDER BY `score_right` DESC LIMIT 0,1";
						$query .= ") as `g_score` ";
						$query .= "FROM ";
						$query .= "`game` as `G` ";
						$query .= "where ";
						$query .= "`G`.`playable` = '1' ";
						$query .= "ORDER BY `G`.`game_number`";

						$result3 = database::query($query);
						if($result3 && mysql_error()=='' && mysql_num_rows($result3) > 0) {
							while($score = mysql_fetch_assoc($result3)) {
								$class = '';
								$score['gscore_uid'] = '';
								if( !is_null( $score['g_score'] ) )
									list($score['g_score'], $score['gscore_uid']) = explode('-',$score['g_score']);

								if( is_numeric($score['g_score'])) {
									if($score['g_score'] <= 40 )
										$class = 'medal_bronze';
									else if($score['g_score'] <= 80 )
										$class = 'medal_silver';
									else if($score['g_score'] > 80 )
										$class = 'medal_gold';
								}
								if(!is_null($score['g_score'])) {
									$html .='<td class="'.$class.'" title="Game: '.$score['name'].' "><a href="'.config::admin_uri('classes/get-certificate/'.$class_uid.'/'.$language_uid.'/'.$score['gscore_uid']).'" target="_blank">'.$score['g_score'].'</a></td>';
								} else {
									$html .='<td class="'.$class.'" title="Game: '.$score['name'].' ">-</td>';
								}
							}
						}
				}
				$html .='</tr>';
			}
		}
		return $html;
	}



	public function getClassUsersAndScoresForUnit ($class_uid,$language_uid,$units_uids ) {
		$html = '';
		$sql = "SELECT ";
		$sql .= "`S`.`uid`, ";
		$sql .= "`S`.`iuser_uid`, ";
		$sql .= "CONCAT(`vlastname`,', ',`vfirstname` ) AS `Name`, ";
		$sql .= "`U`.`email` ";
		$sql .= "FROM `profile_student` AS `S`, ";
		$sql .= "`classes_student` AS `SC`, ";
		$sql .= "`user` AS `U` ";
		$sql .= "WHERE ";
		$sql .= "`U`.`uid` = `S`.`iuser_uid` ";
		$sql .= "and `U`.`deleted` != '1' ";
		$sql .= "and `S`.`uid` = `SC`.`student_uid` ";
		$sql .= "and `SC`.`class_uid` = '".$class_uid."' ";
		$sql .= "ORDER BY `vlastname` ";


		$result = database::query($sql);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$html .='<tr>';
				$html .='<td>'.$row['Name'].'</td>';

				if(!empty($units_uids)) {
					foreach($units_uids as $uid=>$data) {
						$query = "SELECT ";
						$query .= "`G`.`uid` as `g_uid`, ";
						$query .= "`G`.`name`, ";
						$query .= "`G`.`class_name`, ";
						$query .= "( ";
							$query .= "SELECT ";
							$query .= "CONCAT(`score_right`,'-',`uid`) ";
							$query .= "FROM `gamescore` ";
							$query .= "where ";
							$query .= "`game_uid` = `g_uid` ";
							$query .= "and `user_uid` = '".$row['iuser_uid']."' ";
							$query .= "and `language_uid` = '".$language_uid."' ";
							$query .= "and `unit_uid` = '".$data['uid']."'";
							$query .= "and `is_unit_test` = '1'";
							$query .= " ORDER BY `score_right` DESC LIMIT 0,1";
						$query .= ") as `g_score` ";
						$query .= "FROM ";
						$query .= "`game` as `G` ";
						$query .= "where ";
						$query .= "`G`.`playable` = '1' ";
						$query .= "ORDER BY `G`.`game_number`";

						$result2 = database::query($query);
						if($result2 && mysql_error()=='' && mysql_num_rows($result2) > 0) {
							while($score = mysql_fetch_assoc($result2)) {
								$class = '';
								$score['gscore_uid'] = '';
								if( !is_null( $score['g_score'] ) ) {
									list($score['g_score'], $score['gscore_uid']) = explode('-',$score['g_score']);
								}

								if( is_numeric($score['g_score'])) {
									if($score['g_score'] <= 40 ) {
										$class = 'medal_bronze';
									} else if($score['g_score'] <= 80 ) {
										$class = 'medal_silver';
									} else if($score['g_score'] > 80 ) {
										$class = 'medal_gold';
									}
								}

								if(!is_null($score['g_score'])) {
									$html .='<td class="'.$class.'" title="Game: '.$score['name'].' "><a href="'.config::admin_uri('classes/get-certificate/'.$class_uid.'/'.$language_uid.'/'.$score['gscore_uid']).'" target="_blank">'.$score['g_score'].'</a></td>';
								} else {
									$html .='<td class="'.$class.'" title="Game: '.$score['name'].' ">-</td>';
								}
							}
						}
					}
				}
				$html .='</tr>';

			}
		}
		return $html;
	}

	public function getScoresBySectionAndUser($section_id=null, $user_uid=null, $language_uid=null) {
		$scores = array();
		if($user_uid && $section_id && $language_uid) {
			$query = "SELECT ";
			$query.= "`game_uid`, ";
			$query.= "`is_unit_test`, ";
			$query.= "`time`, ";
			$query.= "`score_right`, ";
			$query.= "`score_wrong` ";
			$query.= "FROM ";
			$query.= "`gamescore` ";
			$query.= "WHERE ";
			$query.= "`user_uid`='".mysql_real_escape_string($user_uid)."' ";
			$query.= "AND `section_uid`='".mysql_real_escape_string($section_id)."' ";
			$query.= "AND `language_uid`='".mysql_real_escape_string($language_uid)."' ";
			$query.= "AND `is_unit_test`=0 ";
			$query.= "ORDER BY `game_uid` ASC";

			$result = database::query($query);

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				$max = array ();
				while($row = mysql_fetch_assoc($result)) {
					if($row['game_uid']==1) {
						$thismax = ($row['score_right']*100/($row['score_right']+$row['score_wrong']));
					} else if($row['game_uid']==2) {
						$thismax = ($row['score_right']*100/($row['score_right']+$row['score_wrong']));
					} else if($row['game_uid']==3) {
						$thismax = ($row['score_right']*100/($row['score_right']+$row['score_wrong']));
					} else if($row['game_uid']==4) {
						$thismax = ($row['score_right']*100/($row['score_right']+$row['score_wrong']));
					} else {
						$thismax = -1;
					}
					if(!isset($max[$row['game_uid']])) {
						$max[$row['game_uid']] = 0;
					}
					if($thismax > $max[$row['game_uid']]) {
						$max[$row['game_uid']] = $thismax;
					}
					//$scores[$row['game_uid']] = $row;
				}
				$scores = $max;
			}
		}
		return $scores;
	}

	public function getGameScoreDetail ( $gamescore_uid, $language_uid ) {
		$data = array();
		$right_words = array();
		$Words = array();
		$wrong_words = array();
		$Mgessages = array();
		$images = array();

		$query ="SELECT ";
		$query.="`GS`.*, ";
		$query.="`GT`.`name` AS `GameName`, ";
		$query.="CONCAT(`PS`.`vfirstname`,' ', `PS`.`vlastname`) AS `StudentName`, ";
		$query.="`PS`.`uid` AS `student_uid`, ";
		$query.="`UT`.`name` AS `Unit`, ";
		$query.="`ST`.`name` AS `Section`, ";
		$query.="`unit_number`, ";
		$query.="`section_number`, ";
		$query.="`USER`.`locale` ";
		$query.="FROM `gamescore` as `GS`, ";
		$query.="`profile_student` as `PS`, ";
		$query.="`game_translation` as `GT`, ";
		$query.="`units_translations` as `UT`, ";
		$query.="`sections_translations` as `ST`, ";
		$query.="`units` as `U`, ";
		$query.="`sections` as `S`, ";
		$query.="`user` as `USER` ";
		$query.="WHERE ";
		$query.="`GS`.`user_uid` = `PS`.`iuser_uid` ";
		$query.="AND ";
		$query.="`GS`.`game_uid` = `GT`.`game_uid` ";
		$query.="AND ";
		$query.="`GT`.language_uid = '".$language_uid."' ";
		$query.="AND ";
		$query.="`GS`.`unit_uid` = `U`.`uid` ";
		$query.="AND ";
		$query.="`GS`.`unit_uid` = `UT`.`unit_id` ";
		$query.="AND ";
		$query.="`UT`.language_id = '".$language_uid."' ";
		$query.="AND ";
		$query.="`GS`.`section_uid` = `S`.`uid` ";
		$query.="AND ";
		$query.="`GS`.`section_uid` = `ST`.`section_uid` ";
		$query.="AND ";
		$query.="`ST`.language_id = '".$language_uid."' ";
		$query.="AND ";
		$query.="`USER`.`uid` = `GS`.`user_uid` ";
		$query.="AND ";
		$query.="`GS`.`uid` = '".$gamescore_uid."'";

		$data = database::arrQuery($query);
		if(count($data) > 0) {
			$data[0]['right_words'] = '';
			$data[0]['wrong_words'] = '';
			// GET WORDS: You learnt these words perfectly:
			$query ="SELECT ";
			$query.="`word_translated` ";
			$query.="FROM ";
			$query.="`gamescore_vocab` ";
			$query.="WHERE ";
			$query.="`gamescore_uid` = '".$gamescore_uid."' ";
			$query.="AND ";
			$query.="`score_right` = '1' ";
			$query.="AND ";
			$query.="`score_wrong` = '0'";
			$Words = database::arrQuery($query);
			if(count($Words) > 0) {
				for($i = 0; $i < count($Words); $i++ ) {
					$right_words[] = $Words[$i]['word_translated'];
				}
				$data[0]['right_words']  = iconv("UTF-8", "cp1252",stripslashes(@implode(', ',$right_words)));
			}

			// GET WORDS: Work on these words:
			$query ="SELECT ";
			$query.="`word_translated` ";
			$query.="FROM ";
			$query.="`gamescore_vocab` ";
			$query.="WHERE ";
			$query.="`gamescore_uid` = '".$gamescore_uid."' ";
			$query.="AND ";
			$query.="`score_right` = '0' ";
			$query.="AND ";
			$query.="`score_wrong` = '1'";
			$Words = array();
			$Words = database::arrQuery($query);
			if(count($Words) > 0) {
				for($i = 0; $i < count($Words); $i++ ) {
					$wrong_words[] = $Words[$i]['word_translated'];
				}
				$data[0]['wrong_words']  = iconv("UTF-8", "cp1252", stripslashes(@implode(', ',$wrong_words)));
			}
			unset($Words);
			// GET CERTIFICATE MESSAGE TRANSLATIONS
			$query = "SELECT ";
			$query.="`CM`.`tag`, ";
			$query.="( ";
				$query.="SELECT ";
				$query.="`text` ";
				$query.="FROM ";
				$query.="`certificate_messages_translations` as `CMT`";
				$query.="WHERE ";
				$query.="`CMT`.`message_uid` = `CM`.`uid` ";
				$query.="AND ";
				$query.="`CMT`.`locale` = '".$data[0]['locale']."' ";
				$query.="LIMIT 1";
			$query.=") ";
			$query.="AS `text` ";
			$query.="FROM ";
			$query.="`certificate_messages` as `CM`";
			$Mgessages = database::arrQuery($query);

			for($i = 0;  $i < count($Mgessages); $i++ ) {
				$data[0][$Mgessages[$i]['tag']] = iconv("UTF-8", "cp1252", stripslashes($Mgessages[$i]['text']));
			}
			unset($Mgessages);

			// GET CERTIFICATE IMAGES
			$query ="SELECT ";
			$query.="`logo_url`,";
			$query.="`gold_bg`, ";
			$query.="`silver_bg`,";
			$query.="`bronze_bg` ";
			$query.="FROM ";
			$query.="`language` ";
			$query.="WHERE ";
			$query.="`prefix` = '".$data[0]['locale']."' ";
			$query.="LIMIT 1";
			$images = database::arrQuery($query);
			if(count($images) > 0 ) {
				$data[0] = array_merge($data[0], $images[0]);
			}

			$sizes = array();
			// GET CERTIFICATE GOLD, SILVER BORNZE HEADER SIZES
			$query = "SELECT ";
			$query.="`gold_size`, ";
			$query.="`silver_size`, ";
			$query.="`bronze_size` ";
			$query.="FROM ";
			$query.="`certificate_font_size` ";
			$query.="WHERE ";
			$query.="`locale` = '".$data[0]['locale']."' ";
			$query.="LIMIT 1";
			$sizes = database::arrQuery($query);

			if(count($sizes) > 0 ) {
				$data[0] = array_merge($data[0], $sizes[0]);
			}

			$data[0]['url'] = 'www.languagenut.com/'.$data[0]['locale'];

			if( is_numeric($data[0]['score_right'])) {
				if($data[0]['score_right'] <= 40 ) {
					$data[0]['medal'] = $data[0]['bronze.medal'];
					$data[0]['font_size'] = $data[0]['bronze_size'];
					$data[0]['background'] = $data[0]['bronze_bg'];
					$data[0]['cong.msg'] = $data[0]['bronze.congratulations.medal'];

				} else if($data[0]['score_right'] <= 80 ) {
					$data[0]['medal'] = $data[0]['silver.medal'];
					$data[0]['font_size'] = $data[0]['silver_size'];
					$data[0]['background'] = $data[0]['silver_bg'];
					$data[0]['cong.msg'] = $data[0]['silver.congratulations.medal'];
				} else if($data[0]['score_right'] > 80 ) {
					$data[0]['medal'] = $data[0]['gold.medal'];
					$data[0]['font_size'] = $data[0]['gold_size'];
					$data[0]['background'] = $data[0]['gold_bg'];
					$data[0]['cong.msg'] = $data[0]['gold.congratulations.medal'];
				}
			}
		}
		return $data;
	}

}

?>