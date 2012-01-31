<?php

	class admin_lingualympics_cms extends Controller {

		private $locale		= 'en';
		private $arrPath	= array();

		public function __construct () {
			parent::__construct();
			$this->arrPath = config::get('paths');
			if(isset($this->arrPath[2]) && language::CheckLocale($this->arrPath[2],false)) {
				$this->locale = $this->arrPath[2];
			} else {
				output::redirect(config::admin_uri('lingualympics_cms/en/'));
			}
			$this->index();
		}
		/**
		* index() methods displays  form to edit content
		*/
		protected function index() {
			$skeleton	= make::tpl ('skeleton.admin');
			$body		= make::tpl ('body.lingualympics.cms.add.edit');
			$objLingualympicsCms = new lingualympics_cms();

			/**
			* If submit button is pressed then save form data to database
			*/
			if(isset($_POST['form_submit_button'])){
				if($uid=$objLingualympicsCms->doSave() ){
					// redirect to list if all does well;
					if(!isset($_SESSION['cms_success_message'])) {
						$_SESSION['cms_success_message'] = component_message::success('Record has been updated successfully.');
					}
					output::redirect(config::admin_uri('lingualympics_cms/'.$this->locale.'/'));
				} else {
					/**
					* If there is any error in saving form data to database or if empty data
					* from form then error message and form data
					* repopulated to the user.
					*/
					$body->assign($objLingualympicsCms->arrForm);
				}
			} else {
				$arrData = array();
				$arrData = $objLingualympicsCms->get_content_by_locale($this->locale);
				if(is_array($arrData) && count($arrData)) {
					$body->assign($arrData);
				}
				$body->assign('success_message',(isset($_SESSION['cms_success_message']))?$_SESSION['cms_success_message']:'');
					if(isset($_SESSION['cms_success_message'])) {
						unset($_SESSION['cms_success_message']);
					}
			}
			$arrLocales	= $this->getLocaleLinks();
			$body->assign('select_locales',implode('',$arrLocales));
			$body->assign('locale',$this->locale);
			$skeleton->assign (
				array (
					'body' => $body
				)
			);
			output::as_html($skeleton,true);
		}

		/*[ UTILITY FUNCTIONS TO BE MOVED TO OBJECT METHODS ]*/

		protected function getLocaleLinks() {

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
					$arrLocaleLinks[] = '<span style="padding:0 3px;">[<a href="'.config::admin_uri('lingualympics_cms/'.$row['prefix'].'/').'"'.($row['prefix']==$this->locale?' class="selected"' : '').'>'.$row['prefix'].'</a>]</span>';
				}
			}

			return $arrLocaleLinks;
		}
	}

?>