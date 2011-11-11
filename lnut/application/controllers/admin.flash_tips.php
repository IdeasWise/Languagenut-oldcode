<?php

class admin_flash_tips extends Controller {

	private $token		= 'list';
	private $arrTokens	= array (
		'list',
		'translation'
	);
	private $parts		= array();

	public function __construct () {
		parent::__construct();
		$this->arrPath = config::get('paths');

		if(isset($this->arrPath[2]) && in_array($this->arrPath[2], $this->arrTokens)) {
			$this->token =  $this->arrPath[2];
		}

		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	protected function doList () {
		$skeleton	= make::tpl('skeleton.admin');
		$body		= make::tpl('body.admin.flash_tips.list');

		$arrList		= array();
		$objFlashTips	= new flash_tips();
		$arrList		= $objFlashTips->getList();
		$arrRows		= array();

		if(!empty($arrList)) {
			foreach($arrList as $uid=>$data) {
				$arrRows[] = make::tpl('body.admin.flash_tips.list.row')->assign($data)->get_content();
			}
		}

		$body->assign('list.rows',implode('',$arrRows));
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doTranslation() {
		if(isset($this->arrPath[3]) && is_numeric($this->arrPath[3])) {
			$objFlashTips	= new flash_tips($this->arrPath[3]);
			if($objFlashTips->get_valid()) {
				$objFlashTips->load();
				$locale = 'en';
				if(isset($this->arrPath[4])) {
					$locale=$this->arrPath[4];
				}

				if(isset($_POST['form_submit_button'])) {
					$objFlashTipsTranslation = new flash_tips_translation();
					$objFlashTipsTranslation->saveTranslation();
					output::redirect(config::admin_uri('flash_tips/translation/'.$objFlashTips->get_uid().'/'.$locale.'/'));
				}

				$arrLocales	= $this->getLocaleLinks($locale,$this->arrPath[3]);
				$skeleton	= make::tpl('skeleton.admin');
				$body = make::tpl('body.admin.flash_tips.translation');
				$body->assign('locales',implode("\n",$arrLocales));

				$language_uid	= 14;
				$query ="SELECT ";
				$query.="`uid` ";
				$query.="from ";
				$query.="`language` ";
				$query.="WHERE ";
				$query.="`prefix` = '".$locale."' ";
				$query.="LIMIT 1";
				$result = database::query($query);
				if($result && mysql_num_rows($result) ){
					$row = mysql_fetch_array($result);
					$language_uid = $row['uid'];
				}

				$objFlashTipsTranslation = new flash_tips_translation();
				$arrRow = $objFlashTipsTranslation->getTranslations( 
					$objFlashTips->get_uid(),
					$language_uid
				);
				$body->assign($arrRow);
				$body->assign('locale',$locale);
				$body->assign('flash_tips_uid',$objFlashTips->get_uid());
				$body->assign('title',$objFlashTips->get_title());
				$body->assign('language_uid',$language_uid);

				$skeleton->assign (
					array (
						'body' => $body
					)
				);
				output::as_html($skeleton,true);

			}
		}
	}

	protected function getLocaleLinks($selected='',$flash_tips_uid=null) {

		$arrLocaleLinks = array();

		$query = "SELECT ";
		$query.= "`prefix` ";
		$query.= "FROM ";
		$query.= "`language` ";
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";

		$result= database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$arrLocaleLinks[] = '<span style="padding:0 3px;">[<a href="'.config::admin_uri('flash_tips/translation/'.$flash_tips_uid.'/'.$row['prefix'].'/').'"'.($row['prefix']==$selected?' class="selected"' : '').'>'.$row['prefix'].'</a>]</span>';
			}
		}

		return $arrLocaleLinks;
	}
}
?>