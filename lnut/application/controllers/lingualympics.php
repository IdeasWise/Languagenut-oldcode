<?php

/**
 * lingualympics.php
 */

class Lingualympics extends Controller {

	public $multiplyScoreBy = 1000000;

	public function __construct () {
		parent::__construct();

		$this->page();
	}

	protected function page () {
		$arrPaths = config::get('paths');
		if(isset($arrPaths[1]) && $arrPaths[1]=='cron' ) {
			$this->Cron();
		} else {
			$this->page_default();
		}
	}

	protected function Cron() {

		$query = "DELETE FROM `lingualympics`";
		database::query($query);

		// STARTS SCHOOL QUERY HERE
		$query ="SELECT ";
		$query.="`G`.`user_uid`,";
		$query.="`G`.`time`, ";
		$query.="`G`.`language_uid`, ";
		$query.="MAX(`score_right`) AS `MxScore`, ";
		$query.="`S`.`school`, ";
		$query.="`U`.`locale` ";
		$query.="FROM ";
		$query.="`gamescore` AS `G`, ";
		$query.="`users_schools` AS `S`, ";
		$query.="`user` AS `U` ";
		$query.="WHERE ";
		$query.="`S`.`user_uid`=`G`.`user_uid` ";
		$query.="AND ";
		$query.="`S`.`user_uid`=`U`.`uid` ";
		$query.="AND ";
		$query.="`recorded_dts` > '".date('Y-m-d 00:00:00',strtotime('-7 day'))."' ";
		//$query.="AND ";
		//$query.="`G`.`language_uid` = '".$arrLang['uid']."'";
		$query.="GROUP BY `G`.`game_uid`, `G`.`user_uid` ";
		//$query.="ORDER BY `G`.`user_uid`,`G`.`game_uid`";
		$query.="ORDER BY `MxScore` DESC ";
		$query.="LIMIT 0,20 ";


		$arrScore = array();
		$arrNames = array();
		$arrFlags = array();
		$arrLangs = array();
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row = mysql_fetch_array($result)) {
				$arrNames[$row['user_uid']] = $row['school'];
				$arrLangs[$row['user_uid']] = $row['language_uid'];
				if(isset($arrScore[$row['user_uid']])) {
					$arrScore[$row['user_uid']] += ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
				} else {
					$arrScore[$row['user_uid']] = ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
				}
				$arrFlags[$row['user_uid']] = $row['locale'];
			}
		}
		arsort($arrScore);
		$arrRes = array_slice($arrScore,0,20,true);
		foreach($arrRes as $uid => $Score ) {
			$arrdata = array(
				'name'			=> addslashes($arrNames[$uid]),
				'score'			=> $Score,
				'locale'		=> $arrFlags[$uid],
				'user_uid'		=> $uid,
				'section'		=> 'schools',
				'language_uid'	=> $arrLangs[$uid]
			);
			$this->AddRow($arrdata);
		}

		// START STUDENTS QUERY HERE
		//foreach($arrLanguage as $arrLang) {
			$query ="SELECT ";
			$query.="`G`.`user_uid`,";
			$query.="`G`.`time`, ";
			$query.="`G`.`language_uid`, ";
			$query.="MAX(`score_right`) AS `MxScore` , ";
			$query.="`vfirstname`, ";
			$query.="`vlastname`, ";
			$query.="`S`.`school`, ";
			$query.="`U`.`locale` ";
			$query.="FROM ";
			$query.="`gamescore` AS `G`, ";
			$query.="`profile_student` AS `PS`, ";
			$query.="`user` AS `U`, ";
			$query.="`users_schools` AS `S` ";
			$query.="WHERE ";
			$query.="`PS`.`iuser_uid`=`G`.`user_uid` ";
			$query.="AND ";
			$query.="`PS`.`iuser_uid`=`U`.`uid` ";
			$query.="AND ";
			$query.="`PS`.`school_id`=`S`.`uid` ";
			$query.="AND ";
			$query.="`recorded_dts` > '".date('Y-m-d 00:00:00',strtotime('-7 day'))."' ";
			//$query.="AND ";
			//$query.="`G`.`language_uid` = '".$arrLang['uid']."'";
			$query.="GROUP BY `G`.`game_uid`, `G`.`user_uid` ";
			//$query.="ORDER BY `G`.`user_uid`,`G`.`game_uid`";
			$query.="ORDER BY `MxScore` DESC ";
			$query.="LIMIT 0,10 ";

			$arrScore = array();
			$arrNames = array();
			$arrFlags = array();
			$arrLangs = array();
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row = mysql_fetch_array($result)) {
					$arrLangs[$row['user_uid']] = $row['language_uid'];
					$arrNames[$row['user_uid']] = $row['vfirstname'].' '.substr(strtoupper($row['vlastname']),0,1).', '.preg_replace('/[0-9]/','',ucwords(strtolower(str_replace(array('[',']','_'),array('','',' '),$row['school']))));
					if(isset($arrScore[$row['user_uid']])) {
						$arrScore[$row['user_uid']] += ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					} else {
						$arrScore[$row['user_uid']] = ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					}
					$arrFlags[$row['user_uid']] = $row['locale'];
				}
			}
			arsort($arrScore);
			$arrRes = array_slice($arrScore,0,10,true);
			foreach($arrRes as $uid => $Score ) {
				$arrdata = array(
					'name'			=> addslashes($arrNames[$uid]),
					'score'			=> $Score,
					'locale'		=> $arrFlags[$uid],
					'user_uid'		=> $uid,
					'section'		=> 'students',
					'language_uid'	=> $arrLangs[$uid]
				);
				$this->AddRow($arrdata);
			}
		//}

		// START HOMEUSERS QUERY HERE
	//	foreach($arrLanguage as $arrLang) {
			$query ="SELECT ";
			$query.="`G`.`user_uid`,";
			$query.="`G`.`time`, ";
			$query.="`G`.`language_uid`, ";
			$query.="MAX(`score_right`) AS `MxScore` , ";
			$query.="`vfirstname`, ";
			$query.="`vlastname`, ";
			$query.="`U`.`locale` ";
			$query.="FROM ";
			$query.="`gamescore` AS `G`, ";
			$query.="`profile_homeuser` AS `HM`, ";
			$query.="`user` AS `U` ";
			$query.="WHERE ";
			$query.="`HM`.`iuser_uid`=`G`.`user_uid` ";
			$query.="AND ";
			$query.="`HM`.`iuser_uid`=`U`.`uid` ";
			$query.="AND ";
			$query.="`recorded_dts` > '".date('Y-m-d 00:00:00',strtotime('-7 day'))."' ";
			//$query.="AND ";
			//$query.="`G`.`language_uid` = '".$arrLang['uid']."'";
			$query.="GROUP BY `G`.`game_uid`, `G`.`user_uid` ";
			//$query.="ORDER BY `G`.`user_uid`,`G`.`game_uid`";
			$query.="ORDER BY `MxScore` DESC ";
			$query.="LIMIT 0,10 ";

			$arrScore = array();
			$arrNames = array();
			$arrFlags = array();
			$arrLangs = array();
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row = mysql_fetch_array($result)) {
					$arrLangs[$row['user_uid']] = $row['language_uid'];
					$arrNames[$row['user_uid']] = $row['vlastname'].' '.$row['vfirstname'];
					if(isset($arrScore[$row['user_uid']])) {
						$arrScore[$row['user_uid']] += ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					} else {
						$arrScore[$row['user_uid']] = ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					}
					$arrFlags[$row['user_uid']] = $row['locale'];
				}
			}
			arsort($arrScore);
			$arrRes = array_slice($arrScore,0,10,true);
			foreach($arrRes as $uid => $Score ) {
				$arrdata = array(
					'name'			=> addslashes($arrNames[$uid]),
					'score'			=> $Score,
					'locale'		=> $arrFlags[$uid],
					'user_uid'		=> $uid,
					'section'		=> 'homeusers',
					'language_uid'	=> $arrLangs[$uid]
				);
				$this->AddRow($arrdata);
			}
		//}

	}




	protected function Cron_old() {
		$query = "DELETE FROM `lingualympics`";
		database::query($query);

		$query = "SELECT `uid`, `prefix` FROM `language` WHERE `is_learnable` = '1' ";
		$arrLanguage = database::arrQuery($query);

		// STARTS SCHOOL QUERY HERE
		foreach($arrLanguage as $arrLang) {
			$query ="SELECT ";
			$query.="`G`.`user_uid`,";
			$query.="`G`.`time`, ";
			$query.="MAX(`score_right`) AS `MxScore` , ";
			$query.="`S`.`school`, ";
			$query.="`U`.`locale` ";
			$query.="FROM ";
			$query.="`gamescore` AS `G`, ";
			$query.="`users_schools` AS `S`, ";
			$query.="`user` AS `U` ";
			$query.="WHERE ";
			$query.="`S`.`user_uid`=`G`.`user_uid` ";
			$query.="AND ";
			$query.="`S`.`user_uid`=`U`.`uid` ";
			$query.="AND ";
			$query.="`recorded_dts` > '".date('Y-m-d 00:00:00',strtotime('-7 day'))."' ";
			$query.="AND ";
			$query.="`G`.`language_uid` = '".$arrLang['uid']."' ";
			$query.="GROUP BY `G`.`game_uid`, `G`.`user_uid` ";
			$query.="ORDER BY `G`.`user_uid`,`G`.`game_uid`";

			$arrScore = array();
			$arrNames = array();
			$arrFlags = array();
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row = mysql_fetch_array($result)) {
					$arrNames[$row['user_uid']] = $row['school'];
					if(isset($arrScore[$row['user_uid']])) {
						$arrScore[$row['user_uid']] += ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					} else {
						$arrScore[$row['user_uid']] = ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					}
					$arrFlags[$row['user_uid']] = $row['locale'];
				}
			}
			arsort($arrScore);
			$arrRes = array_slice($arrScore,0,10,true);
			foreach($arrRes as $uid => $Score ) {
				$arrdata = array(
					'name'			=> addslashes($arrNames[$uid]),
					'score'			=> $Score,
					'locale'		=> $arrFlags[$uid],
					'user_uid'		=> $uid,
					'section'		=> 'schools',
					'language_uid'	=> $arrLang['uid']
				);
				$this->AddRow($arrdata);
			}
		}

		// START STUDENTS QUERY HERE
		foreach($arrLanguage as $arrLang) {
			$query ="SELECT ";
			$query.="`G`.`user_uid`,";
			$query.="`G`.`time`, ";
			$query.="MAX(`score_right`) AS `MxScore` , ";
			$query.="`vfirstname`, ";
			$query.="`vlastname`, ";
			$query.="`S`.`school`, ";
			$query.="`U`.`locale` ";
			$query.="FROM ";
			$query.="`gamescore` AS `G`, ";
			$query.="`profile_student` AS `PS`, ";
			$query.="`user` AS `U`, ";
			$query.="`users_schools` AS `S` ";
			$query.="WHERE ";
			$query.="`PS`.`iuser_uid`=`G`.`user_uid` ";
			$query.="AND ";
			$query.="`PS`.`iuser_uid`=`U`.`uid` ";
			$query.="AND ";
			$query.="`PS`.`school_id`=`S`.`uid` ";
			$query.="AND ";
			$query.="`recorded_dts` > '".date('Y-m-d 00:00:00',strtotime('-7 day'))."' ";
			$query.="AND ";
			$query.="`G`.`language_uid` = '".$arrLang['uid']."'";
			$query.="GROUP BY `G`.`game_uid`, `G`.`user_uid` ";
			$query.="ORDER BY `G`.`user_uid`,`G`.`game_uid`";

			$arrScore = array();
			$arrNames = array();
			$arrFlags = array();
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row = mysql_fetch_array($result)) {
					$arrNames[$row['user_uid']] = $row['vfirstname'].' '.substr(strtoupper($row['vlastname']),0,1).', '.preg_replace('/[0-9]/','',ucwords(strtolower(str_replace(array('[',']','_'),array('','',' '),$row['school']))));
					if(isset($arrScore[$row['user_uid']])) {
						$arrScore[$row['user_uid']] += ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					} else {
						$arrScore[$row['user_uid']] = ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					}
					$arrFlags[$row['user_uid']] = $row['locale'];
				}
			}
			arsort($arrScore);
			$arrRes = array_slice($arrScore,0,10,true);
			foreach($arrRes as $uid => $Score ) {
				$arrdata = array(
					'name'			=> addslashes($arrNames[$uid]),
					'score'			=> $Score,
					'locale'		=> $arrFlags[$uid],
					'user_uid'		=> $uid,
					'section'		=> 'students',
					'language_uid'	=> $arrLang['uid']
				);
				$this->AddRow($arrdata);
			}
		}

		// START HOMEUSERS QUERY HERE
		foreach($arrLanguage as $arrLang) {
			$query ="SELECT ";
			$query.="`G`.`user_uid`,";
			$query.="`G`.`time`, ";
			$query.="MAX(`score_right`) AS `MxScore` , ";
			$query.="`vfirstname`, ";
			$query.="`vlastname`, ";
			$query.="`U`.`locale` ";
			$query.="FROM ";
			$query.="`gamescore` AS `G`, ";
			$query.="`profile_homeuser` AS `HM`, ";
			$query.="`user` AS `U` ";
			$query.="WHERE ";
			$query.="`HM`.`iuser_uid`=`G`.`user_uid` ";
			$query.="AND ";
			$query.="`HM`.`iuser_uid`=`U`.`uid` ";
			$query.="AND ";
			$query.="`recorded_dts` > '".date('Y-m-d 00:00:00',strtotime('-7 day'))."' ";
			$query.="AND ";
			$query.="`G`.`language_uid` = '".$arrLang['uid']."'";
			$query.="GROUP BY `G`.`game_uid`, `G`.`user_uid` ";
			$query.="ORDER BY `G`.`user_uid`,`G`.`game_uid`";

			$arrScore = array();
			$arrNames = array();
			$arrFlags = array();
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row = mysql_fetch_array($result)) {
					$arrNames[$row['user_uid']] = $row['vlastname'].' '.$row['vfirstname'];
					if(isset($arrScore[$row['user_uid']])) {
						$arrScore[$row['user_uid']] += ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					} else {
						$arrScore[$row['user_uid']] = ($row['MxScore']*$this->multiplyScoreBy)/$row['time'];
					}
					$arrFlags[$row['user_uid']] = $row['locale'];
				}
			}
			arsort($arrScore);
			$arrRes = array_slice($arrScore,0,10,true);
			foreach($arrRes as $uid => $Score ) {
				$arrdata = array(
					'name'			=> addslashes($arrNames[$uid]),
					'score'			=> $Score,
					'locale'		=> $arrFlags[$uid],
					'user_uid'		=> $uid,
					'section'		=> 'homeusers',
					'language_uid'	=> $arrLang['uid']
				);
				$this->AddRow($arrdata);
			}
		}

	}
	
	protected function AddRow($data=array()) {
		$query ="INSERT ";
		$query.="INTO ";
		$query.="`lingualympics` (";
		$query.="`vname`, ";
		$query.="`score`, ";
		$query.="`locale`, ";
		$query.="`user_uid`, ";
		$query.="`language_uid`, ";
		$query.="`section` ";
		$query.=") VALUES (";
		$query.="'".$data['name']."', ";
		$query.="'".floor($data['score'])."', ";
		$query.="'".$data['locale']."', ";
		$query.="'".$data['user_uid']."', ";
		$query.="'".$data['language_uid']."', ";
		$query.="'".$data['section']."' ";
		$query.=")";
		database::query($query);
	}

	protected function page_default () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		//$skeleton = make::tpl ('skeleton.basic');
		$skeleton = make::tpl ('skeleton.lingualympics');

		/**
		 * Fetch the body content template
		 */
		$body = make::tpl ('body.lingualympics');

		$body->assign($this->getLanguageContents());
		$arrRow = array();
		$locale = config::get('locale');
		$query = "SELECT * FROM `lingualympics_cms` WHERE `locale`='".$locale."' ";
		$result = database::query($query);
		if(mysql_error() == '' && mysql_num_rows($result)) {
			$arrRow = mysql_fetch_assoc($result);
		} else {
			$query = "SELECT * FROM `lingualympics_cms` WHERE `locale`='en' ";
			$result = database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				$arrRow = mysql_fetch_assoc($result);
			}
		}
		//$content = '';
		//$body->assign('content',$content);

		/**
		 * Fetch the page details
		 */
		//$page = new page('lingualympics');

		/**
		 * Build the output
		 */
		if(is_array($arrRow) && count($arrRow)) {
			$body->assign ($arrRow);
		}
		$skeleton->assign (
			array (
				'title'			=> (isset($arrRow['meta_title']))?$arrRow['meta_title']:'',
				'keywords'		=> (isset($arrRow['meta_keyword']))?$arrRow['meta_keyword']:'',
				'description'	=> (isset($arrRow['meta_description']))?$arrRow['meta_description']:'',
				'body'			=> $body
			)
		);
		output::as_html($skeleton,true);
	}

	private function getLanguageContents() {

		$arrSchool		= array();
		$arrStudents	= array();
		$arrHomeUser	= array();

		$arrSchool		= $this->getAllSchoolContent();
		$arrStudents		= $this->getAllStudentContent();
		return array(
			'language'		=>'',
			'div.schools'	=>$arrSchool,
			'div.students'	=>$arrStudents,
			'div.homeusers'	=>''
		);
	}

	private function getAllSchoolContent() {

		$style='';
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`lingualympics` ";
		$query.="WHERE ";
		$query.="`section` = 'schools' ";
		$query.="ORDER BY `score` DESC ";
		$result = database::query($query);

		$i = 1;
		$arrRows = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row = mysql_fetch_array($result)) {
				if($row['locale'] == 'en') {
					$row['locale'] = 'gb';
				}
				$arrRows[] = make::tpl('body.lingualympics.table.rows')->assign(
					array(
						'i'		=>($i++),
						'locale'=>$row['locale'],
						'vname'	=>trim(stripslashes(htmlentities($row['vname']))),
						'score'	=>$row['score']
					)
				)->get_content();
			}
		}
		$Html=make::tpl('body.lingualympics.table')->assign(
			array(
				'class'			=>'locale-school-scores school',
				'style'			=>$style,
				'table_content'	=>implode('',$arrRows)
			)
		)->get_content();
		return $Html;
	}

	private function getAllStudentContent() {

		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`lingualympics` ";
		$query.="WHERE ";
		//$query.="`language_uid`='".$arrLang['uid']."'";
		//$query.="AND ";
		$query.="`section` != 'schools' ";
		$query.="ORDER BY `score` DESC ";
		$result = database::query($query);

		$i = 1;
		$arrRows = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row = mysql_fetch_array($result)) {
				if($row['locale'] == 'en') {
					$row['locale'] = 'gb';
				}
				$arrRows[] = make::tpl('body.lingualympics.table.rows')->assign(
					array(
						'i'		=>($i++),
						'locale'=>$row['locale'],
						'vname'	=>trim(stripslashes(htmlentities($row['vname']))),
						'score'	=>$row['score']
					)
				)->get_content();
			}
		}
		$Html=make::tpl('body.lingualympics.table')->assign(
			array(
				'class'			=>'all-student-scores students',
				'style'			=>'style="display:none;"',
				'table_content'	=>implode('',$arrRows)
			)
		)->get_content();
		return $Html;
	}

	private function getLanguageContents_old() {
		$arrLangHtml	= array();
		$arrSchool		= array();
		$arrStudents	= array();
		$arrHomeUser	= array();

		$arrLanguage = language::getLanguages();
		foreach($arrLanguage as $arrLang) {
			if($arrLang['is_learnable']==0) {
				continue;
			}
			$active = '';
			if($arrLang['prefix']=='fr') {
				$active=' active';
			}
			$arrLangHtml[]='<a href="#" class="'.$arrLang['prefix'].$active.'"><span title="'.$arrLang['name'].'">'.$arrLang['name'].'('.strtoupper($arrLang['prefix']).')</span></a>';
			$arrSchool[]	= $this->getSchoolContent($arrLang);
			$arrStudents[]	= $this->getStudentContent($arrLang);
			$arrHomeUser[]	= $this->getHomeUserContent($arrLang);
		}
		return array(
			'language'		=>implode('',$arrLangHtml),
			'div.schools'	=>implode('',$arrSchool),
			'div.students'	=>implode('',$arrStudents),
			'div.homeusers'	=>implode('',$arrHomeUser)
		);
	}

	private function getSchoolContent($arrLang) {
		$style = "style='display:none;'";
		if($arrLang['prefix']=='fr') {
			$style='';
		}
		
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`lingualympics` ";
		$query.="WHERE ";
		$query.="`language_uid`='".$arrLang['uid']."'";
		$query.="AND ";
		$query.="`section` = 'schools' ";
		$query.="ORDER BY `score` DESC ";
		$result = database::query($query);

		$i = 1;
		$arrRows = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row = mysql_fetch_array($result)) {
				if($row['locale'] == 'en') {
					$row['locale'] = 'gb';
				}
				$arrRows[] = make::tpl('body.lingualympics.table.rows')->assign(
					array(
						'i'		=>($i++),
						'locale'=>$row['locale'],
						'vname'	=>trim(stripslashes(htmlentities($row['vname']))),
						'score'	=>$row['score']
					)
				)->get_content();
			}
		}
		$Html=make::tpl('body.lingualympics.table')->assign(
			array(
				'class'			=>'locale-school-scores school-'.$arrLang['prefix'],
				'style'			=>$style,
				'table_content'	=>implode('',$arrRows)
			)
		)->get_content();
		return $Html;
	}

	private function getStudentContent($arrLang) {

		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`lingualympics` ";
		$query.="WHERE ";
		$query.="`language_uid`='".$arrLang['uid']."'";
		$query.="AND ";
		$query.="`section` = 'students' ";
		$query.="ORDER BY `score` DESC ";
		$result = database::query($query);

		$i = 1;
		$arrRows = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row = mysql_fetch_array($result)) {
				if($row['locale'] == 'en') {
					$row['locale'] = 'gb';
				}
				$arrRows[] = make::tpl('body.lingualympics.table.rows')->assign(
					array(
						'i'		=>($i++),
						'locale'=>$row['locale'],
						'vname'	=>trim(stripslashes(htmlentities($row['vname']))),
						'score'	=>$row['score']
					)
				)->get_content();
			}
		}
		$Html=make::tpl('body.lingualympics.table')->assign(
			array(
				'class'			=>'all-student-scores students-'.$arrLang['prefix'],
				'style'			=>'style="display:none;"',
				'table_content'	=>implode('',$arrRows)
			)
		)->get_content();
		return $Html;
	}

	private function getHomeUserContent($arrLang) {
		$query ="SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`lingualympics` ";
		$query.="WHERE ";
		$query.="`language_uid`='".$arrLang['uid']."'";
		$query.="AND ";
		$query.="`section` = 'homeusers' ";
		$query.="ORDER BY `score` DESC ";
		$result = database::query($query);
		$i = 1;
		$arrRows = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row = mysql_fetch_array($result)) {
				if($row['locale'] == 'en') {
					$row['locale'] = 'gb';
				}
				$arrRows[] = make::tpl('body.lingualympics.table.rows')->assign(
					array(
						'i'		=>($i++),
						'locale'=>$row['locale'],
						'vname'	=>trim(stripslashes(htmlentities($row['vname']))),
						'score'	=>$row['score']
					)
				)->get_content();
			}
		}
		$Html=make::tpl('body.lingualympics.table')->assign(
			array(
				'class'			=>'all-homeuser-scores homeusers-'.$arrLang['prefix'],
				'style'			=>'style="display:none;"',
				'table_content'	=>implode('',$arrRows)
			)
		)->get_content();
		return $Html;
	}

}
/*

SELECT max(`score_right`),`user_uid` FROM `gamescore` GROUP BY `user_uid`
SELECT max(`score_right`),`user_uid`,`vfirstname`,`vlastname` FROM `gamescore` AS `G`,`profile_student` AS `PS` WHERE `PS`.`iuser_uid`=`G`.`user_uid` GROUP BY `user_uid`

*/
?>