<?php

/**
 * trynow.php
 */

class Test extends Controller {

	public function __construct () {
		parent::__construct();

		$this->page();
	}


	protected function page() {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = new xhtml('skeleton.public');
		$skeleton->load();

		/**
		 * Fetch the body content
		 */
		$locale = config::get('locale');
		#if($locale=='nl') {
		#	$body = new xhtml('body.index.nl');
		#} else {
		echo 'body.index.'.$locale;
			$body = new xhtml('body.index.'.$locale);
		#}
		$body->load();


		/**
		 * Set Country Flags
		 */
		if($locale=='en' || $locale=='us'){
			$displayLanguage=array(
					'jp'=>'ja',
					'dk'=>'dk',
					'de'=>'ge',
					'us'=>'us',
					'fr'=>'fr'
				);
			$flags="";
			$languages=language::getPrefixes();
			foreach ($languages as $key => $language) {
				$lang=array_search($language["prefix"], $displayLanguage);
				if($lang!==false){
					$flags.='&nbsp;<a href="'.config::base($language["prefix"]."/").'"><img src="'.config::base("images/flag/".$lang.".png") .'" border="0" /></a>';
				}
			}
			$body->assign('country_flags', $flags);
		}
		/**
		 * Fetch Translations
		 */
		$comTranslations = new component_translations;
		$arrPanels = $comTranslations->homePageTabsByLocale();
		$body->assign($arrPanels);

		$arrTerms = config::translate(array(
					'welcome',
					'games',
					'songs',
					'culture',
					'teachers_say',
					'children_say',
					'contact',
					'play_and_learn',
					'terms',
					'privacy_policy',
					'free_2_week_trial',
					'all_rights_reserved'
				));

	/*
	*  on following condition we should put a check if array ($arrTerms) counts matche with all index to check translation is available or not if not we should redirect it to /en/ locale
	*/
		if (is_array($arrTerms)) {
			foreach ($arrTerms as $key => $val) {
				$body->assign('translate:' . $key, $val);
			}
		}

		/**
		 * Fetch the page details
		 */
		$page = new page('index');

		/**
		 * Build the output
		 */
		$skeleton->assign(
			array(
				'title' => $page->title(),
				'keywords' => $page->keywords(),
				'description' => $page->description(),
				'body' => $body
			)
		);

		output::as_html($skeleton, true);
	}


	protected function gamescore_script () { die('check file!!');
		$query="SELECT * FROM `gamescore20110725` WHERE `uid` > 58281";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result) && $result) {
			while($row=mysql_fetch_array($result)) {
				$query ="INSERT INTO `gamescore` ";
				$query.="(";
					$query.="`user_uid`,";
					$query.="`game_uid`,";
					$query.="`language_uid`,";
					$query.="`section_uid`,";
					$query.="`is_unit_test`,";
					$query.="`time`,";
					$query.="`score_right`,";
					$query.="`score_wrong`,";
					$query.="`recorded_dts`,";
					$query.="`spelling_right`,";
					$query.="`spelling_wrong`";
				$query.=") ";
				$query.="VALUES(";
					$query.="'".$row['user_uid']."',";
					$query.="'".$row['game_uid']."',";
					$query.="'".$row['language_uid']."',";
					$query.="'".$row['section_uid']."',";
					$query.="'".$row['is_unit_test']."',";
					$query.="'".$row['time']."',";
					$query.="'".$row['score_right']."',";
					$query.="'".$row['score_wrong']."',";
					$query.="'".$row['recorded_dts']."',";
					$query.="'".$row['spelling_right']."',";
					$query.="'".$row['spelling_wrong']."'";
				$query.=")";
				$insert = mysql_query($query) or die(mysql_error());
				$auto_uid = mysql_insert_id();
				$query="SELECT * FROM `gamescore_vocab20110725` WHERE `gamescore_uid`='".$row['uid']."'";
				$result_vocabs = mysql_query($query);
				if(mysql_error()=='' && $result_vocabs && mysql_num_rows($result_vocabs)) {
					while($row_vocab = mysql_fetch_array($result_vocabs)) {
						$query ="INSERT INTO `gamescore_vocab` ";
						$query.="(";
							$query.="`vocab_uid`,";
							$query.="`language_uid`,";
							$query.="`gamescore_uid`,";
							$query.="`word_english`,";
							$query.="`word_translated`,";
							$query.="`time`,";
							$query.="`score_right`,";
							$query.="`score_wrong`";
						$query.=") ";
						$query.="VALUES(";
							$query.="'".$row_vocab['vocab_uid']."',";
							$query.="'".$row_vocab['language_uid']."',";
							$query.="'".$auto_uid."',";
							$query.="'".addslashes($row_vocab['word_english'])."',";
							$query.="'".addslashes($row_vocab['word_translated'])."',";
							$query.="'".$row_vocab['time']."',";
							$query.="'".$row_vocab['score_right']."',";
							$query.="'".$row_vocab['score_wrong']."'";
						$query.=")";
						$insert_vocab = mysql_query($query) or die(mysql_query());
					}
				}
			}
		}
	}
}

?>