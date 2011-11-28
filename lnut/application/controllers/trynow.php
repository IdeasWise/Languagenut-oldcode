<?php

/**
 * trynow.php
 */

class TryNow extends Controller {

	public function __construct () {
		parent::__construct();
		$this->page();
	}

	protected function page () {
		/**
		 * Fetch the flash public xhtml page template
		 */
		$skeleton = new xhtml ('skeleton.trynow');
		$skeleton->load();
		$config_data = config::translate('need_flash');
		if(isset($_SESSION['trynow'])) {
			$_SESSION['trynow'] = 1;
		} else {
			$_SESSION['trynow'] = 1;
		}

		$locale					= config::get('locale');
		$support_language_uid	= 14;
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
		/*
		$swf = 'swf';
		if(in_array($support_language_uid,array(106,107,110))) {
			$swf = 'swf10';
		}
		*/
		$skeleton->assign(
			array(
				'translate:need_flash'	=> $config_data,
				'support_language_uid'	=> $support_language_uid,
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
}

?>