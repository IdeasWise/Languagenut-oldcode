<?php

/**
 * flash.php
 */

class Flash extends Controller {

	public function __construct () {
		parent::__construct();
		$this->page();
	}

	protected function page () {

		if(count($_SESSION) > 0 && isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in']==true) {
			$arrPaths				= config::get('paths');
			$user_uid				= $_SESSION['user']['uid'];
			//$type					= $_SESSION['user']['type'];
			$locale					= $_SESSION['user']['prefix'];
			$locale					= config::get('locale');
			$support_language_id	= 14;
			$swf					= 'swf';
			$is_ie					= false;
			$arrPackages			= user::get_user_packages($_SESSION['user']['uid']);
			$tour_list = '["unit3/song","unit1/section5/listen","unit6/section5/memory","unit4/section2/hangman","unit4/section3/noughtscrosses","unit6/story","unit17/section1/pairs","unit5/section2/noughtscrosses"]';
			$site_map				= '[{"pageId":"learningSelection","children":[{"pageId":"yearSelectionEAL","children":[{"pageId":"unitSectionSelectionEAL","children":[{"pageId":"ealGame","children":[{"pageId":"gamePage"},{"pageId":"ealTest","children":[{"pageId":"gamePage"}]}]},{"pageId":"karaokePage"},{"pageId":"storyPage"}]}]}]}]';
			$flash_package_token	= 'standard';
			$language_id_text = 'support_language_id';
			$other_notification = '';
			/*
			if(isset($_SESSION['user']['package_token'])) {
				$flash_package_token = $_SESSION['user']['package_token'];
			}
			*/
			if(isset($arrPaths[1]) && in_array($arrPaths[1],array('standard','eal'))){
				$flash_package_token = $arrPaths[1];
			} else if(is_array($arrPackages) && count($arrPackages)==1){
				$flash_package_token = $arrPackages[0];
			}
			if($flash_package_token == 'standard' && !in_array($flash_package_token,$arrPackages)){
				$flash_package_token = 'lgfl_standard';
			}
			if($flash_package_token == 'eal' && !in_array($flash_package_token,$arrPackages)){
				if(in_array('lgfl_eal',$arrPackages)) {
					$flash_package_token = 'lgfl_eal';
				} else {
					$flash_package_token = 'standard';
				}
			}

			$arrEnLocales = array(
				'bz',
				'jm',
				'tt',
				'gy',
				'ag',
				'dm',
				'vc',
				'bs',
				'bd',
				'bw',
				'fj',
				'gm',
				'gh',
				'gy',
				'ke',
				'mt',
				'mu',
				'na',
				'ng',
				'pk',
				'rw',
				'ws',
				'sl',
				'sg',
				'sb',
				'za',
				'tz',
				'to',
				'ug',
				'vu',
				'zm',
				'zw'
			);

			$arrFrLocales = array(
				'cd',
				'mg',
				'cm',
				'ci',
				'bf',
				'ne',
				'sn',
				'ml',
				'bi',
				'bj',
				'tg',
				'ga',
				'dj'
			);

			$arrSpLocales = array(
				'es',
				'co',
				'gt',
				'pe',
				'ni',
				'do',
				'bo',
				've',
				'ar',
				'cu',
				'pr',
				'py',
				'ec',
				'hn',
				'sv',
				'cr',
				'pa'
			);
			if(in_array($locale,$arrEnLocales)) {
				$locale = 'en';
			} else if(in_array($locale,$arrFrLocales)) {
				$locale = 'fr';
			} else if(in_array($locale,$arrSpLocales)) {
				$locale = 'sp';
			}
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`flash_version` ";
			$query.="from ";
			$query.="`language` ";
			$query.="WHERE ";
			$query.="`prefix` = '".$locale."' ";
			$query.="LIMIT 1";
			$result = database::query($query);
			if($result && mysql_num_rows($result) ){
				$row = mysql_fetch_array($result);
				$support_language_id = $row['uid'];
				if(!empty($row['flash_version'])) {
					$swf = $row['flash_version'];
				}
			}

			$validate	= array();
			$type		= array('school', 'schooladmin', 'schoolteacher', 'student', 'homeuser');
			$validate	= array_intersect(@$_SESSION['user']['user_type'], $type);

			if(count($validate)){
				$paths = config::get('paths');
				if(count($paths) > 2) {

				} else {
					/**
					 * Fetch the flash public xhtml page template
					 */
					
					/*
					if(in_array($support_language_id,array(106,107,110))) {
						$swf = 'swf10';
					}*/
					if(in_array($flash_package_token,array('eal','lgfl_eal'))) {
						$site_map = '[{"pageId":"supportSelection","children":[{"pageId":"yearSelectionEAL","children":[{"pageId":"unitSectionSelectionEAL","children":[{"pageId":"ealGame","children":[{"pageId":"gamePage"},{"pageId":"ealTest","children":[{"pageId":"gamePage"}]}]},{"pageId":"karaokePage"},{"pageId":"storyPage"}]}]}]}]';
						$language_id_text = 'learning_language_id';
					}

					if($flash_package_token == 'lgfl_standard' && isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights']!='student') {
						$other_notification = '<a href="'.config::url('lgfl-upgrade/mfl/').'" style="color:white;font-size:0.8em;font-family:Arial;">Get all of Languagenut mfl with your LGFL discount!</a>';
					}

					if($flash_package_token == 'lgfl_eal' && isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights']!='student') {
						$other_notification = '<a href="'.config::url('lgfl-upgrade/eal/').'" style="color:white;font-size:0.8em;font-family:Arial;">Get all of Languagenut EAL with your LGFL discount!</a>';
					}
					
					/*
					if($other_notification=='') {
						if(is_array($arrPackages) && count($arrPackages)) {
							if(!in_array('standard',$arrPackages)) {
								$other_notification = '<a href="'.config::url('upgrade/').'" style="color:white;font-size:0.8em;font-family:Arial;">Free trial the MFL resource here!</a>';
							} else if(!in_array('eal',$arrPackages)) {
								$other_notification = '<a href="'.config::url('upgrade/').'" style="color:white;font-size:0.8em;font-family:Arial;">Free trial the EAL resource here!</a>';
							}
						}
					}
					*/

					$ie_site_map= str_replace(array('|[',']|'),array('[',']'),str_replace('\\','',str_replace('"', '|', json_encode($site_map))));
					$ie_tour_list= str_replace(array('|[',']|'),array('[',']'),str_replace('\\','',str_replace('"', '|', json_encode($tour_list))));
					$skeleton = make::tpl ('skeleton.flash');
					$skeleton->assign(
						array(
							'translate:need_flash'	=> config::translate('need_flash'),
							'support_language_id'	=> $support_language_id,
							'swf'					=> $swf,
							'package_token'			=> $flash_package_token,
							'site_map'				=> "'".$site_map."'",
							'tour_list'				=> "'".$tour_list."'",
							'ie_site_map'			=> "'".$ie_site_map."'",
							'ie_tour_list'			=> "'".$ie_tour_list."'",
							'language_id_text'		=> $language_id_text,
							'other_notification'	=> $other_notification
						)
					);

					/**
					 * Fetch the page details
					 */
					$page = new page('index');

					/**
					 * Build the output
					 */
					$skeleton->assign (
						array (
							'title'			=> $page->title(),
							'keywords'		=> $page->keywords(),
							'description'	=> $page->description()
						)
					);

					output::as_html($skeleton,true);
				}
			} else {
				// do nothing yet
				//print_r($_SESSION);
			}
		} else {
			//print_r($_SESSION);
			output::redirect(config::url('logout/'));
		}
	}
}

?>