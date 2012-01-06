<?php

/**
 * index.php
 */
class Index extends Controller {

	public function __construct() {
		parent::__construct();

		$paths = config::get('paths');

		if(isset($paths[0]) && $paths[0] == 'cancel-subscription') {
			$this->load_controller('cancel_subscription');
			exit;
		}

		if(isset($paths[0]) && $paths[0] == 'send-application') {
			$this->load_controller('send_application');
			exit;
		}
		$this->page();
	}

	protected function page() {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = new xhtml('skeleton.testpublic');
		$skeleton->load();

		/**
		 * Fetch the body content
		 */
		$locale = config::get('locale');
		$body = new xhtml('body.index.'.$locale);
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
		//$arrPanels = $comTranslations->homePageTabsByLocale('en');
		$arrPanels = $this->homePageTabsByLocale();
		/*
		echo '<pre>';
		print_r($arrPanels);
		echo '</pre>';
		*/
		/*
		foreach($arrPanels as $panel => $content) {
			$arrPanels[$panel] = $this->ascii_to_entities($content);
		}*/
		/*
		echo '<pre>';
		print_r($arrPanels);
		echo '</pre>';
		*/
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
			'all_rights_reserved',
			'home_user_link_caption',
			'school_user_link_caption'
		));

		/*
		 *  on following condition we should put a check if array ($arrTerms) counts matche with all index to check translation is available or not if not we should redirect it to /en/ locale
		 */
		if (is_array($arrTerms)) {
			foreach ($arrTerms as $key => $val) {
				$body->assign('translate:' . $key, $val);
				$body->assign($key, $val);
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
	
	public function homePageTabsByLocale() {
		$gameHTML = '<div id="games" class="panel" style="float: left; position: relative;">

<h2>الألعاب</h2>

<div id="gameIcons"></div>

<p>الأطفال يحبون تعلم كلمات جديدة بمفاهيم لغوية عندما يلعبون مع الألعاب . انه ممتع، و منارة المعلم ، و أيضا ترتبط بدقة التقدم خلال السنوات ،لأنه لايقدر بثمن. مع أكثر من ١٤٠٠ كلمة وعباره، كما ان العديد من العاب لانجويج نت يمكن أن تغذي المشاريع المدرسية.</p>

<ul>
<li class="listenAndLearn"><a href="http://images.languagenut.com/nz/listen.jpg">ا ستمع وتعلم</a></li>
<li class="pairs"><a href="http://images.languagenut.com/nz/pairs.jpg">ثنائي</a></li>
<li class="memory"><a href="http://images.languagenut.com/nz/memory.jpg">ذاكرة</a></li>
<li class="multipleChoice"><a href="http://images.languagenut.com/nz/multiple.jpg">اختيارات متعددة</a></li>
</ul>

<ul>
<li class="hangman"><a href="http://images.languagenut.com/nz/hangman.jpg">لعبة المشنقة</a></li>
<li class="noughtsAndCrosses"><a href="http://images.languagenut.com/nz/crosses.jpg">النقاط و التقاطع</a></li>
<li class="presentation"><a href="http://images.languagenut.com/nz/presentation.jpg">الطريقة التي تقدم بها المعلومات</a></li>
<li class="spelling"><a href="http://images.languagenut.com/nz/spelling.jpg">الأغاني</a></li>
</ul>

</div>';
		$arrPanels = array (
			'welcome'	=> '',
			'games'		=> '',
			'songs'		=> '',
			'culture'	=> '',
			'teachers'	=> '',
			'children'	=> '',
			'contact'	=> ''
		);

		$arrPanels_2 = array (
			'welcome'	=> 'lba',
			'games'		=> 'en',
			'songs'		=> 'lba',
			'culture'	=> 'lba',
			'teachers'	=> 'lba',
			'children'	=> 'lba',
			'contact'	=> 'lba'
		);

		$arrResponse = array ();

		$query = "SELECT ";

		$arrQuery = array ();

		foreach($arrPanels_2 as $key=>$val) {
			$arrQuery[]= "(SELECT `html` FROM `page_index_tab_{$key}_translations` WHERE `locale`='$val' LIMIT 1) AS `{$key}_html`";
		}

		$query.= implode(', ',$arrQuery);

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$arrPanels['welcome']	= stripslashes($row['welcome_html']);
			$arrPanels['games']		= stripslashes($gameHTML);
			$arrPanels['songs']		= stripslashes($row['songs_html']);
			$arrPanels['culture']	= stripslashes($row['culture_html']);
			$arrPanels['teachers']	= stripslashes($row['teachers_html']);
			$arrPanels['children']	= stripslashes($row['children_html']);
			$arrPanels['contact']	= stripslashes($row['contact_html']);
		}

		return $arrPanels;
	}

	private function ascii_to_entities($str) {
		$count	= 1;
		$out	= '';
		$temp	= array();
		for ($i = 0, $s = strlen($str); $i < $s; $i++) {
			$ordinal = ord($str[$i]);
			if ($ordinal < 128) {
				if (count($temp) == 1) {
					$out  .= '&#'.array_shift($temp).';';
					$count = 1;
				}
				$out .= $str[$i];
			} else {
				if (count($temp) == 0)	{
					$count = ($ordinal < 224) ? 2 : 3;
				}
				$temp[] = $ordinal;
				if (count($temp) == $count) {
					$number = ($count == 3) ? (($temp['0'] % 16) * 4096) +
					(($temp['1'] % 64) * 64) +
					($temp['2'] % 64) : (($temp['0'] % 32) * 64) +
					($temp['1'] % 64);
					$out .= '&#'.$number.';';
					$count = 1;
					$temp = array();
				}
			}
		}
		return $out;
	}

}

?>