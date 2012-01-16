<?php

class landing_cms extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}
	
	public function getList($all=false) {
		if(!$all) {
			$query = 'SELECT ';
			$query.= 'COUNT(`uid`) ';
			$query.= 'FROM ';
			$query.= '`landing_cms`';
			$this->setPagination( $query );
		}
		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`page_title`,  ";
		$query.= "`locale`,  ";
		$query.= "`slug` ";
		$query.= "FROM ";
		$query.= "`landing_cms` ";
		$query.= $this->getOrderBy();
		if(!$all) {
			$query.= "LIMIT ".$this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function get_langing_page_by_slug($slug='sulg-404',$check_locale=true){
		if($slug=='') {
			return false;
		} else {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`landing_cms` ";
			$query.="WHERE ";
			$query.="`slug`='".mysql_real_escape_string($slug)."' ";
			if($check_locale && $slug!='sulg-404') {
				$query.="AND ";
				$query.="`locale`='".config::get('locale')."' ";
			}
			$query.="LIMIT 0,1 ";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$arrRes = mysql_fetch_array($result);
				return $arrRes;
			} else {
				return false;
			}
		}
	}

	public function doSave() {
		if($this->isValidateFormData() == true) {
			if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$insert = $this->insert();
				return $insert;
			}
			return true;
		} else {
			return false;
		}
	}

	private function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$arrFields = array(
			'locale' => array(
				'value' => (isset($_POST['locale'])) ? trim($_POST['locale']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please select locale!',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please select valid locale.',
				'errIndex' => 'error.locale'
			),
			'page_title' => array(
				'value' => (isset($_POST['page_title'])) ? trim($_POST['page_title']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter title.',
				'minChar' => 2,
				'maxChar' => 260,
				'errMinMax' => 'Title must be 2 to 260 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid title.',
				'errIndex' => 'error.page_title'
			),
			'slug' => array(
				'value' => (isset($_POST['slug'])) ? trim($_POST['slug']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter slug.',
				'minChar' => 2,
				'maxChar' => 260,
				'errMinMax' => 'Slug must be 2 to 260 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid slug.',
				'errIndex' => 'error.slug'
			),
			'sidebar_sprite_image' => array(
				'value' => (isset($_POST['sidebar_sprite_image'])) ? trim($_POST['sidebar_sprite_image']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter side bar sprite image.',
				'minChar' => 2,
				'maxChar' => 260,
				'errMinMax' => 'Side bar sprite image must be 2 to 260 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid side bar sprite image.',
				'errIndex' => 'error.sidebar_sprite_image'
			),
			'intro_content' => array(
				'value' => (isset($_POST['intro_content'])) ? trim($_POST['intro_content']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid into content.',
				'errIndex' => 'error.intro_content'
			),
			'body_content' => array(
				'value' => (isset($_POST['body_content'])) ? trim($_POST['body_content']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid body content.',
				'errIndex' => 'error.body_content'
			),
			'sidebar_content' => array(
				'value' => (isset($_POST['sidebar_content'])) ? trim($_POST['sidebar_content']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid sidebar content.',
				'errIndex' => 'error.sidebar_content'
			),
			'meta_title' => array(
				'value' => (isset($_POST['meta_title'])) ? trim($_POST['meta_title']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please enter meta title.',
				'minChar' => 2,
				'maxChar' => 260,
				'errMinMax' => 'Meta title must be 2 to 260 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid meta title.',
				'errIndex' => 'error.meta_title'
			),
			'meta_keywords' => array(
				'value' => (isset($_POST['meta_keywords'])) ? trim($_POST['meta_keywords']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid meta keyword.',
				'errIndex' => 'error.meta_keywords'
			),
			'meta_description' => array(
				'value' => (isset($_POST['meta_description'])) ? trim($_POST['meta_description']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid meta description.',
				'errIndex' => 'error.meta_description'
			)
			
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			$this->set_locale($arrFields['locale']['value']);
			$this->set_slug($arrFields['slug']['value']);
			$this->set_page_title($arrFields['page_title']['value']);
			$this->set_sidebar_sprite_image($arrFields['sidebar_sprite_image']['value']);
			$this->set_intro_content(str_replace(array('{{', '}}'),array('&#123;&#123;', '&#125;&#125;'),$arrFields['intro_content']['value']));
			//$this->set_menu_content($arrFields['menu_content']['value']);
			$this->set_body_content(str_replace(array('{{', '}}'),array('&#123;&#123;', '&#125;&#125;'),$arrFields['body_content']['value']));
			$this->set_sidebar_content(str_replace(array('{{', '}}'),array('&#123;&#123;', '&#125;&#125;'),$arrFields['sidebar_content']['value']));
			$this->set_meta_title($arrFields['meta_title']['value']);
			$this->set_meta_keywords($arrFields['meta_keywords']['value']);
			$this->set_meta_description($arrFields['meta_description']['value']);
			return true;
		} else {
			return false;
		}
	}

	public function generate_menu_content($cms_uid=null) {
		if($cms_uid!=null && isset($_POST['header_text'])) {
			parent::__construct($cms_uid, __CLASS__);
			if($this->get_valid()) {
				$this->load();
				$menu_html = '';
				foreach($_POST['header_text'] as $index => $value) {
					$sub_menu_html ='';
					if(trim($value)!='') {
						$menu_html.='<li>';
						$menu_html.='<a class="heading" href="#">'.$value.'</a>';

						if(isset($_POST['menu_name'][$index])) {
							foreach($_POST['menu_name'][$index] as $menu_index => $menu_name) {
								$menu_url = (isset($_POST['menu_url'][$index][$menu_index])?$_POST['menu_url'][$index][$menu_index]:'');
								if(!empty($menu_name) && !empty($menu_url)) {
									$sub_menu_html.='<li>';
									$sub_menu_html.='<a href="'.$menu_url.'">'.$menu_name.'</a>';
									$sub_menu_html.='</li>';
								}
							}
						}

						if(!empty($sub_menu_html)) {
							$menu_html.='<ul class="subMenu">';
							$menu_html.=$sub_menu_html;
							$menu_html.='</ul>';
						}
						$menu_html.='</li>';
					}
				}
			}
			$menu_html = '<ul id="landingMenu">'.$menu_html.'</ul>';
			$this->set_menu_content($menu_html);
			$this->save();
		}
	}
}

?>