<?php

class component_translations {

	public $captcha_key	= '01GzIUP5tukWldlG11j35BaQ==';
	public $captcha_c	= 'XYGd2UAhtkLeO8BZovtp9vXDbcEtFqhGonoRqi3WigA=';
	public $emaillink	= '';

	public function __construct() {
		$path = 'http://mailhide.recaptcha.net/d?k='.$this->captcha_key.'&amp;c='.$this->captcha_c;
		$this->emaillink = '<a href="'.$path.'" onclick="window.open(\''.$path.'\', \'\', \'toolbar=0, scrollbars=0, location=0, statusbar=0, menubar=0, resizable=0, width=500, height=300\'); return false;" title="Reveal this e-mail address">Send us an email!</a>';
	}

	public function homePageTabsByLocale($locale='') {

		if($locale=='') {
			$locale = config::get('locale');
		}

		$arrPanels = array (
			'welcome'	=> '',
			'games'		=> '',
			'songs'		=> '',
			'culture'	=> '',
			'teachers'	=> '',
			'children'	=> '',
			'contact'	=> ''
		);

		$arrResponse = array ();

		$query = "SELECT ";

		$arrQuery = array ();

		foreach($arrPanels as $key=>$val) {
			$arrQuery[]= "(SELECT `html` FROM `page_index_tab_{$key}_translations` WHERE `locale`='$locale' LIMIT 1) AS `{$key}_html`";
		}

		$query.= implode(', ',$arrQuery);

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$arrPanels['welcome']	= stripslashes($row['welcome_html']);
			$arrPanels['games']		= stripslashes($row['games_html']);
			$arrPanels['songs']		= stripslashes($row['songs_html']);
			$arrPanels['culture']	= stripslashes($row['culture_html']);
			$arrPanels['teachers']	= stripslashes($row['teachers_html']);
			$arrPanels['children']	= stripslashes($row['children_html']);
			$arrPanels['contact']	= str_replace('{{ email_link }}',$this->emaillink,stripslashes($row['contact_html']));
		}

		return $arrPanels;
	}
}

?>