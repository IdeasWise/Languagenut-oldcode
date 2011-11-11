<?php

class plugin_certificate extends plugin {

	/* data having slug variable */
    public function __construct($data = array()) {
        $this->data =   $data;        
    }

    public function get_class_name() {
        return __CLASS__;
    }
	
    public function run() {		
		
    }  
    
    public function pdf_uploads($language_uid) {
    		$language =  new language();
		$Bodyarray = array();
		$Bodyarray ['uid'] = $language_uid;
    		if(isset($_POST['form_language_uploads'])) {
			if( $language->doSaveImages() === false ){
			$Bodyarray ['message'] = @$language->changed_array['message_error'];
			}
		}
		
		$language = new language($language_uid);
		 $language->load();
		 foreach( $language->TableData as $idx => $val ){
				$Bodyarray[$idx] = $val['Value'];
	  	}
    		$xhtml = new xhtml('body.admin.language.certificate');
		$xhtml->load();
		$data['form.action'] = $_SERVER['REQUEST_URI'].'#tab-3';
		$xhtml->assign($Bodyarray);
    		return $xhtml->get_content();
    }
}


?>