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
			$user_uid				= $_SESSION['user']['uid'];
			//$type					= $_SESSION['user']['type'];
			$locale					= $_SESSION['user']['prefix'];
			$locale					= config::get('locale');
			$support_language_id	= 14;
			$swf					= 'swf';

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

					$skeleton = make::tpl ('skeleton.flash');
					$skeleton->assign(
						array(
							'translate:need_flash'	=> config::translate('need_flash'),
							'support_language_id'	=> $support_language_id,
							'swf'					=> $swf
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