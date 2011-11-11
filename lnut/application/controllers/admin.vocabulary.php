<?php

class admin_sections_vocabulary extends Controller {

    private $token	= 'list';

    private $arrTokens	= array (
            'list',
            'edit',
            'add',
            'delete',
            'deletetranslation'
            
    );
  
   private $parts = array();
	
    public function __construct () {
        parent::__construct();
	$this->parts = config::get('paths');          
        if(isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
             $this->token =  $this->parts[2];
        }
        
         if(in_array($this->token,$this->arrTokens)) {
		$method = 'do' . ucfirst($this->token); 
		$this->$method();
            }
    
        
        
    }

    
  
  
    protected function doAdd()
    {
        $skeleton   = new xhtml ('skeleton.admin');
        $skeleton->load();

        $body       = new xhtml('body.admin.vocabulary.add');
        $body->load();

        $sectionObject = new sections();

        $arrBody = array();       
   
        if( isset($_POST['form_submit_button'])){
            $SaveObject       = new sections_vocabulary();
            if( $SaveObject->doSave() ){
                 $SaveObject->redirectTo('admin/vocabulary/list'); // redirect to invoice list if all does well;
            }
            else{
                $SaveObject->arrForm['section_uid'] = $sectionObject->SectionSelectBox('section_uid', $SaveObject->arrForm['section_uid']);
                $body->assign( $SaveObject->arrForm );
            }
        }
        else           
            $arrBody['section_uid'] = $sectionObject->SectionSelectBox('section_uid');

        
        $body->assign( $arrBody );
        
        $skeleton->assign (
                    array (
                    'body' => $body
                    )
            );
            output::as_html($skeleton,true);
    }

    protected function doEdit()
    {       

        $skeleton   = new xhtml ('skeleton.admin');
        $skeleton->load();

        $body       = new xhtml('body.admin.vocabulary.edit');
        $body->load();

        $sectionObject = new sections();

        $arrBody = array();
        $arrBody['uid'] =  @$this->parts[3];        
    
        if( isset($_POST['form_submit_button'])){
              $SaveObject       = new sections_vocabulary();
            if( $SaveObject->doSave() ){
                 $SaveObject->redirectTo('admin/vocabulary/list'); // redirect to invoice list if all does well;
            }
            else{
                $SaveObject->arrForm['section_uid'] = $sectionObject->SectionSelectBox('section_uid', $SaveObject->arrForm['section_uid']);
                $body->assign( $SaveObject->arrForm );
            }
        }
        else
        {
            if($this->parts[3] > 0){
                $EditObject = new sections_vocabulary($this->parts[3]);
                $EditObject->load();
                foreach( $EditObject->TableData as $idx => $val ){
                         $arrBody[$idx] = $val['Value'];
            }
            $arrBody['section_uid'] = $sectionObject->SectionSelectBox('section_uid', $arrBody['section_uid']);
             
            
            if($arrBody['active'] == 0 )
            {
               $arrBody['active'] =  'checked="checked"';
            }            
        }
        }


        $arrBody['tab.translations'] = $this->doTranslationsList($arrBody['uid']);
        $body->assign( $arrBody );

        $skeleton->assign (
                    array (
                    'body' => $body
                    )
            );
            output::as_html($skeleton,true);

    }

    protected function doTranslationsList( $term_uid )
    {
        

        $body       = new xhtml('body.admin.vocabulary.translation.list');
        $body->load();

        $bodyArr = array();
        $langObject = new language();
        $bodyArr['language_id'] =  $langObject->LanguageSelectBox('language_id');

        if(isset($_POST['add_translation'])){
            $translationObject = new sections_vocabulary_translations();
            if( $translationObject->doSave() ){

            }
            else{

                if($translationObject->arrForm['uid'] > 0 ){
            $translationObject->arrForm['uid'] = $_POST['uid'];
            $translationObject->arrForm['button_lable'] = 'Update';
            $translationObject->arrForm['cancel_button'] = 'display:block;';
        }
               $translationObject->arrForm['language_id'] =  $langObject->LanguageSelectBox('language_id', $translationObject->arrForm['language_id']);
               $body->assign( $translationObject->arrForm );
            }

        }




        $sectionsObject = new sections_vocabulary();
        $rows = array();
        $page_rows  = array();
        $rows      = $sectionsObject->SectionsVocabularyTranslationsList($term_uid);
        if(!empty($rows)) {
            foreach($rows as $uid=>$data) {

                $panel = new xhtml('body.admin.vocabulary.translation.row');
                $panel->load();
                $panel->assign($data);
                $page_rows[]    = $panel->get_content();
            }
        }
        else
           $page_rows[] = 'Translations are not available for this vocabulary.';

       

        
            $bodyArr['uid'] =  0;
            $bodyArr['button_lable'] = 'Add';
            $bodyArr['cancel_button'] = 'display:none;';
       

        
        $bodyArr['term_uid'] =  $term_uid;
        $bodyArr['frm_action'] =  $_SERVER['REQUEST_URI'].'#tab-2';


        $body->assign('list.rows'          ,   implode('',$page_rows));
        $body->assign($bodyArr);

         return $body->get_content();
    }
    protected function doDelete()
    {
       
        if( $this->parts[3] > 0){
            $deleteObject = new sections_vocabulary($this->parts[3]);
            $deleteObject->delete();
            //$invoice->redirectTo('admin/language/list/'); // redirect to invoice list if all does well;
            @ob_start();
            header('location:'.$_SERVER['HTTP_REFERER']);
            exit;
        }
        
    }

    protected function doDeletetranslation()
    {

        if( $this->parts[4] > 0){

            $translationObject = new sections_vocabulary_translations($this->parts[4]);
            $translationObject->delete();
            $translationObject->redirectTo('admin/vocabulary/edit/'.$this->parts[3].'#tab-2'); // redirect to invoice list if all does well;

            
        }
    }

    protected function doList () {

        $skeleton   = new xhtml ('skeleton.admin');
        $skeleton->load();

        $body       = new xhtml('body.admin.vocabulary.list');
        $body->load();

        $users      = array();
        $user       = new sections_vocabulary();
        $users      = $user->getList();
        $page_rows  = array();
        if(!empty($users)) {
            foreach($users as $uid=>$data) {               
                
                $panel = new xhtml('body.admin.vocabulary.list.row');
                $panel->load();
                $panel->assign($data);
                $page_rows[]    = $panel->get_content();
            }
        }

        $page_display_title     =   $user->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $user->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$user->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$user->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $body->assign('page.display.title'  ,   $page_display_title);
        $body->assign('page.navigation'     ,   $page_navigation);
        $body->assign('list.rows'          ,   implode('',$page_rows));
       
        $skeleton->assign (
                array (
                'body'          => $body
                )
        );
        output::as_html($skeleton,true);
    }
    
}

?>